<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
$currentPage = $_GET['page'] ?? 'file';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <?php $breadcrumbCurrent = 'File / Assignment'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg text-success">Transfer Search</h3>
                    <div class="bg-warning/15 border border-warning/40 rounded-box p-6">
                        <p class="text-sm text-base-content/80">File and assignment workflow page is ready for migration from legacy module.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

