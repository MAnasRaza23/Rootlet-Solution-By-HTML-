<?php
declare(strict_types=1);
const DB_HOST = '127.0.0.1';
const DB_NAME = 'rootlet_blog';
const DB_USER = 'root';
const DB_PASS = '';
// Leave empty to detect the URL path from the current script, including nested folders.
// Set to '/your-folder' only if your web server rewrites script URLs.
const BASE_URL = '';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite'=>'Lax', 'path'=>'/']);
    session_start();
}
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
} catch (PDOException $e) { http_response_code(503); if (defined('BLOG_API_REQUEST')) { echo json_encode(['error' => 'Database connection failed. Check includes/config.php and start MySQL.']); exit; } exit('Database connection failed. Check includes/config.php and start MySQL.'); }
function url(string $path=''): string {
    $base = BASE_URL;
    if ($base === '') {
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/blog-system/index.php'));
        $base = rtrim(dirname($script), '/');
        if (in_array(basename($base), ['admin', 'api'], true)) $base = rtrim(dirname($base), '/');
    }
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}
function e(?string $text): string { return htmlspecialchars($text ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function check_csrf(): void { if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) { http_response_code(419); exit('Invalid form token. Refresh and try again.'); } }
function redirect(string $path): never { header('Location: '.url($path)); exit; }
function excerpt(string $html, int $length=160): string { $plain=trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? ''); return function_exists('mb_strimwidth') ? mb_strimwidth($plain, 0, $length, '…', 'UTF-8') : (strlen($plain)>$length ? substr($plain,0,$length).'…' : $plain); }
function safe_content(string $html): string {
    $doc = new DOMDocument();
    $old = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="content-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors(); libxml_use_internal_errors($old);
    $root=$doc->getElementById('content-root') ?: $doc->getElementsByTagName('div')->item(0);
    if (!$root) return '';
    $allowed=['p','br','h2','h3','h4','strong','b','em','i','ul','ol','li','a','blockquote'];
    $clean = function(DOMNode $node) use (&$clean,$doc,$allowed): string {
        if ($node instanceof DOMText) return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (!($node instanceof DOMElement)) return '';
        $tag=strtolower($node->tagName);
        if (in_array($tag,['script','style','iframe','svg','object','form','img'],true)) return '';
        $inner=''; foreach ($node->childNodes as $child) $inner.=$clean($child);
        if (!in_array($tag,$allowed,true)) return $inner;
        if ($tag==='br') return '<br>';
        $attr=''; if ($tag==='a') { $href=trim($node->getAttribute('href')); if (preg_match('~^(https?://|mailto:|/)~i',$href) && !str_starts_with($href,'//')) $attr=' href="'.htmlspecialchars($href,ENT_QUOTES,'UTF-8').'" rel="noopener noreferrer"'; }
        return '<'.$tag.$attr.'>'.$inner.'</'.$tag.'>';
    };
    $out=''; foreach ($root->childNodes as $child) $out.=$clean($child); return $out;
}
function upload_image(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Choose an image to upload.');
    if ($file['size'] > 5*1024*1024 || $file['size'] < 1) throw new RuntimeException('Image must be smaller than 5 MB.');
    $types=[IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png', IMAGETYPE_WEBP=>'webp'];
    $details=@getimagesize($file['tmp_name']);
    $type=$details[2] ?? null;
    $extension=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    if (!isset($types[$type]) || !in_array($extension,['jpg','jpeg','png','webp'],true) || ($extension==='jpeg'?'jpg':$extension)!==$types[$type]) throw new RuntimeException('Only valid JPG, PNG, or WEBP images are allowed.');
    $name=bin2hex(random_bytes(16)).'.'.$types[$type];
    if (!move_uploaded_file($file['tmp_name'], dirname(__DIR__).'/uploads/'.$name)) throw new RuntimeException('Could not save image. Check uploads folder permissions.');
    return $name;
}
function remove_image(string $name): void { if (preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/',$name)) { $path=dirname(__DIR__).'/uploads/'.$name; if (is_file($path)) unlink($path); } }
