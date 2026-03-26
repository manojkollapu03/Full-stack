<?php
global $categories, $payment_methods, $filter_category, $filter_date_range;
$filter_category   = $filter_category   ?? 'All';
$filter_date_range = $filter_date_range ?? 'This Month';
?>
<div class="panel-card">
  <h3 class="panel-title">Add New Expense</h3>
  <form method="POST" class="expense-form">
    <input type="hidden" name="action" value="add_expense">
    <div class="form-group"><label>Title</label><input type="text" name="title" class="form-input" placeholder="e.g. Coffee, Rent, Salary..." required></div>
    <div class="form-group"><label>Category</label>
      <select name="category" class="form-input" required><option value="">Select Category</option>
        <?php foreach($categories as $c): ?><option value="<?=$c?>"><?=$c?></option><?php endforeach; ?>
      </select></div>
    <div class="form-group"><label>Type</label>
      <div class="type-toggle">
        <label class="type-opt"><input type="radio" name="type" value="expense" checked><span>Expense</span></label>
        <label class="type-opt"><input type="radio" name="type" value="income"><span>Income</span></label>
      </div></div>
    <div class="form-group"><label>Amount</label><input type="number" name="amount" class="form-input" placeholder="0.00" step="0.01" min="0.01" required></div>
    <div class="form-group"><label>Date</label><input type="date" name="date" class="form-input" value="<?=date('Y-m-d')?>" required></div>
    <div class="form-group"><label>Payment Method</label>
      <select name="method" class="form-input"><?php foreach($payment_methods as $m): ?><option><?=$m?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label>Notes <span class="opt">(optional)</span></label><input type="text" name="notes" class="form-input" placeholder="Additional notes..."></div>
    <button type="submit" class="add-btn">+ Add</button>
  </form>
</div>
<div class="panel-card">
  <h3 class="panel-title">Filters</h3>
  <form method="GET">
    <input type="hidden" name="page" value="dashboard">
    <div class="form-group"><label>Category</label>
      <select name="filter_category" class="form-input" onchange="this.form.submit()">
        <option value="All" <?=$filter_category==='All'?'selected':''?>>All</option>
        <?php foreach($categories as $c): ?><option value="<?=$c?>" <?=$filter_category===$c?'selected':''?>><?=$c?></option><?php endforeach; ?>
      </select></div>
    <div class="form-group"><label>Date Range</label>
      <select name="filter_date_range" class="form-input" onchange="this.form.submit()">
        <?php foreach(['This Month','Last Month','Last 3 Months','This Year','All Time'] as $d): ?>
          <option <?=$filter_date_range===$d?'selected':''?>><?=$d?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="filter-btns"><a href="?" class="btn-reset">Reset</a><button type="submit" class="btn-apply">Apply</button></div>
  </form>
</div>
