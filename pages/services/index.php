<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/nav.php';
?>

<?php
$currentPage = $_GET['page'] ?? 'home';
?>

<div class="flex gap-6 w-full">
    <!-- Left sidebar -->
    <aside class="hidden lg:block w-72 shrink-0">
        <?php require __DIR__ . '/sidebar.php'; ?>
    </aside>

    <!-- Main content -->
    <main class="flex-1 px-4">
        <div class="space-y-4">
            <h2 class="text-2xl font-semibold text-base-content">Services</h2>

            <div class="card bg-base-100 shadow-xl border border-base-300">
                <div class="card-body">
                    <h3 class="card-title text-lg">Service setup</h3>
                    <p class="text-base-content/70 text-sm mb-4">
                        Transfer and tour services will be managed here (same data as legacy <em>Service Setup</em>).
                    </p>
                    <div class="bg-warning/15 border border-warning/40 rounded-box p-6 text-center">
                        <p class="text-base-content/80">Coming next: country / city / type filters and service table.</p>
                        <div class="card-actions justify-center mt-4">
                            <button type="button" class="btn btn-primary btn-outline" disabled>Configure services</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

