<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/functions.php';
require_login();
$pdo = db(); // ต้องมีฟังก์ชัน db() ในโปรเจกต์คุณ

$user_id = current_user_id();
$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
  set_flash('err', 'ไม่พบเลขออเดอร์');
  redirect('/nongkasek/kaipui/agriculture/orders.php');
}

// โหลดออเดอร์ของ user นี้เท่านั้น
$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
$st->execute([$order_id, $user_id]);
$order = $st->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  http_response_code(404);
  echo "Order not found";
  exit;
}

// ===== upload slip =====
if (isset($_POST['upload_slip'])) {

  if (!isset($_FILES['slip']) || !is_array($_FILES['slip'])) {
    set_flash('err', 'กรุณาเลือกไฟล์สลิป');
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  $err = (int)($_FILES['slip']['error'] ?? UPLOAD_ERR_NO_FILE);
  if ($err !== UPLOAD_ERR_OK) {
    $map = [
      UPLOAD_ERR_INI_SIZE   => 'ไฟล์ใหญ่เกินกำหนด (php.ini)',
      UPLOAD_ERR_FORM_SIZE  => 'ไฟล์ใหญ่เกินกำหนด (ฟอร์ม)',
      UPLOAD_ERR_PARTIAL    => 'อัปโหลดไม่ครบ',
      UPLOAD_ERR_NO_FILE    => 'กรุณาเลือกไฟล์',
      UPLOAD_ERR_NO_TMP_DIR => 'ไม่มีโฟลเดอร์ temp',
      UPLOAD_ERR_CANT_WRITE => 'เขียนไฟล์ไม่ได้ (permission)',
      UPLOAD_ERR_EXTENSION  => 'ถูก extension บล็อก',
    ];
    set_flash('err', $map[$err] ?? ('อัปโหลดผิดพลาด code=' . $err));
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  $tmp  = $_FILES['slip']['tmp_name'];
  $size = (int)($_FILES['slip']['size'] ?? 0);

  if ($size > 5 * 1024 * 1024) {
    set_flash('err', 'ไฟล์ใหญ่เกิน 5MB');
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  if (!isset($allowed[$mime])) {
    set_flash('err', 'อนุญาตเฉพาะ JPG/PNG/WEBP');
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  $dir = __DIR__ . '/uploads/slips';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  if (!is_dir($dir) || !is_writable($dir)) {
    set_flash('err', 'โฟลเดอร์ uploads/slips ใช้งานไม่ได้ (permission)');
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  $ext = $allowed[$mime];
  $name = 'slip_o'.$order_id.'_u'.$user_id.'_'.date('Ymd_His').'.'.$ext;
  $fs = $dir . '/' . $name;
  $web = 'uploads/slips/' . $name;

  if (!move_uploaded_file($tmp, $fs)) {
    set_flash('err', 'ย้ายไฟล์ไม่สำเร็จ');
    redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
  }

  // ถ้าตาราง orders ไม่มีคอลัมน์เหล่านี้ ให้แก้ตาม DB คุณ
  $pdo->prepare("
    UPDATE orders
    SET slip_path=?, slip_uploaded_at=NOW(), payment_status='pending'
    WHERE id=? AND user_id=?
  ")->execute([$web, $order_id, $user_id]);

  set_flash('ok', 'อัปโหลดสลิปแล้ว รอแอดมินตรวจสอบ');
  redirect('/nongkasek/kaipui/agriculture/pay.php?order_id=' . $order_id);
}

$flash = get_flash();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ชำระเงิน #<?php echo (int)$order_id; ?></title>
</head>
<body style="font-family:Arial,sans-serif;margin:20px">
  <h2>ชำระเงินคำสั่งซื้อ #<?php echo (int)$order_id; ?></h2>

  <?php if (!empty($flash)): ?>
    <div style="padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0">
      <?php echo htmlspecialchars($flash['msg']); ?>
    </div>
  <?php endif; ?>

  <p><b>ยอดรวม:</b> <?php echo htmlspecialchars((string)($order['total_amount'] ?? $order['total'] ?? '0')); ?></p>
  <p><b>สถานะ:</b> <?php echo htmlspecialchars((string)($order['payment_status'] ?? $order['status'] ?? '-')); ?></p>

  <?php if (!empty($order['slip_path'])): ?>
    <p><b>สลิป:</b></p>
    <img src="<?php echo htmlspecialchars($order['slip_path']); ?>" style="max-width:420px;border:1px solid #eee;border-radius:10px">
  <?php endif; ?>

  <hr>
  <h3>อัปโหลดสลิป</h3>
  <form method="post" enctype="multipart/form-data">
    <input type="file" name="slip" accept="image/*" required>
    <button type="submit" name="upload_slip">อัปโหลด</button>
  </form>
</body>
</html>
