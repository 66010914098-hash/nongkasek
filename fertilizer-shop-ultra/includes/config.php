<?php
declare(strict_types=1);

// =====================
// APP SETTINGS
// =====================
define('APP_NAME', '4สหายขายปุ่ย');

// =====================
// AUTO BASE_URL (สำคัญมาก)
// ทำให้ลิงก์ /assets, /uploads, /admin ไม่พัง แม้จะวางในโฟลเดอร์ย่อย
// =====================
$script = $_SERVER['SCRIPT_NAME'] ?? '';                 // เช่น /fertilizer_shop_ultra/fertilizer-shop-ultra/admin/login.php
$dir = str_replace('\\', '/', dirname($script));         // เช่น /fertilizer_shop_ultra/fertilizer-shop-ultra/admin
$dir = rtrim($dir, '/');

// ถ้าอยู่ใน /admin ให้ถอยออก 1 ชั้นเป็น root โปรเจกต์
if (substr($dir, -6) === '/admin') {
  $dir = rtrim(dirname($dir), '/');                      // เช่น /fertilizer_shop_ultra/fertilizer-shop-ultra
}

// กรณี root เป็น "/" หรือ "."
if ($dir === '/' || $dir === '.' ) $dir = '';

define('BASE_URL', $dir);

// =====================
// DB SETTINGS (แก้ตามเครื่อง/เซิร์ฟเวอร์)
// =====================
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fertilizer_shop');
define('DB_USER', 'root');
define('DB_PASS', '');

// =====================
// SESSION + TIMEZONE
// =====================
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Bangkok');

// =====================
// UPLOADS
// =====================
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('SLIP_DIR', UPLOAD_DIR . '/slips');
define('PRODUCT_IMG_DIR', UPLOAD_DIR . '/products');
