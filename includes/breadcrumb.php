<?php
// Reusable breadcrumb bar for content pages.
// Expects $breadcrumbCurrent string from caller.
// Optional: $breadcrumbParentLabel + $breadcrumbParentHref for a linked segment before the current page.
$breadcrumbCurrent = $breadcrumbCurrent ?? 'Page';
$breadcrumbParentLabel = $breadcrumbParentLabel ?? '';
$breadcrumbParentHref = $breadcrumbParentHref ?? '';
?>
<nav class="flex w-full p-3 bg-base-100 border border-base-300 rounded-box shadow-sm" aria-label="Breadcrumb">
    <ol class="inline-flex flex-wrap items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        <li class="inline-flex items-center">
            <a href="index.php?page=home" class="inline-flex items-center text-sm font-medium text-base-content/80 hover:text-primary">
                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/>
                </svg>
                Home
            </a>
        </li>
        <?php if ($breadcrumbParentLabel !== '' && $breadcrumbParentHref !== ''): ?>
            <li>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180 text-base-content/50" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                    </svg>
                    <a href="<?= h((string) $breadcrumbParentHref) ?>" class="text-sm font-medium text-base-content/80 hover:text-primary"><?= h((string) $breadcrumbParentLabel) ?></a>
                </div>
            </li>
        <?php endif; ?>
        <li aria-current="page">
            <div class="flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 rtl:rotate-180 text-base-content/50" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/>
                </svg>
                <span class="inline-flex items-center text-sm font-medium text-base-content"><?= h((string) $breadcrumbCurrent) ?></span>
            </div>
        </li>
    </ol>
</nav>

