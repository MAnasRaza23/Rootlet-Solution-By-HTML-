<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function contact_response(int $status, array $data): void {
    http_response_code($status); echo json_encode($data); exit;
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}
$_SESSION['contact_csrf'] ??= bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'GET') contact_response(200, ['token' => $_SESSION['contact_csrf']]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: GET, POST'); contact_response(405, ['error' => 'Method not allowed.']); }
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) contact_response(413, ['error' => 'Message is too large.']);
if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['contact_csrf'], $_POST['csrf'])) contact_response(419, ['error' => 'Refresh the page and try again.']);
if (!empty($_POST['website'])) contact_response(422, ['error' => 'Unable to send this enquiry.']);
$fields = [];
foreach (['name' => 120, 'phone' => 30, 'email' => 254, 'subject' => 180, 'message' => 10000] as $key => $max) {
    if (!is_string($_POST[$key] ?? null)) contact_response(422, ['error' => 'Complete all fields.']);
    $value = trim($_POST[$key]);
    $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    if ($value === '' || $length > $max || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) contact_response(422, ['error' => 'Enter a valid ' . $key . ' within the allowed length.']);
    if ($key !== 'message' && preg_match('/[\r\n]/', $value)) contact_response(422, ['error' => 'Invalid field value.']);
    $fields[$key] = $value;
}
if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) contact_response(422, ['error' => 'Enter a valid email address.']);
if (!preg_match('/^[+0-9(). \-]{7,30}$/', $fields['phone']) || strlen(preg_replace('/[^0-9]/', '', $fields['phone'])) < 7) contact_response(422, ['error' => 'Enter a valid phone number.']);
if (time() - (int) ($_SESSION['contact_attempt'] ?? 0) < 30) contact_response(429, ['error' => 'Please wait 30 seconds before sending again.']);
$_SESSION['contact_attempt'] = time();
require_once __DIR__ . '/../includes/contact-smtp.php';
try {
    send_contact_email(require __DIR__ . '/../includes/contact-mail.php', $fields);
    contact_response(200, ['success' => true, 'message' => 'Thank you! Your message has been submitted to our team.']);
} catch (Throwable $error) {
    error_log('Rootlet contact: ' . $error->getMessage());
    contact_response(503, ['error' => 'Unable to send right now. Please email info@rootletsolutions.com or hr@rootletsolutions.com, or try again later.']);
}
