<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/partials.php';

$posts = $pdo->query(
    'SELECT id, title, image, posted_by, post_date FROM blogs ORDER BY post_date DESC, id DESC'
)->fetchAll();

$postCount = count($posts);

$status = [
    'created' => 'Blog published.',
    'updated' => 'Blog updated.',
    'deleted' => 'Blog deleted.',
];

admin_start('All Blogs', 'blogs');
?>
<div class="page-heading">
    <div>
        <h1>All Blogs</h1>
        <p><?= $postCount ?> published post<?= $postCount === 1 ? '' : 's' ?></p>
    </div>
    <a class="button" href="<?= e(url('admin/add-blog.php')) ?>">+ Add Blog</a>
</div>

<?php if (isset($status[$_GET['status'] ?? ''])): ?>
    <div class="notice" role="status"><?= e($status[$_GET['status']]) ?></div>
<?php endif; ?>

<div class="panel table-wrap" id="edit">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Blog Title</th>
                <th>Posted By</th>
                <th>Post Date</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= (int) $post['id'] ?></td>
                    <td>
                        <img class="thumb" src="<?= e(url('uploads/' . $post['image'])) ?>" alt="">
                    </td>
                    <td>
                        <a href="<?= e(url('page/blog-detail.html?id=' . $post['id'])) ?>" target="_blank" rel="noopener">
                            <?= e($post['title']) ?>
                        </a>
                    </td>
                    <td><?= e($post['posted_by']) ?></td>
                    <td><?= e($post['post_date']) ?></td>
                    <td>
                        <a class="table-action" href="<?= e(url('admin/edit-blog.php?id=' . $post['id'])) ?>">Edit</a>
                    </td>
                    <td>
                        <form action="<?= e(url('admin/delete-blog.php')) ?>" method="post" data-confirm-delete>
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$posts): ?>
                <tr>
                    <td colspan="7">No blogs yet. Add your first post.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php admin_end(); ?>
