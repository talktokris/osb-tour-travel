<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>

<div class="space-y-4">
    <h2 class="text-2xl font-semibold text-base-content">Home</h2>

    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h3 class="card-title text-lg">Direct Transport Booking – Quick Search</h3>
            <div class="bg-warning/15 border border-warning/40 rounded-box p-4 md:p-6">
                <form class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium">Country</span></label>
                            <input type="text" class="input input-bordered w-full" placeholder="Select country">
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium">City</span></label>
                            <input type="text" class="input input-bordered w-full" placeholder="Select city">
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium">Agent name</span></label>
                            <input type="text" class="input input-bordered w-full" placeholder="Search by agent">
                        </div>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text font-medium">File no.</span></label>
                            <input type="text" class="input input-bordered w-full" placeholder="File number">
                        </div>
                        <div class="form-control w-full md:col-span-2">
                            <label class="label"><span class="label-text font-medium">Service date</span></label>
                            <input type="date" class="input input-bordered w-full max-w-xs">
                        </div>
                    </div>
                    <div class="card-actions justify-end">
                        <button type="button" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
            <p class="text-base-content/70 text-sm mt-4">
                Welcome, <span class="font-medium text-base-content"><?= h($_SESSION['user_name'] ?? 'User') ?></span>.
                Use the tabs above for agents, services, and bookings. Dashboard stats will appear here as we migrate features from the legacy system.
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
