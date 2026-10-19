<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    define('BLOG_API_REQUEST', true);
    require_once __DIR__ . '/../includes/config.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        header('Allow: GET');
        echo json_encode(['error' => 'Method not allowed.']);
        exit;
    }
    $detail = array_key_exists('id', $_GET);
    if ($detail) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid blog ID.']);
            exit;
        }
        $stmt = $pdo->prepare('SELECT id,title,image,posted_by,post_date,content FROM blogs WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) {
            http_response_code(404);
            echo json_encode(['error' => 'Blog not found.']);
            exit;
        }
        $posts = [$post];
    } else {
        $limit = null;
        if (isset($_GET['limit'])) {
            $limit = filter_var($_GET['limit'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
            if ($limit === false) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid result limit.']);
                exit;
            }
        }
        $sql = 'SELECT id,title,image,posted_by,post_date,content FROM blogs ORDER BY post_date DESC,id DESC';
        if ($limit !== null) $sql .= ' LIMIT ' . (int)$limit;
        $posts = $pdo->query($sql)->fetchAll();
    }
    foreach ($posts as &$post) {
        $post['id'] = (int)$post['id'];
        $post['image_url'] = url('uploads/' . basename($post['image']));
        $post['formatted_date'] = date('F j, Y', strtotime($post['post_date']));
        $post['excerpt'] = excerpt($post['content']);
        if ($detail) $post['content'] = safe_content($post['content']);
        else unset($post['content']);
        unset($post['image']);
    }
    unset($post);
    echo json_encode($detail ? ['post' => $posts[0]] : ['posts' => $posts], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load blogs. Please try again later.']);
}
