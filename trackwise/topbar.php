<?php
$user = currentUser();
$initials = $user['initials'] ?? 'U';
$search = $_GET['search'] ?? '';
?>
<header class="topbar">
  <form method="GET" class="tb-search">
    <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']??'dashboard') ?>">
    <span class="tb-icon">⌕</span>
    <input type="text" name="search" class="tb-input" placeholder="Search transactions..." value="<?= htmlspecialchars($search) ?>">
  </form>
  <div class="tb-right">
    <div class="tb-avatar"><?= htmlspecialchars($initials) ?></div>
    <span class="tb-uname"><?= htmlspecialchars($user['name']??'') ?></span>
  </div>
</header>
