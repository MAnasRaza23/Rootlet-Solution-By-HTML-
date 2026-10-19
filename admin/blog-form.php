<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/partials.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$editing = basename($_SERVER['SCRIPT_NAME']) === 'edit-blog.php';

$post = [
    'title' => '',
    'posted_by' => 'Admin',
    'post_date' => date('Y-m-d'),
    'content' => '',
    'image' => '',
];

if ($editing) {
    if (!$id) {
        redirect('admin/blogs.php');
    }

    $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id=?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(404);
        exit('Blog not found.');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    foreach (['title', 'posted_by', 'post_date', 'content'] as $key) {
        $post[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    $post['content'] = safe_content($post['content']);

    if ($post['title'] === '' || (function_exists('mb_strlen') ? mb_strlen($post['title']) : strlen($post['title'])) > 255) {
        $errors[] = 'Enter a title (up to 255 characters).';
    }

    if ($post['posted_by'] === '' || (function_exists('mb_strlen') ? mb_strlen($post['posted_by']) : strlen($post['posted_by'])) > 120) {
        $errors[] = 'Enter an author name (up to 120 characters).';
    }

    $date = DateTime::createFromFormat('!Y-m-d', $post['post_date']);
    if (!$date || $date->format('Y-m-d') !== $post['post_date']) {
        $errors[] = 'Select a valid post date.';
    }

    if (trim(strip_tags($post['content'])) === '') {
        $errors[] = 'Write some blog content.';
    }

    $hasImage = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if (!$editing && !$hasImage) {
        $errors[] = 'Choose a featured image.';
    }

    if (!$errors) {
        $newImage = null;

        try {
            if ($hasImage) {
                $newImage = upload_image($_FILES['image']);
            }

            if ($editing) {
                $stmt = $pdo->prepare('UPDATE blogs SET title=?, image=?, posted_by=?, post_date=?, content=? WHERE id=?');
                $stmt->execute([
                    $post['title'],
                    $newImage ?? $post['image'],
                    $post['posted_by'],
                    $post['post_date'],
                    $post['content'],
                    $id,
                ]);

                if ($newImage) {
                    remove_image($post['image']);
                }
            } else {
                $stmt = $pdo->prepare('INSERT INTO blogs(title, image, posted_by, post_date, content) VALUES(?,?,?,?,?)');
                $stmt->execute([
                    $post['title'],
                    $newImage,
                    $post['posted_by'],
                    $post['post_date'],
                    $post['content'],
                ]);
            }

            redirect('admin/blogs.php?status=' . ($editing ? 'updated' : 'created'));
        } catch (Throwable $e) {
            if ($newImage) {
                remove_image($newImage);
            }

            $errors[] = $e instanceof RuntimeException ? $e->getMessage() : 'Could not save the blog. Please try again.';
        }
    }
}

admin_start($editing ? 'Edit Blog' : 'Add Blog', $editing ? 'edit' : 'add');
?>
<div class="page-heading">
    <div>
        <h1><?= $editing ? 'Edit Blog' : 'Add Blog' ?></h1>
        <p>Write and format your post below.</p>
    </div>
    <a class="text-link" href="<?= e(url('admin/blogs.php')) ?>">← All Blogs</a>
</div>

<?php if ($errors): ?>
    <div class="alert" role="alert">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="panel blog-form" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <label>
        Blog title
        <input name="title" value="<?= e($post['title']) ?>" maxlength="255" required>
    </label>

    <div class="form-grid">
        <label>
            Posted by
            <input name="posted_by" value="<?= e($post['posted_by']) ?>" maxlength="120" required>
        </label>

        <label>
            Post date
            <input type="date" name="post_date" value="<?= e($post['post_date']) ?>" required>
        </label>
    </div>

    <label>
        Featured image <?= $editing ? '(optional to replace)' : '' ?>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>>
    </label>

    <?php if ($editing): ?>
        <img class="image-preview" src="<?= e(url('uploads/' . $post['image'])) ?>" alt="Current featured image">
    <?php endif; ?>

    <div class="editor-label">Main content</div>

    <div class="toolbar" role="toolbar" aria-label="Formatting">
        <button type="button" data-command="formatBlock" data-value="p">Paragraph</button>
        <button type="button" data-command="formatBlock" data-value="h2">Heading</button>
        <button type="button" data-command="formatBlock" data-value="h3">Subheading</button>
        <button type="button" data-command="bold"><b>B</b></button>
        <button type="button" data-command="italic"><i>I</i></button>
        <button type="button" data-command="insertUnorderedList">• List</button>
        <button type="button" data-command="insertOrderedList">1. List</button>
        <button type="button" data-command="createLink">Link</button>
    </div>

    <div
        id="editor"
        class="editor"
        contenteditable="true"
        role="textbox"
        aria-label="Blog content"
        aria-multiline="true"
        data-placeholder="Start writing your blog…"
    ><?= $post['content'] ?></div>

    <textarea id="content-field" name="content" hidden></textarea>

    <p class="hint">Use headings, paragraphs, lists, and links. Images go in the featured image field.</p>

    <button class="button" type="submit"><?= $editing ? 'Update Blog' : 'Publish Blog' ?></button>
</form>

<?php admin_end(); ?>
