<?php
require_once __DIR__.'/../includes/config.php';
if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn()>0) { http_response_code(403); exit('An admin already exists. Delete admin/setup.php after setup.'); }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf(); $username=trim((string)($_POST['username']??'')); $email=trim((string)($_POST['email']??'')); $password=(string)($_POST['password']??'');
    if (!preg_match('/^[A-Za-z0-9_]{3,80}$/',$username)) $error='Username must be 3–80 letters, numbers, or underscores.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) $error='Enter a valid email address.';
    elseif (strlen($password)<12) $error='Use a password with at least 12 characters.';
    else { try { $stmt=$pdo->prepare('INSERT INTO admins(username,email,password_hash) VALUES(?,?,?)'); $stmt->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT)]); redirect('admin/login.php'); } catch (PDOException $e) { $error='Could not create admin. Check the database setup.'; } }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Admin</title><link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>"></head><body class="login-body"><main class="login-card"><h1>Create first admin</h1><p>Available only until the first admin is created.</p><?php if ($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><label>Username<input name="username" required minlength="3" maxlength="80" autocomplete="username"></label><label>Email<input type="email" name="email" required autocomplete="email"></label><label>Password<input type="password" name="password" required minlength="12" autocomplete="new-password"></label><button class="button">Create Admin</button></form></main></body></html>
