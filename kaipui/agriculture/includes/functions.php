<?php
require_once __DIR__ . '/db.php';

/* ---------------------------
  Helpers
--------------------------- */

function h($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '/'): string {
  if (preg_match('~^https?://~i', $path)) return $path;
  if ($path === '') $path = '/';
  if ($path[0] !== '/') $path = '/' . $path;
  return rtrim(BASE_URL, '/') . $path;
}

function redirect(string $path): void {
  header('Location: ' . url($path));
  exit;
}

function ensure_dir(string $dir_fs): void {
  if (!is_dir($dir_fs)) {
    if (!mkdir($dir_fs, 0775, true) && !is_dir($dir_fs)) {
      throw new Exception('สร้างโฟลเดอร์ไม่สำเร็จ: ' . $dir_fs);
    }
  }
  if (!is_writable($dir_fs)) {
    throw new Exception('โฟลเดอร์เขียนไม่ได้: ' . $dir_fs);
  }
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

function is_admin(): bool {
  return !empty($_SESSION['is_admin']);
}

function require_admin(): void {
  if (!is_admin()) {
    set_flash('err', 'ต้องเป็นแอดมินเท่านั้น');
    redirect('/admin/login.php');
  }
}

/* ---------------------------
  Cart
--------------------------- */

function cart_init(): void {
  if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
}

function cart_count(): int {
  $cart = $_SESSION['cart'] ?? [];
  $sum = 0;
  foreach ($cart as $qty) $sum += (int)$qty;
  return $sum;
}

/* ---------------------------
  Upload (สำคัญ)
  - ย้ายไฟล์ไป dir_fs
  - คืนค่าเป็นพาธเว็บ เช่น /uploads/products/xxx.jpg
--------------------------- */

function safe_upload(array $file, array $allow_ext, string $dir_fs, string $name_prefix, string $dir_web): string {
  $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
  if ($err !== UPLOAD_ERR_OK) return ''; // ให้ผู้เรียกเช็คเอง

  $tmp = $file['tmp_name'] ?? '';
  if ($tmp === '' || !is_uploaded_file($tmp)) return '';

  $size = (int)($file['size'] ?? 0);
  if ($size <= 0) return '';
  if ($size > 10 * 1024 * 1024) return ''; // 10MB กันพัง

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $map = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  if (!isset($map[$mime])) return '';
  $ext = $map[$mime];
  if (!in_array($ext, $allow_ext, true)) return '';

  ensure_dir($dir_fs);

  $name = $name_prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $path_fs = rtrim($dir_fs, '/\\') . DIRECTORY_SEPARATOR . $name;

  if (!move_uploaded_file($tmp, $path_fs)) return '';

  // ✅ คืนค่าเป็นพาธเว็บ (ไว้เก็บ DB)
  $dir_web = rtrim($dir_web, '/');
  return $dir_web . '/' . $name;
}
