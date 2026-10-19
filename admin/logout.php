<?php
require_once __DIR__.'/auth.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
check_csrf(); $_SESSION=[]; session_regenerate_id(true); redirect('admin/login.php');
