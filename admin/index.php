<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';

// Visiting /admin/ always goes to the right page for this session.
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
redirect('admin/login.php');
