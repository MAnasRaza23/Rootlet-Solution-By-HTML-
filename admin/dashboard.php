<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/partials.php';

$count = (int) $pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn();
$recent = $pdo->query('SELECT id, title, post_date FROM blogs ORDER BY post_date DESC, id DESC LIMIT 5')->fetchAll();

admin_start('Dashboard', 'dashboard');
?>
<div class="page-heading row">
    <div class="Left-sec col-lg-6">
        <h1>Dashboard</h1>
        <p>Manage your Rootlet blog content.</p>
    </div>

    <div class=" right-sec col-lg-6">
        <a class="button" href="<?= e(url('admin/add-blog.php')) ?>"><i class="fa-regular fa-pen-to-square"></i> Add
            Blog</a>
    </div>

</div>

<div class="stat-card row">
    <div class="col-lg-4 cols">
        <h5>Total posts</h5>
        
    </div>
    <div class="col-lg-4 cols">
        <h6><?= $count ?></h6>       
    </div>
    <div class="col-lg-4 cols">
        <a href="<?= e(url('admin/blogs.php')) ?>">View all blogs  <i class="fa-solid fa-arrow-right-long"></i></a>
    </div>

</div>

<section class="panel">
    <h2>Recent posts</h2>

    <?php if (!$recent): ?>
        <p>No blogs yet. Create your first post.</p>
    <?php else: ?>
        <ul class="recent-list">
            <?php foreach ($recent as $post): ?>
                <li>
                    <a href="<?= e(url('admin/edit-blog.php?id=' . $post['id'])) ?>"><?= e($post['title']) ?></a>
                    <time><?= e($post['post_date']) ?></time>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php admin_end(); ?>