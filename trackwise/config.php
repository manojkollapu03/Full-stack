<?php
// config.php
define('DB_HOST','localhost'); define('DB_PORT','3306');
define('DB_NAME','trackwise_db'); define('DB_USER','root'); define('DB_PASS','');
define('DB_CHARSET','utf8mb4'); define('APP_NAME','TrackWise');
define('SESSION_KEY','tw_user');

function getDB():PDO {
    static $pdo=null; if($pdo) return $pdo;
    $dsn=sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',DB_HOST,DB_PORT,DB_NAME,DB_CHARSET);
    try { $pdo=new PDO($dsn,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); }
    catch(PDOException $e){ die('<div style="font-family:sans-serif;padding:40px;background:#fef2f2;color:#dc2626;border-radius:8px;max-width:500px;margin:60px auto"><h3>DB Connection Failed</h3><p>Check config.php credentials and that <b>trackwise_db</b> exists.</p><small>'.htmlspecialchars($e->getMessage()).'</small></div>'); }
    return $pdo;
}

function requireLogin():void { if(session_status()===PHP_SESSION_NONE)session_start(); if(empty($_SESSION[SESSION_KEY])){header('Location: login.php');exit;} }
function currentUser():?array { if(session_status()===PHP_SESSION_NONE)session_start(); return $_SESSION[SESSION_KEY]??null; }
function isLoggedIn():bool { if(session_status()===PHP_SESSION_NONE)session_start(); return !empty($_SESSION[SESSION_KEY]); }

// Nav items — removed: add expense, budget, help & support
$nav_items=[
    ['icon'=>'⊞','label'=>'Dashboard',   'page'=>'dashboard'],
    ['icon'=>'≡', 'label'=>'Transactions','page'=>'transactions'],
    ['icon'=>'◈', 'label'=>'Categories',  'page'=>'categories'],
    ['icon'=>'▦', 'label'=>'Reports',     'page'=>'reports'],
    ['icon'=>'✦', 'label'=>'Settings',    'page'=>'settings'],
];

$default_categories=[
    ['name'=>'Food',         'icon'=>'🛒','color'=>'#f97316','type'=>'expense'],
    ['name'=>'Utilities',    'icon'=>'⚡','color'=>'#3b82f6','type'=>'expense'],
    ['name'=>'Travel',       'icon'=>'✈️','color'=>'#06b6d4','type'=>'expense'],
    ['name'=>'Entertainment','icon'=>'🎬','color'=>'#a855f7','type'=>'expense'],
    ['name'=>'Healthcare',   'icon'=>'💊','color'=>'#10b981','type'=>'expense'],
    ['name'=>'Shopping',     'icon'=>'🛍️','color'=>'#f43f5e','type'=>'expense'],
    ['name'=>'Education',    'icon'=>'📚','color'=>'#eab308','type'=>'expense'],
    ['name'=>'Income',       'icon'=>'💰','color'=>'#22c55e','type'=>'income'],
    ['name'=>'Others',       'icon'=>'📌','color'=>'#94a3b8','type'=>'both'],
];
$categories=array_column($default_categories,'name');
$payment_methods=['Card','Bank Transfer','UPI','Cash','Bank'];

function getCategoryIcon(string $cat):string {
    return ['Food'=>'🛒','Utilities'=>'⚡','Travel'=>'✈️','Entertainment'=>'🎬','Healthcare'=>'💊','Shopping'=>'🛍️','Education'=>'📚','Income'=>'💰','Others'=>'📌'][$cat]??'📌';
}
function getCategoryColor(string $cat):string {
    return ['Food'=>'#f97316','Utilities'=>'#3b82f6','Travel'=>'#06b6d4','Entertainment'=>'#a855f7','Healthcare'=>'#10b981','Shopping'=>'#f43f5e','Education'=>'#eab308','Income'=>'#22c55e','Others'=>'#94a3b8'][$cat]??'#94a3b8';
}
function generateInitials(string $name):string {
    $p=explode(' ',trim($name)); $i='';
    foreach(array_slice($p,0,2) as $w) $i.=strtoupper(mb_substr($w,0,1));
    return $i?:'U';
}
