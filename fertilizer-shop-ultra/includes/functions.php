<?php
require_once __DIR__ . '/db.php';

/* ---------------------------
  Helpers
--------------------------- */

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '/'): string {
  // ถ้า path เป็นลิงก์เต็ม (http/https) คืนกลับเลย
  if (preg_match('~^https?://~i', $path)) return $path;

  if ($path === '') $path = '/';
  if ($path[0] !== '/') $path = '/' . $path;

  return rtrim(BASE_URL, '/') . $path;
}

function redirect(string $path): void {
  header('Location: ' . url($path));
  exit;
}

/* ---------------------------
  Flash Message
--------------------------- */

function set_flash(string $type, string $msg): void {
  $_SESSION['_flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash(): ?array {
  if (!isset($_SESSION['_flash'])) return null;
  $f = $_SESSION['_flash'];
  unset($_SESSION['_flash']);
  return $f;
}

/* ---------------------------
  Auth
--------------------------- */

function is_logged_in(): bool {
  return !empty($_SESSION['user_id']);
}

function current_user_id(): int {
  return (int)($_SESSION['user_id'] ?? 0);
}

function require_login(): void {
  if (!is_logged_in()) {
    set_flash('warn', 'กรุณาเข้าสู่ระบบก่อน');
    redirect('/login.php');
  }
}

/* ---------------------------
  Cart
--------------------------- */

function cart_init(): void {
  if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }
}

function cart_count(): int {
  $cart = $_SESSION['cart'] ?? [];
  $sum = 0;
  foreach ($cart as $qty) $sum += (int)$qty;
  return $sum;
}

/* ---------------------------
  Product helper
--------------------------- */

function product_cover(PDO $pdo, int $product_id): ?string {
  // ถ้าคุณเก็บรูปใน products.image ให้ใช้แบบนี้ (ถ้าไม่มีคอลัมน์นี้ ให้ return null)
  try {
    $st = $pdo->prepare("SELECT image FROM products WHERE id=? LIMIT 1");
    $st->execute([$product_id]);
    $row = $st->fetch();
    if (!$row) return null;
    $img = trim((string)($row['image'] ?? ''));
    if ($img === '') return null;

    // ถ้าเก็บเป็น path เช่น /uploads/products/xxx.jpg ก็คืนกลับได้เลย
    return $img;
  } catch (Exception $e) {
    return null;
  }
}

/* ---------------------------
  Upload Slip (optional helper)
--------------------------- */

function safe_upload(array $file, array $allow_ext, string $dir_fs, string $name_prefix): string {
  if (!isset($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ');
  }

  $tmp = $file['tmp_name'];
  $size = (int)($file['size'] ?? 0);
  if ($size > 5 * 1024 * 1024) {
    throw new Exception('ไฟล์ใหญ่เกิน 5MB');
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $map = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf'
  ];

  if (!isset($map[$mime])) throw new Exception('ชนิดไฟล์ไม่รองรับ');

  $ext = $map[$mime];
  if (!in_array($ext, $allow_ext, true)) throw new Exception('นามสกุลไม่อนุญาต');

  if (!is_dir($dir_fs)) mkdir($dir_fs, 0775, true);

  $name = $name_prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $path_fs = rtrim($dir_fs, '/\\') . DIRECTORY_SEPARATOR . $name;

  if (!move_uploaded_file($tmp, $path_fs)) {
    throw new Exception('ย้ายไฟล์ไม่สำเร็จ');
  }

  // คืนค่าเป็น path web ให้คุณกำหนดเองภายนอก (เช่น /uploads/slips/xxx.jpg)
  return $name;
}
function is_admin(): bool {
  return !empty($_SESSION['is_admin']);
}

function require_admin(): void {
  if (!is_admin()) {
    set_flash('err', 'ต้องเป็นแอดมินเท่านั้น');
    redirect('/admin/login.php');
  }
}
