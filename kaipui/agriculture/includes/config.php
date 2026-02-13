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
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = str_replace('\\', '/', dirname($script));
$dir = rtrim($dir, '/');

// ถ้าอยู่ใน /admin ให้ถอยออก 1 ชั้นเป็น root โปรเจกต์
if (substr($dir, -6) === '/admin') {
  $dir = rtrim(dirname($dir), '/');
}

if ($dir === '/' || $dir === '.') $dir = '';
define('BASE_URL', $dir);

// =====================
// DB SETTINGS (แก้ตามเครื่อง/เซิร์ฟเวอร์)
// =====================
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fertilizer_shop');
define('DB_USER', 'root');
define('DB_PASS', 'Acc123456#');

// =====================
// SESSION + TIMEZONE
// =====================
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('Asia/Bangkok');

// =====================
// UPLOADS (ไฟล์จริง)
// =====================
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('SLIP_DIR', UPLOAD_DIR . '/slips');
define('PRODUCT_IMG_DIR', UPLOAD_DIR . '/products');

// =====================
// UPLOADS (พาธเว็บ)  ✅ สำคัญ: เก็บลง DB ต้องเป็นพาธเว็บ
// =====================
define('SLIP_URL', '/uploads/slips');
define('PRODUCT_IMG_URL', '/uploads/products');
