<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
$currentPage = $_GET['page'] ?? 'report';
?>

<div class="flex gap-6 w-full">
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <main class="flex-1 px-4">
        <div class="space-y-4">
            <h2 class="text-2xl font-semibold text-base-content">Report</h2>
            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg text-success">Report by</h3>
                    <div class="bg-warning/15 border border-warning/40 rounded-box p-6">
                        <p class="text-sm text-base-content/80">Report filters and report tables will be developed in this section.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

