<?php
session_start();
require_once 'config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = ''; $email_val = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $email_val = htmlspecialchars($email);
    if (!$email || !$pass) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $stmt = getDB()->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION[SESSION_KEY] = ['id'=>$u['id'],'name'=>$u['full_name'],'email'=>$u['email'],'initials'=>$u['avatar_initials']??generateInitials($u['full_name'])];
            header('Location: index.php'); exit;
        } else { $error = 'Invalid email or password.'; }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — TrackWise</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand-row">
      <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="13" stroke="#3b82f6" stroke-width="1.8"/><path d="M8 14 Q8 8 14 8 Q20 8 20 14" stroke="#3b82f6" stroke-width="1.8" fill="none" stroke-linecap="round"/><circle cx="14" cy="14" r="3" fill="#3b82f6"/></svg>
      <span class="brand-name">TrackWise</span>
    </div>
    <h1 class="auth-heading">Sign in</h1>
    <?php if ($error): ?><div class="auth-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="f-group"><label>Email</label><input type="email" name="email" value="<?= $email_val ?>" placeholder="you@example.com" required></div>
      <div class="f-group"><label>Password</label><input type="password" name="password" placeholder="Password" required></div>
      <button class="auth-btn">Sign In →</button>
    </form>
    <p class="auth-foot">No account? <a href="register.php">Register</a></p>
  </div>
</div>
</body></html>
