<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pdo = db();

$user_id = current_user_id();
$order_id = (int)($_GET['order_id'] ?? 0);

// ถ้าไม่ส่ง order_id มา → เลือกออเดอร์ค้างชำระล่าสุด
if ($order_id <= 0) {
  $st = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE user_id=? AND payment_status IN ('pending','unpaid')
    ORDER BY created_at DESC
    LIMIT 1
  ");
  $st->execute([$user_id]);
  $last = $st->fetch();
  if ($last) {
    redirect('/agriculture/pay.php?order_id=' . (int)$last['id']);
  }
  set_flash('err', 'ไม่พบรายการที่ต้องชำระเงิน');
  redirect('/agriculture/orders.php');
}

// ดึงออเดอร์ + กันคนอื่นแอบดู
$st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
$st->execute([$order_id, $user_id]);
$order = $st->fetch();
if (!$order) {
  http_response_code(404);
  echo "Order not found";
  exit;
}

// ดึงรายการสินค้าในออเดอร์ (ถ้ามีตาราง order_items)
$items = [];
try {
  $st = $pdo->prepare("
    SELECT oi.*, p.name AS product_name
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id=?
    ORDER BY oi.id ASC
  ");
  $st->execute([$order_id]);
  $items = $st->fetchAll();
} catch (Throwable $e) {
  // ถ้าโปรเจกต์ไม่มี order_items ก็ไม่ให้พัง
  $items = [];
}

// อัปโหลดสลิป
if (isset($_POST['upload_slip'])) {

  if (!isset($_FILES['slip']) || !is_array($_FILES['slip'])) {
    set_flash('err', 'กรุณาเลือกไฟล์สลิป');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
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
    $msg = isset($map[$err]) ? $map[$err] : ('อัปโหลดผิดพลาด code=' . $err);
    set_flash('err', $msg);
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  $tmp  = $_FILES['slip']['tmp_name'];
  $size = (int)($_FILES['slip']['size'] ?? 0);

  if ($size <= 0) {
    set_flash('err', 'ไฟล์ไม่ถูกต้อง');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  if ($size > 5 * 1024 * 1024) {
    set_flash('err', 'ไฟล์ใหญ่เกิน 5MB');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  // ตรวจ mime จริง
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
  ];
  if (!isset($allowed[$mime])) {
    set_flash('err', 'อนุญาตเฉพาะ JPG/PNG/WEBP');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  // โฟลเดอร์ปลายทาง
  $dir = __DIR__ . '/uploads/slips';
  if (!is_dir($dir)) {
    @mkdir($dir, 0775, true);
  }
  if (!is_dir($dir) || !is_writable($dir)) {
    set_flash('err', 'โฟลเดอร์อัปโหลดใช้งานไม่ได้ (permission)');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  $ext  = $allowed[$mime];
  $name = 'slip_o' . $order_id . '_u' . $user_id . '_' . date('Ymd_His') . '.' . $ext;

  $path_fs  = $dir . '/' . $name;
  $path_web = 'uploads/slips/' . $name; // relative

  if (!move_uploaded_file($tmp, $path_fs)) {
    set_flash('err', 'อัปโหลดไม่สำเร็จ');
    redirect('/agriculture/pay.php?order_id=' . $order_id);
  }

  // อัปเดตฐานข้อมูล (คอลัมน์เหล่านี้ต้องมีในตาราง orders)
  $pdo->prepare("
    UPDATE orders
    SET slip_path = ?, slip_uploaded_at = NOW(), payment_status = 'pending'
    WHERE id = ? AND user_id = ?
  ")->execute([$path_web, $order_id, $user_id]);

  set_flash('ok', 'อัปโหลดสลิปแล้ว รอแอดมินตรวจสอบ');
  redirect('/agriculture/pay.php?order_id=' . $order_id);
}

$flash = get_flash(); // ถ้าใน functions.php มี flash helper

?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ชำระเงิน #<?php echo (int)$order_id; ?></title>
  <style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f7f7f7}
    .wrap{max-width:920px;margin:0 auto}
    .card{background:#fff;border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:14px}
    .msg{padding:10px;border-radius:10px;margin-bottom:10px}
    .ok{background:#e7f8ed;border:1px solid #b7ebc6}
    .err{background:#ffecec;border:1px solid #ffb7b7}
    table{width:100%;border-collapse:collapse}
    th,td{border-bottom:1px solid #eee;padding:10px;text-align:left}
    img{max-width:100%;border-radius:12px;border:1px solid #eee}
    button{padding:10px 14px;border:0;border-radius:10px;cursor:pointer}
  </style>
</head>
<body>
<div class="wrap">

  <div class="card">
    <h2>ชำระเงินคำสั่งซื้อ #<?php echo (int)$order_id; ?></h2>

    <?php if (!empty($flash)): ?>
      <div class="msg <?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <p><b>ยอดรวม:</b> <?php echo htmlspecialchars((string)($order['total_amount'] ?? $order['total'] ?? '0')); ?></p>
    <p><b>สถานะชำระเงิน:</b> <?php echo htmlspecialchars((string)($order['payment_status'] ?? $order['status'] ?? '-')); ?></p>

    <?php if (!empty($order['slip_path'])): ?>
      <p><b>สลิปที่อัปโหลด:</b></p>
      <img src="<?php echo htmlspecialchars($order['slip_path']); ?>" alt="slip">
    <?php else: ?>
      <p><i>ยังไม่มีสลิป</i></p>
    <?php endif; ?>
  </div>

  <?php if (!empty($items)): ?>
    <div class="card">
      <h3>รายการสินค้า</h3>
      <table>
        <thead>
          <tr>
            <th>สินค้า</th>
            <th>จำนวน</th>
            <th>ราคา</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?php echo htmlspecialchars((string)($it['product_name'] ?? $it['name'] ?? '-')); ?></td>
              <td><?php echo htmlspecialchars((string)($it['qty'] ?? $it['quantity'] ?? '1')); ?></td>
              <td><?php echo htmlspecialchars((string)($it['price'] ?? '0')); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="card">
    <h3>อัปโหลดสลิป</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="slip" accept="image/*" required>
      <br><br>
      <button type="submit" name="upload_slip">อัปโหลดสลิป</button>
    </form>
  </div>

</div>
</body>
</html>
