<?php
session_start();
require_once 'config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = ''; $vals = ['name'=>'','email'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email']     ?? '');
    $pass  = trim($_POST['password']  ?? '');
    $cpass = trim($_POST['confirm_pw']?? '');
    $vals  = ['name'=>htmlspecialchars($name),'email'=>htmlspecialchars($email)];

    if (!$name||!$email||!$pass||!$cpass)       { $error = 'All fields are required.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
    elseif (strlen($pass)<6)                    { $error = 'Password must be at least 6 characters.'; }
    elseif ($pass !== $cpass)                   { $error = 'Passwords do not match.'; }
    else {
        $db = getDB();
        $chk = $db->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $init = generateInitials($name);
            $ins  = $db->prepare('INSERT INTO users (full_name,email,password_hash,avatar_initials) VALUES(?,?,?,?)');
            $ins->execute([$name,$email,$hash,$init]);
            $uid = (int)$db->lastInsertId();
            global $default_categories;
            $cs = $db->prepare('INSERT INTO categories (user_id,name,icon,color,type) VALUES(?,?,?,?,?)');
            foreach($default_categories as $c) $cs->execute([$uid,$c['name'],$c['icon'],$c['color'],$c['type']]);
            session_regenerate_id(true);
            $_SESSION[SESSION_KEY] = ['id'=>$uid,'name'=>$name,'email'=>$email,'initials'=>$init];
            header('Location: index.php?welcome=1'); exit;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — TrackWise</title>
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
    <h1 class="auth-heading">Create account</h1>
    <?php if ($error): ?><div class="auth-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="f-group"><label>Full Name</label><input type="text" name="full_name" value="<?= $vals['name'] ?>" placeholder="John Doe" required></div>
      <div class="f-group"><label>Email</label><input type="email" name="email" value="<?= $vals['email'] ?>" placeholder="you@example.com" required></div>
      <div class="f-group"><label>Password</label><input type="password" name="password" placeholder="Min. 6 characters" required></div>
      <div class="f-group"><label>Confirm Password</label><input type="password" name="confirm_pw" placeholder="Repeat password" required></div>
      <button class="auth-btn">Create Account →</button>
    </form>
    <p class="auth-foot">Have an account? <a href="login.php">Sign in</a></p>
  </div>
</div>
</body></html>
