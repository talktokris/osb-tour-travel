<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>

<div class="space-y-4">
    <h2 class="text-2xl font-semibold text-base-content">Bookings</h2>

    <div class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body">
            <h3 class="card-title text-lg">Files &amp; assignments</h3>
            <p class="text-base-content/70 text-sm mb-4">
                Booking search and file entry will mirror the legacy <em>File / Assignment</em> and <em>Search</em> flows.
            </p>
            <div class="bg-warning/15 border border-warning/40 rounded-box p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Guest / file</span></label>
                        <input type="text" class="input input-bordered" placeholder="Name or file no." disabled>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Service date</span></label>
                        <input type="date" class="input input-bordered" disabled>
                    </div>
                </div>
                <div class="card-actions justify-end mt-4">
                    <button type="button" class="btn btn-primary" disabled>Search bookings</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
