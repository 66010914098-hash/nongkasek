<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_URL', '/fertilizer-shop-ultra/kaipui/fertilizer-shop-ultra'); // ✅ ปรับให้ตรง root เว็บคุณ

require_once __DIR__ . '/../includes/functions.php';

try {
  $pdo = db();
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>DB connect error</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
  exit;
}

$user_id  = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$user_id) {
  header("Location: " . BASE_URL . "/login.php");
  exit;
}
if ($order_id <= 0) {
  http_response_code(400);
  echo "Missing order_id";
  exit;
}

if (!isset($_FILES['slip'])) {
  http_response_code(400);
  echo "No file";
  exit;
}

$err = (int)($_FILES['slip']['error'] ?? UPLOAD_ERR_NO_FILE);
if ($err !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo "Upload error code=" . $err;
  exit;
}

$tmp  = $_FILES['slip']['tmp_name'];
$size = (int)($_FILES['slip']['size'] ?? 0);

// จำกัด 5MB
if ($size > 5 * 1024 * 1024) {
  http_response_code(400);
  echo "File too large (max 5MB)";
  exit;
}

// ตรวจ MIME จริง
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $tmp);
finfo_close($finfo);

$allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
if (!isset($allowed[$mime])) {
  http_response_code(400);
  echo "Only JPG/PNG/WEBP allowed";
  exit;
}

// สร้างโฟลเดอร์อัปโหลด
$dir = __DIR__ . '/../uploads/slips';
if (!is_dir($dir)) @mkdir($dir, 0775, true);
if (!is_dir($dir) || !is_writable($dir)) {
  http_response_code(500);
  echo "Upload dir not writable: uploads/slips";
  exit;
}

$ext  = $allowed[$mime];
$name = 'slip_o'.$order_id.'_u'.$user_id.'_'.date('Ymd_His').'.'.$ext;

$fs  = $dir . '/' . $name;
$web = 'uploads/slips/' . $name;

if (!move_uploaded_file($tmp, $fs)) {
  http_response_code(500);
  echo "move_uploaded_file failed";
  exit;
}

// อัปเดตฐานข้อมูล (ต้องมี slip_path และ payment_status ใน orders)
try {
  $pdo->prepare("UPDATE orders SET slip_path=?, payment_status='pending' WHERE id=? AND user_id=?")
      ->execute([$web, $order_id, $user_id]);
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>SQL error (update slip)</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
  exit;
}

$_SESSION['flash'] = 'อัปโหลดสลิปสำเร็จ รอตรวจสอบ';

// กลับไปหน้า pay
header("Location: " . BASE_URL . "/pay/?order_id=" . $order_id);
exit;
