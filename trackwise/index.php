<?php
session_start();
require_once 'config.php';
requireLogin();

$user    = currentUser();
$user_id = (int)$user['id'];
$db      = getDB();
$msg     = '';
$page    = $_GET['page'] ?? 'dashboard';

// POST HANDLERS
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (($_POST['action']??'')==='add_expense') {
        $title  = trim($_POST['title']    ?? '');
        $cat    = trim($_POST['category'] ?? '');
        $amount = floatval($_POST['amount']??0);
        $date   = trim($_POST['date']     ?? '');
        $notes  = trim($_POST['notes']    ?? '');
        $type   = trim($_POST['type']     ?? 'expense');
        $method = trim($_POST['method']   ?? 'Card');
        $desc   = $title ?: ($notes ?: $cat);
        if ($cat && $amount>0 && $date) {
            $real = $type==='income' ? $amount : -$amount;
            $db->prepare('INSERT INTO transactions (user_id,description,category,amount,type,payment_method,tx_date,notes) VALUES(?,?,?,?,?,?,?,?)')
               ->execute([$user_id,$desc,$cat,$real,$type,$method,$date,$notes]);
            $msg='success';
        } else { $msg='error'; }
    }
    if (($_POST['action']??'')==='delete') {
        $db->prepare('DELETE FROM transactions WHERE id=? AND user_id=?')
           ->execute([(int)$_POST['delete_id'],$user_id]);
    }
}

// STATS
$s=$db->prepare('SELECT SUM(CASE WHEN type="income" THEN amount ELSE 0 END) AS inc, SUM(CASE WHEN type="expense" THEN ABS(amount) ELSE 0 END) AS exp FROM transactions WHERE user_id=?');
$s->execute([$user_id]); $stats=$s->fetch();
$total_inc=(float)($stats['inc']??0); $total_exp=(float)($stats['exp']??0); $total_bal=$total_inc-$total_exp;

// FILTERS
$filter_category   = $_GET['filter_category']   ?? 'All';
$filter_date_range = $_GET['filter_date_range']  ?? 'This Month';
$search            = trim($_GET['search']        ?? '');

$where='WHERE user_id=:uid'; $p=[':uid'=>$user_id];
if ($filter_category!=='All') { $where.=' AND category=:cat'; $p[':cat']=$filter_category; }
switch($filter_date_range) {
    case 'Last Month':    $where.=' AND tx_date BETWEEN :s AND :e'; $p[':s']=date('Y-m-01',strtotime('first day of last month')); $p[':e']=date('Y-m-t',strtotime('last day of last month')); break;
    case 'Last 3 Months': $where.=' AND tx_date>=:s'; $p[':s']=date('Y-m-d',strtotime('-3 months')); break;
    case 'This Year':     $where.=' AND YEAR(tx_date)=:yr'; $p[':yr']=date('Y'); break;
    case 'All Time':      break;
    default:              $where.=' AND YEAR(tx_date)=:yr AND MONTH(tx_date)=:mo'; $p[':yr']=date('Y'); $p[':mo']=date('n');
}
if ($search) { $where.=' AND (description LIKE :s1 OR category LIKE :s2)'; $p[':s1']="%$search%"; $p[':s2']="%$search%"; }

$stmt=$db->prepare("SELECT * FROM transactions $where ORDER BY tx_date DESC,id DESC LIMIT 100");
$stmt->execute($p); $filtered=$stmt->fetchAll();

// Monthly data (reports)
$ms=$db->prepare('SELECT MONTHNAME(tx_date) AS mon,SUM(ABS(amount)) AS total FROM transactions WHERE user_id=? AND type="expense" AND YEAR(tx_date)=YEAR(NOW()) GROUP BY MONTH(tx_date),mon ORDER BY MONTH(tx_date)');
$ms->execute([$user_id]); $monthly_data=array_column($ms->fetchAll(),'total','mon');

// Spending by category (categories page)
$cs=$db->prepare('SELECT category,SUM(ABS(amount)) AS total FROM transactions WHERE user_id=? AND type="expense" AND YEAR(tx_date)=YEAR(NOW()) AND MONTH(tx_date)=MONTH(NOW()) GROUP BY category ORDER BY total DESC');
$cs->execute([$user_id]); $spending_by_cat=array_column($cs->fetchAll(),'total','category');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=ucfirst($page)?> — TrackWise</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="components.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-wrap">
  <?php include 'topbar.php'; ?>
  <div class="content">

  <?php if(isset($_GET['welcome'])): ?>
  <div class="welcome-bar">👋 Welcome, <b><?=htmlspecialchars($user['name'])?></b>! Add your first transaction using the panel on the right.
    <button onclick="this.parentElement.remove()">✕</button></div>
  <?php endif; ?>

  <?php if($page==='dashboard'): ?>
  <!-- DASHBOARD -->
  <div class="page-head"><h1>Dashboard</h1><span class="date-chip"><?=date('d M Y')?></span></div>
  <div class="dash-layout">
    <div class="dash-left">
      <div class="stat-grid">
        <div class="stat-card bal"><div class="stat-lbl">Total Balance</div><div class="stat-val">$<?=number_format($total_bal,2)?></div><div class="stat-chg <?=$total_bal>=0?'pos':'neg'?>"><?=$total_bal>=0?'↑ Positive':'↓ Negative'?></div></div>
        <div class="stat-card exp"><div class="stat-lbl">Total Expenses</div><div class="stat-val">$<?=number_format($total_exp,2)?></div><div class="stat-chg neg">↓ All time</div></div>
        <div class="stat-card inc"><div class="stat-lbl">Total Income</div><div class="stat-val">$<?=number_format($total_inc,2)?></div><div class="stat-chg pos">↑ All time</div></div>
      </div>
      <div class="card">
        <div class="card-head"><h3>Recent Transactions</h3><a href="?page=transactions" class="link-sm">View All →</a></div>
        <table class="tx-table">
          <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Method</th><th></th></tr></thead>
          <tbody>
          <?php foreach(array_slice($filtered,0,6) as $tx): ?>
          <tr>
            <td class="tx-date"><?=date('d M Y',strtotime($tx['tx_date']))?></td>
            <td class="tx-desc"><span><?=getCategoryIcon($tx['category'])?></span> <?=htmlspecialchars($tx['description'])?></td>
            <td><span class="cat-pill cat-<?=strtolower($tx['category'])?>"><?=htmlspecialchars($tx['category'])?></span></td>
            <td class="<?=$tx['amount']>=0?'pos':'neg'?>"><?=$tx['amount']>=0?'+':''?>$<?=number_format(abs($tx['amount']),2)?></td>
            <td><span class="method-pill"><?=htmlspecialchars($tx['payment_method'])?></span></td>
            <td><form method="POST" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="delete_id" value="<?=$tx['id']?>"><button class="del-btn" onclick="return confirm('Delete?')">✕</button></form></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($filtered)): ?><tr><td colspan="6" class="empty">No transactions yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="dash-right"><?php include 'panel.php'; ?></div>
  </div>

  <?php elseif($page==='transactions'): ?>
  <!-- TRANSACTIONS -->
  <div class="page-head"><h1>Transactions</h1><span class="date-chip"><?=count($filtered)?> records</span></div>
  <!-- filter bar -->
  <form method="GET" class="filter-bar">
    <input type="hidden" name="page" value="transactions">
    <select name="filter_category" class="filter-sel" onchange="this.form.submit()">
      <option value="All" <?=$filter_category==='All'?'selected':''?>>All Categories</option>
      <?php foreach($categories as $c): ?><option value="<?=$c?>" <?=$filter_category===$c?'selected':''?>><?=$c?></option><?php endforeach; ?>
    </select>
    <select name="filter_date_range" class="filter-sel" onchange="this.form.submit()">
      <?php foreach(['This Month','Last Month','Last 3 Months','This Year','All Time'] as $d): ?>
        <option <?=$filter_date_range===$d?'selected':''?>><?=$d?></option>
      <?php endforeach; ?>
    </select>
    <a href="?page=transactions" class="btn-reset">Reset</a>
  </form>
  <div class="card">
    <table class="tx-table">
      <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Method</th><th>Type</th><th></th></tr></thead>
      <tbody>
      <?php foreach($filtered as $tx): ?>
      <tr>
        <td class="tx-date"><?=date('d M Y',strtotime($tx['tx_date']))?></td>
        <td class="tx-desc"><span><?=getCategoryIcon($tx['category'])?></span> <?=htmlspecialchars($tx['description'])?></td>
        <td><span class="cat-pill cat-<?=strtolower($tx['category'])?>"><?=htmlspecialchars($tx['category'])?></span></td>
        <td class="<?=$tx['amount']>=0?'pos':'neg'?>"><?=$tx['amount']>=0?'+':''?>$<?=number_format(abs($tx['amount']),2)?></td>
        <td><span class="method-pill"><?=htmlspecialchars($tx['payment_method'])?></span></td>
        <td><span class="type-chip <?=$tx['type']?>"><?=ucfirst($tx['type'])?></span></td>
        <td><form method="POST" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="delete_id" value="<?=$tx['id']?>"><button class="del-btn" onclick="return confirm('Delete?')">✕</button></form></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($filtered)): ?><tr><td colspan="7" class="empty">No transactions found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php elseif($page==='categories'): ?>
  <!-- CATEGORIES — spending by category -->
  <div class="page-head"><h1>Categories</h1><span class="sub">This month's breakdown</span></div>
  <div class="cat-layout">
    <div class="card">
      <h3 style="margin-bottom:16px">Spending by Category</h3>
      <div class="donut-wrap">
        <canvas id="donutChart" width="180" height="180"></canvas>
        <div class="donut-center">
          <span class="donut-total">$<?=number_format(array_sum($spending_by_cat),0)?></span>
          <span class="donut-lbl">Total</span>
        </div>
      </div>
    </div>
    <div class="card">
      <h3 style="margin-bottom:16px">Breakdown</h3>
      <?php if(empty($spending_by_cat)): ?><p class="empty">No expenses this month.</p>
      <?php else: $total_cat=array_sum($spending_by_cat); foreach($spending_by_cat as $cat=>$amt): $pct=$total_cat>0?round($amt/$total_cat*100):0; ?>
        <div class="cat-row">
          <div class="cat-info"><span class="cat-dot" style="background:<?=getCategoryColor($cat)?>"></span><span><?=getCategoryIcon($cat)?> <?=$cat?></span></div>
          <div class="cat-bar-wrap"><div class="cat-bar" style="width:<?=$pct?>%;background:<?=getCategoryColor($cat)?>"></div></div>
          <div class="cat-meta"><span class="cat-pct"><?=$pct?>%</span><span class="cat-amt">$<?=number_format($amt,2)?></span></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <?php elseif($page==='reports'): ?>
  <!-- REPORTS — monthly chart -->
  <div class="page-head"><h1>Reports</h1><span class="sub">Monthly overview — <?=date('Y')?></span></div>
  <div class="card" style="margin-bottom:20px">
    <div class="card-head">
      <h3>Monthly Expense Overview</h3>
      <div class="chart-toggle">
        <button class="tgl active" onclick="switchChart('monthly',this)">Monthly</button>
        <button class="tgl" onclick="switchChart('yearly',this)">Yearly</button>
      </div>
    </div>
    <div class="chart-wrap"><canvas id="expenseChart"></canvas></div>
  </div>
  <div class="report-stats">
    <div class="card rstat"><div class="rstat-lbl">Total Spent This Year</div><div class="rstat-val">$<?=number_format(array_sum($monthly_data),2)?></div></div>
    <div class="card rstat"><div class="rstat-lbl">Monthly Average</div><div class="rstat-val">$<?=count($monthly_data)>0?number_format(array_sum($monthly_data)/count($monthly_data),2):'0.00'?></div></div>
    <div class="card rstat"><div class="rstat-lbl">Highest Month</div><div class="rstat-val"><?=count($monthly_data)>0?array_search(max($monthly_data),$monthly_data).' · $'.number_format(max($monthly_data),0):'—'?></div></div>
  </div>

  <?php elseif($page==='settings'): ?>
  <!-- SETTINGS -->
  <div class="page-head"><h1>Settings</h1></div>
  <div class="settings-layout">
    <div class="card"><h3 style="margin-bottom:16px">Account</h3>
      <div class="settings-info">
        <div class="si-row"><span class="si-lbl">Name</span><span class="si-val"><?=htmlspecialchars($user['name']??'')?></span></div>
        <div class="si-row"><span class="si-lbl">Email</span><span class="si-val"><?=htmlspecialchars($user['email']??'')?></span></div>
      </div>
    </div>
    <div class="card"><h3 style="margin-bottom:16px">Actions</h3>
      <a href="logout.php" class="sa-btn danger" onclick="return confirm('Sign out?')">Sign Out</a>
    </div>
  </div>

  <?php endif; ?>
  </div>
</div>
<script src="app.js"></script>
<script>
<?php if($page==='reports'): ?>initBarChart(<?=json_encode($monthly_data)?>);<?php endif; ?>
<?php if($page==='categories'): ?>initDonutChart(<?=json_encode($spending_by_cat)?>);<?php endif; ?>
<?php if($msg==='success'): ?>showToast('Transaction added!','success');<?php elseif($msg==='error'): ?>showToast('Fill required fields.','error');<?php endif; ?>
</script>
</body></html>
