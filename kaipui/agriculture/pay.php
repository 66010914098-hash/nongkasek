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
  $order = $st->fetch();
  if ($order) $order_id = (int)$order['id'];
}

// โหลดออเดอร์ที่กำลังจะจ่าย
if ($order_id > 0) {
  $st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
  $st->execute([$order_id, $user_id]);
  $order = $st->fetch();
} else {
  $order = null;
}

// โหลดออเดอร์ค้างชำระทั้งหมด
$st = $pdo->prepare("
  SELECT id,total,payment_status,shipping_status,created_at,slip_path
  FROM orders
  WHERE user_id=? AND payment_status IN ('pending','unpaid')
  ORDER BY created_at DESC
");
$st->execute([$user_id]);
$pending_orders = $st->fetchAll();

// โหลดรายการสินค้า
$order_items = [];
if ($order) {
  try {
    $sti = $pdo->prepare("SELECT * FROM order_items WHERE order_id=? ORDER BY id ASC");
    $sti->execute([$order_id]);
    $order_items = $sti->fetchAll();
  } catch (Exception $e) {
    $order_items = [];
  }
}

// อัปโหลดสลิป
if ($order && isset($_POST['upload_slip'])) {

  if (!isset($_FILES['slip']) || ($_FILES['slip']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    set_flash(type: 'err', msg: 'กรุณาเลือกไฟล์สลิป');
    redirect(path: '/pay.php?order_id=' . $order_id);
  }

  $tmp  = $_FILES['slip']['tmp_name'];
  $size = (int)($_FILES['slip']['size'] ?? 0);
  if ($size > 5 * 1024 * 1024) {
    set_flash(type: 'err', msg: 'ไฟล์ใหญ่เกิน 5MB');
    redirect(path: '/pay.php?order_id=' . $order_id);
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  if (!isset($allowed[$mime])) {
    set_flash(type: 'err', msg: 'อนุญาตเฉพาะ JPG/PNG/WEBP');
    redirect(path: '/pay.php?order_id=' . $order_id);
  }

  $dir = __DIR__ . '/uploads/slips';
  if (!is_dir($dir)) mkdir($dir, 0775, true);

  $ext  = $allowed[$mime];
  $name = 'slip_o'.$order_id.'_u'.$user_id.'_'.date('Ymd_His').'.'.$ext;
  $path_fs  = $dir . '/' . $name;
  $path_web = '/uploads/slips/' . $name;

  if (!move_uploaded_file($tmp, $path_fs)) {
    set_flash(type: 'err', msg: 'อัปโหลดไม่สำเร็จ');
    redirect(path: '/pay.php?order_id=' . $order_id);
  }

  // อัปเดต DB
  try {
    $pdo->prepare("
      UPDATE orders
      SET slip_path=?, slip_uploaded_at=NOW(), payment_status='pending'
      WHERE id=? AND user_id=?
    ")->execute([$path_web, $order_id, $user_id]);
  } catch (Exception $e) {
    $pdo->prepare("
      UPDATE orders
      SET slip_path=?, payment_status='pending'
      WHERE id=? AND user_id=?
    ")->execute([$path_web, $order_id, $user_id]);
  }

  set_flash(type: 'ok', msg: 'อัปโหลดสลิปแล้ว รอแอดมินตรวจสอบ');
  redirect(path: '/pay.php?order_id=' . $order_id);
}

require __DIR__ . '/includes/header.php';
?>

<div class="card panel">
  <div class="section-title">
    <div>
      <div class="h2">ชำระเงิน / ยอดค้างชำระ</div>
      <div class="small">PromptPay + อัปโหลดสลิป</div>
    </div>
    <a class="btn" href="<?= h(url('/orders.php')) ?>">ไปหน้าออเดอร์</a>
  </div>

  <?php if(!$order): ?>
    <div class="card" style="padding:18px; margin-top:14px">
      <div class="h3">ยังไม่มีรายการค้างชำระ</div>
    </div>
  <?php else: ?>

    <div class="grid2" style="margin-top:14px; gap:18px">
      <!-- ซ้าย -->
      <div class="card" style="padding:18px">
        <div class="h3">ออเดอร์ #<?= (int)$order['id'] ?></div>
        <div class="h2" style="margin-top:8px">
          ฿<?= number_format((float)$order['total'],2) ?>
        </div>

        <div style="margin-top:14px; display:flex; justify-content:center">
          <img src="<?= h(url('/assets/img/qr_promptpay.jpg')) ?>"
               style="width:min(360px,100%); border-radius:16px">
        </div>

        <div class="small" style="margin-top:12px; white-space:pre-wrap">
<b>ที่อยู่จัดส่ง</b>
<?= h($order['shipping_address'] ?? '-') ?>
        </div>
      </div>

      <!-- ขวา -->
      <div class="card" style="padding:18px">
        <div class="h3">อัปโหลดสลิป</div>

        <form method="post" enctype="multipart/form-data" style="margin-top:14px">
          <input class="input" type="file" name="slip" required>
          <div style="margin-top:12px">
            <button class="btn primary" name="upload_slip">อัปโหลด</button>
          </div>
        </form>

        <?php if(!empty($order['slip_path'])): ?>
          <div style="margin-top:14px">
            <a class="btn sky" href="<?= h(url($order['slip_path'])) ?>" target="_blank">ดูสลิป</a>
          </div>
        <?php endif; ?>

        <hr style="margin:18px 0">

        <div class="h3">รายการสินค้า</div>
        <?php if(!$order_items): ?>
          <div class="small">ไม่มีข้อมูลสินค้า</div>
        <?php else: ?>
          <table class="table" style="margin-top:10px">
            <thead>
              <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>รวม</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($order_items as $it): ?>
                <tr>
                  <td><?= h($it['product_name'] ?? '-') ?></td>
                  <td><?= (int)($it['qty'] ?? 0) ?></td>
                  <td>฿<?= number_format((float)($it['subtotal'] ?? 0),2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
