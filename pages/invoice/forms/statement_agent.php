<?php
declare(strict_types=1);
?>
<div class="invoice-form-fieldstack">
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
    <label class="form-control"><span class="label-text text-xs font-semibold">Search Country</span>
      <select name="country" class="select select-bordered select-sm w-full bg-white"><option value="">Select</option><?php $sel=(string)($fv['country']??''); foreach($countries as $c){$s=$sel===$c?' selected':''; echo '<option value="'.h($c).'"'.$s.'>'.h($c).'</option>'; } ?></select>
    </label>
    <label class="form-control"><span class="label-text text-xs font-semibold">Select City</span>
      <select name="city" class="select select-bordered select-sm w-full bg-white"><option value="">Select</option><?php $sel=(string)($fv['city']??''); foreach($cities as $c){$s=$sel===$c?' selected':''; echo '<option value="'.h($c).'"'.$s.'>'.h($c).'</option>'; } ?></select>
    </label>
  </div>
  <label class="form-control w-full max-w-md"><span class="label-text text-xs font-semibold">Agent Name</span>
    <select name="search_word" class="select select-bordered select-sm w-full bg-white"><option value="">Select</option><?php $sel=(string)($fv['search_word']??''); foreach($agents as $a){$s=$sel===$a?' selected':''; echo '<option value="'.h($a).'"'.$s.'>'.h($a).'</option>'; } ?></select>
  </label>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
    <label class="form-control"><span class="label-text text-xs font-semibold">From Date</span><input type="text" name="from_date" class="input input-bordered input-sm w-full bg-white js-invoice-date-input" placeholder="yyyy-mm-dd" value="<?= h((string) ($fv['from_date'] ?? date('Y-m-01'))) ?>"></label>
    <label class="form-control"><span class="label-text text-xs font-semibold">To Date</span><input type="text" name="to_date" class="input input-bordered input-sm w-full bg-white js-invoice-date-input" placeholder="yyyy-mm-dd" value="<?= h((string) ($fv['to_date'] ?? date('Y-m-d'))) ?>"></label>
  </div>
</div>
