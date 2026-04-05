<?php

declare(strict_types=1);

/** @var string $csrf */
/** @var string $redirect */
?>
<dialog id="search-delete-dialog" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete record</h3>
        <p class="py-3 text-sm">Are you sure you want to delete this transfer line? This cannot be undone.</p>
        <p class="text-sm text-base-content/70 mb-4"><span id="search-delete-label"></span></p>
        <div class="modal-action">
            <form method="post" action="index.php?page=search_delete" class="flex flex-wrap gap-2 justify-end w-full">
                <input type="hidden" name="_token" value="<?= h($csrf) ?>">
                <input type="hidden" name="file_id" id="search-delete-file-id" value="">
                <input type="hidden" name="redirect" value="<?= h($redirect) ?>">
                <button type="button" class="btn" onclick="document.getElementById('search-delete-dialog').close()">Cancel</button>
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
<script>
(function () {
    var dlg = document.getElementById('search-delete-dialog');
    var idInput = document.getElementById('search-delete-file-id');
    var labelEl = document.getElementById('search-delete-label');
    if (!dlg || !idInput) return;
    document.querySelectorAll('.js-search-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            idInput.value = btn.getAttribute('data-file-id') || '';
            if (labelEl) labelEl.textContent = btn.getAttribute('data-label') || '';
            dlg.showModal();
        });
    });
})();
</script>
