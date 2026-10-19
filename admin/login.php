<?php
require_once __DIR__ . '/../includes/config.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $identity = trim((string) ($_POST['identity'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username=? OR email=? LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        redirect('admin/dashboard.php');
    }

    $error = 'Incorrect username/email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Rootlet</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body class="login-body">
    <main class="login-card">
        <a class="brand" href="<?= e(url()) ?>">
            <span class="brand-mark">R</span> Rootlet Solutions
        </a>

        <h1>Welcome back</h1>
        <p>Sign in to manage your blog posts.</p>

        <?php if ($error): ?>
            <div class="alert" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <label>
                Username or email
                <input name="identity" autocomplete="username" required autofocus>
            </label>

            <label>
                Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="button" type="submit">Log in</button>
        </form>

        <a class="back-link" href="<?= e(url('page/blog.html')) ?>">← Back to blogs</a>
    </main>
</body>
</html>

