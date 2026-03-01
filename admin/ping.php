<?php
define('ADMIN_SECURE', true);

require_once '../config.php';
require_once '../includes/security.php';

initSecureSession();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    exit;
}

http_response_code(204);

