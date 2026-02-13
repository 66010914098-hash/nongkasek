<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');

/* ================= DB ================= */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fertilizer_shop');
define('DB_USER', 'root');
define('DB_PASS', 'Acc123456#');

/* ================= PATH ================= */
define('BASE_URL', '/nongkasek/kaipui/agriculture');

define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('PRODUCT_IMG_DIR', UPLOAD_DIR . '/products');

define('PRODUCT_IMG_URL', BASE_URL . '/uploads/products');
