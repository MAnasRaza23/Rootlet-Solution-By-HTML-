<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

check_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('admin/blogs.php');
}

$stmt = $pdo->prepare('SELECT image FROM blogs WHERE id=?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post) {
    $stmt = $pdo->prepare('DELETE FROM blogs WHERE id=?');
    $stmt->execute([$id]);
    remove_image($post['image']);
}

redirect('admin/blogs.php?status=deleted');

