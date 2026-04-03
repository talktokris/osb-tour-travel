<?php
// POST delete redirect must run before any HTML output.
if (!isset($mysqli)) {
    require __DIR__ . '/../../config.php';
}
require_once __DIR__ . '/../../includes/users_service.php';

$currentPage = $_GET['page'] ?? 'users';
$actor = users_actor($mysqli);
users_require_access($actor);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    $token = (string) ($_POST['_token'] ?? '');
    $userId = (int) ($_POST['user_id'] ?? 0);

    if (!users_csrf_validate($token)) {
        users_flash_set('error', 'Invalid request token.');
    } else {
        $target = users_find($mysqli, $userId, $actor);
        if (!$target) {
            users_flash_set('error', 'User not found or access denied.');
        } elseif ((int) $target['Userid'] === (int) $actor['id']) {
            users_flash_set('error', 'You cannot delete your own account.');
        } elseif (users_delete($mysqli, $userId)) {
            users_flash_set('success', 'User deleted successfully.');
        } else {
            users_flash_set('error', 'Delete failed.');
        }
    }

    header('Location: index.php?page=users');
    exit;
}

$users = users_list($mysqli, $actor);
$flash = users_flash_get();
$csrf = users_csrf_token();

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'Users / User List'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body space-y-4">
                    <?php if (!empty($flash)): ?>
                        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                            <span><?= h((string) $flash['message']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto rounded-box border border-base-300">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Users Name</th>
                                    <th>Gender</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Title</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row): ?>
                                    <tr>
                                        <td><?= h((string) $row['Userid']) ?></td>
                                        <td><?= h((string) $row['Username']) ?></td>
                                        <td><?= h((string) $row['gender']) ?></td>
                                        <td><?= h((string) $row['Role']) ?></td>
                                        <td><?= h((string) $row['department']) ?></td>
                                        <td><?= h((string) $row['position']) ?></td>
                                        <td>
                                            <a class="btn btn-xs btn-outline" href="index.php?page=users_edit&id=<?= (int) $row['Userid'] ?>">Edit</a>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-error btn-outline js-delete-user" data-user-id="<?= (int) $row['Userid'] ?>" data-user-name="<?= h((string) $row['Username']) ?>">Delete</button>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-info btn-outline" href="index.php?page=users_view&id=<?= (int) $row['Userid'] ?>">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<form id="delete-user-form" method="post" action="index.php?page=users" class="hidden" aria-hidden="true">
    <input type="hidden" name="_token" value="<?= h($csrf) ?>">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete-user-form-user-id" value="">
</form>

<style>
    /* Native <dialog> + DaisyUI .modal clash (UA styles); use dedicated overlay + centered panel */
    #delete-user-modal.delete-user-dialog {
        margin: 0;
        max-width: none;
        max-height: none;
        width: 100%;
        height: 100%;
        padding: 1.25rem;
        border: none;
        background: transparent;
        display: none;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    #delete-user-modal.delete-user-dialog[open] {
        display: flex;
    }
    #delete-user-modal.delete-user-dialog::backdrop {
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
    }
    #delete-user-modal .delete-user-dialog__surface {
        width: 100%;
        max-width: 26rem;
        background: var(--color-base-100, #ffffff);
        color: var(--color-base-content, #1e293b);
        border-radius: 1rem;
        border: 1px solid color-mix(in oklab, var(--color-base-content, #64748b) 12%, transparent);
        box-shadow:
            0 25px 50px -12px rgba(15, 23, 42, 0.35),
            0 0 0 1px rgba(255, 255, 255, 0.06) inset;
        padding: 1.5rem 1.5rem 1.25rem;
        animation: delete-user-dialog-in 0.22s ease-out;
    }
    @keyframes delete-user-dialog-in {
        from {
            opacity: 0;
            transform: translateY(0.5rem) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    #delete-user-modal .delete-user-dialog__title {
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        margin: 0 0 0.35rem;
        color: var(--color-base-content, #0f172a);
    }
    #delete-user-modal .delete-user-dialog__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in oklab, var(--color-error, #ef4444) 12%, transparent);
        color: var(--color-error, #dc2626);
        margin-bottom: 1rem;
    }
    #delete-user-modal .delete-user-dialog__message {
        margin: 0 0 1.35rem;
        font-size: 0.875rem;
        line-height: 1.55;
        color: color-mix(in oklab, var(--color-base-content, #64748b) 78%, transparent);
    }
    #delete-user-modal .delete-user-dialog__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
    }
    #delete-user-modal .delete-user-dialog__actions .btn {
        min-width: 5.5rem;
    }
</style>

<dialog id="delete-user-modal" class="delete-user-dialog" aria-labelledby="delete-user-modal-title" aria-describedby="delete-user-modal-message">
    <div class="delete-user-dialog__surface">
        <div class="delete-user-dialog__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="delete-user-dialog__title" id="delete-user-modal-title">Delete user?</h3>
        <p class="delete-user-dialog__message" id="delete-user-modal-message">Are you sure you want to delete this user? This cannot be undone.</p>
        <div class="delete-user-dialog__actions">
            <button type="button" class="btn btn-outline" id="delete-user-modal-no">No</button>
            <button type="button" class="btn btn-error" id="delete-user-modal-yes">Yes</button>
        </div>
    </div>
</dialog>

<script>
(function () {
    var modal = document.getElementById('delete-user-modal');
    var form = document.getElementById('delete-user-form');
    var userIdInput = document.getElementById('delete-user-form-user-id');
    var msg = document.getElementById('delete-user-modal-message');
    var btnNo = document.getElementById('delete-user-modal-no');
    var btnYes = document.getElementById('delete-user-modal-yes');
    if (!modal || !form || !userIdInput || !msg || !btnNo || !btnYes) return;

    document.querySelectorAll('.js-delete-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-user-id') || '';
            var name = this.getAttribute('data-user-name') || '';
            userIdInput.value = id;
            if (name) {
                msg.textContent = 'Are you sure you want to delete user "' + name + '"? This cannot be undone.';
            } else {
                msg.textContent = 'Are you sure you want to delete this user? This cannot be undone.';
            }
            modal.showModal();
        });
    });

    btnNo.addEventListener('click', function () {
        modal.close();
    });

    btnYes.addEventListener('click', function () {
        modal.close();
        form.submit();
    });
})();
</script>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

