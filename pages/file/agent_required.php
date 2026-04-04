<?php

declare(strict_types=1);

$currentPage = 'file';

require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<div class="flex gap-6 w-full pb-6">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>
    <main class="flex-1 min-w-0 px-0 sm:px-1">
        <div class="max-w-lg">
            <?php $breadcrumbCurrent = 'File / Assignment'; require __DIR__ . '/../../includes/breadcrumb.php'; ?>
            <div class="card bg-base-100 border border-base-300 shadow-md">
                <div class="card-body space-y-4">
                    <h2 class="card-title text-lg text-primary">Agent required</h2>
                    <p class="text-sm text-base-content/80">
                        Please select an agent from the <strong>Home</strong> page before using File / Assignment.
                        This replaces the old popup alert so you can continue in one click.
                    </p>
                    <div class="card-actions">
                        <a href="index.php?page=home" class="btn btn-primary btn-sm">Go back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
