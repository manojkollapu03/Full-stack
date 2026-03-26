<?php
global $nav_items;
$page = $_GET['page'] ?? 'dashboard';
$user = currentUser();
$initials = $user['initials'] ?? generateInitials($user['name'] ?? 'U');
?>
<aside class="sidebar">
  <div class="sb-brand">
    <svg width="26" height="26" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="13" stroke="#3b82f6" stroke-width="1.8"/><path d="M8 14 Q8 8 14 8 Q20 8 20 14" stroke="#3b82f6" stroke-width="1.8" fill="none" stroke-linecap="round"/><circle cx="14" cy="14" r="3" fill="#3b82f6"/></svg>
    <span class="sb-title">TrackWise</span>
  </div>
  <nav class="sb-nav">
    <?php foreach($nav_items as $item): $active=($page===$item['page']); ?>
    <a href="?page=<?= $item['page'] ?>" class="sb-link<?= $active?' active':'' ?>">
      <span class="sb-icon"><?= $item['icon'] ?></span>
      <span><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sb-footer">
    <a href="logout.php" class="sb-link sb-logout" onclick="return confirm('Sign out?')">
      <span class="sb-icon">⎋</span><span>Sign Out</span>
    </a>
    <div class="sb-user">
      <div class="sb-avatar"><?= htmlspecialchars($initials) ?></div>
      <div class="sb-info">
        <span class="sb-name"><?= htmlspecialchars($user['name']??'User') ?></span>
        <span class="sb-email"><?= htmlspecialchars($user['email']??'') ?></span>
      </div>
    </div>
  </div>
</aside>
