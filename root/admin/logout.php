<?php
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
header("Location: " . (BASE_URL ?: '') . "/admin/login.php");
exit;
