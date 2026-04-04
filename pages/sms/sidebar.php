<?php
$smsPage = $_GET['page'] ?? 'sms';
?>
<aside class="module-sidebar">
    <div class="module-sidebar__head">SMS Menu</div>
    <div class="px-3 pt-2 pb-1 text-[10px] uppercase tracking-wider text-slate-500 font-medium">Messaging</div>
    <ul class="menu">
        <li class="<?= $smsPage === 'sms' ? 'active' : '' ?>"><a href="index.php?page=sms">Send SMS List <span class="opacity-50">..</span></a></li>
        <li class="<?= $smsPage === 'sms_test' ? 'active' : '' ?>"><a href="index.php?page=sms_test">SMS Test <span class="opacity-50">..</span></a></li>
        <li class="<?= $smsPage === 'sms_credit' ? 'active' : '' ?>"><a href="index.php?page=sms_credit">SMS Credit <span class="opacity-50">..</span></a></li>
        <li class="<?= $smsPage === 'sms_history' ? 'active' : '' ?>"><a href="index.php?page=sms_history">SMS List <span class="opacity-50">..</span></a></li>
    </ul>
</aside>
