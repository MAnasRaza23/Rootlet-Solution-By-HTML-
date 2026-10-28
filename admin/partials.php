<?php

function admin_start(string $title, string $active = ''): void
{
?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?= e($title) ?> | Rootlet Admin</title>

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous">
        <link
            rel="stylesheet"
            href="<?= e(url('assets/css/admin.css')) ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css" integrity="sha512-x9WwyMYBnlXMNQ6kQ/Lyzu1NqIhLQKL5Oq6xByfXuRj7s9CskyCbLv/1IjqzJmXwFXWr0ov6jBV7Qbc0hh9nHg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    </head>

    <body>
        <div class="container-fluid admin-layout">
            <div class="row admin-row">
                <aside id="sidebar" class="col-lg-2 sidebar-column">
                    <a
                        href="<?= e(url('admin/dashboard.php')) ?>"
                        class="brand-logo">
                        <img
                            class="logo"
                            src="<?= e(url('assets/images/rootlet-white-logo.png')) ?>"
                            alt="Rootlet logo">
                    </a>

                    <nav class="manu" aria-label="Admin-">
                        <a
                            class="<?= $active === 'dashboard' ? 'active' : '' ?>"
                            href="<?= e(url('admin/dashboard.php')) ?>">
                            <span><i class="fa-regular fa-house"></i></span>
                            Dashboard
                        </a>

                        <a
                            class="<?= $active === 'add' ? 'active' : '' ?>"
                            href="<?= e(url('admin/add-blog.php')) ?>">
                            <span><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <circle cx="12" cy="12" r="10" stroke="#ffffff" stroke-width="1.5"></circle>
                                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path>
                                    </g>
                                </svg></span>
                            Add Blog
                        </a>

                        <a
                            class="<?= $active === 'blogs' ? 'active' : '' ?>"
                            href="<?= e(url('admin/blogs.php')) ?>">
                            <span><i class="fa-solid fa-list-ul"></i></span>
                            All Blogs
                        </a>

                        <a href="<?= e(url('admin/blogs.php#edit')) ?>">
                            <span><i class="fa-regular fa-pen-to-square"></i></span>
                            Edit Blog
                        </a>

                        <a
                            href="<?= e(url('page/blog.html')) ?>"
                            target="_blank"
                            rel="noopener">
                            <span><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                            View Website
                        </a>
                    </nav>

                    <form
                        action="<?= e(url('admin/logout.php')) ?>"
                        method="post"
                        class="logOut">
                        <?= csrf_field() ?>

                        <button class="logout" type="submit">
                            Log out
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </aside>
                <div class="col-lg-10 workspace-column">
                    <div class="workspace">
                        <header class="topbar">
                            <button
                                class="menu-toggle"
                                type="button"
                                aria-controls="sidebar"
                                aria-expanded="false"
                                aria-label="Toggle admin menu">
                                <span aria-hidden="true">☰</span>
                            </button>

                            <span class="page-name"><?= e($title) ?></span>

                            <div class="admin-name">
                                <i class="fa-regular fa-circle-user"></i>
                                <span >
                                    <?= e((string) ($_SESSION['admin_name'] ?? 'Admin')) ?>
                                </span>
                            </div>

                        </header>

                        <main class="admin-main container-fluid">
                        <?php
                    }

                    function admin_end(): void
                    {
                        ?>
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
        <script src="<?= e(url('assets/js/admin.js')) ?>"></script>
    </body>

    </html>
<?php
                    }
