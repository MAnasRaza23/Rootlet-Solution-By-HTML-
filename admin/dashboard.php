<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/partials.php';

$count = (int) $pdo->query('SELECT COUNT(*) FROM blogs')->fetchColumn();
$recent = $pdo->query('SELECT id, title, post_date FROM blogs ORDER BY post_date DESC, id DESC LIMIT 5')->fetchAll();

admin_start('Dashboard', 'dashboard');
?>
<div class="page-heading row">
    <div class="Left-sec col-lg-6">
        <div>
            <span>
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Iconly/Curved/Paper"> <g id="Paper"> <path id="Stroke 1" d="M14.3053 15.45H8.90527" stroke="#255ac5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path id="Stroke 2" d="M12.2604 11.4387H8.90442" stroke="#255ac5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path id="Stroke 3" fill-rule="evenodd" clip-rule="evenodd" d="M20.1598 8.3L14.4898 2.9C13.7598 2.8 12.9398 2.75 12.0398 2.75C5.74978 2.75 3.64978 5.07 3.64978 12C3.64978 18.94 5.74978 21.25 12.0398 21.25C18.3398 21.25 20.4398 18.94 20.4398 12C20.4398 10.58 20.3498 9.35 20.1598 8.3Z" stroke="#255ac5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path id="Stroke 5" d="M13.9342 2.83276V5.49376C13.9342 7.35176 15.4402 8.85676 17.2982 8.85676H20.2492" stroke="#255ac5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g> </g> </g></svg>
            </span>
        </div>
       <div>
         <h2>Dashboard</h2>
        <p>Manage your Rootlet blog content.</p>
       </div>
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