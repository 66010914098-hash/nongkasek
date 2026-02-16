<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pdo = db();

// ✅ กัน cart ว่าง
cart_init();
$cart = $_SESSION['cart'] ?? [];
$ids = array_keys($cart);

if (!$ids) {
  set_flash('warn', 'ตะกร้าว่าง');
  redirect('/cart.php');
}

// ✅ ดึงสินค้าในตะกร้า
$in = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

if (!$products) {
  set_flash('err', 'ไม่พบสินค้าในตะกร้า');
  redirect('/cart.php');
}

// ✅ รวมยอด
$total = 0.0;
foreach ($products as $p) {
  $pid = (int)$p['id'];
  $qty = (int)($cart[$pid] ?? 0);
  if ($qty <= 0) continue;
  $total += $qty * (float)$p['price'];
}

// ✅ บันทึกออเดอร์
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $address = trim((string)($_POST['address'] ?? ''));

  if ($address === '') {
    $err = 'กรุณากรอกที่อยู่จัดส่ง';
  } else {
    $pdo->beginTransaction();

    try {
      // 1) insert orders
      $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total, shipping_address, payment_status, shipping_status, created_at)
        VALUES (?, ?, ?, 'unpaid', 'processing', NOW())
      ");
      $stmt->execute([current_user_id(), $total, $address]);
      $orderId = (int)$pdo->lastInsertId();

      // 2) insert order_items
      $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
      ");

      foreach ($products as $p) {
        $pid = (int)$p['id'];
        $qty = (int)($cart[$pid] ?? 0);
        if ($qty <= 0) continue;

        $unit = (float)$p['price'];
        $sub  = $unit * $qty;

        $itemStmt->execute([$orderId, $pid, $p['name'], $unit, $qty, $sub]);
      }

      // 3) clear cart
      $_SESSION['cart'] = [];

      $pdo->commit();

      set_flash('ok', 'สร้างออเดอร์แล้ว ไปหน้าชำระเงิน');
      // ✅ ไปหน้า pay.php เพื่อสแกน QR + อัปสลิป
      redirect('/pay.php?order_id=' . $orderId);

    } catch (Exception $e) {
      $pdo->rollBack();
      $err = 'บันทึกออเดอร์ไม่สำเร็จ: ' . $e->getMessage();
    }
  }
}

require __DIR__ . '/includes/header.php';
?>

<div class="card panel">
  <div class="section-title">
    <div>
      <div class="h2">ชำระเงิน</div>
      <div class="small">กรอกที่อยู่จัดส่ง แล้วกด “ยืนยันสั่งซื้อ”</div>
    </div>
    <a class="btn" href="<?= h(url('/cart.php')) ?>">← กลับตะกร้า</a>
  </div>

  <?php if($err): ?>
    <div class="toast err"><b>ผิดพลาด</b><div><?= h($err) ?></div></div>
  <?php endif; ?>

  <div class="grid2" style="margin-top:14px; gap:18px">
    <!-- ที่อยู่ -->
    <div class="card" style="padding:18px">
      <div class="h3">ที่อยู่จัดส่ง</div>
      <form method="post" style="margin-top:12px">
        <textarea class="input" name="address" rows="5" placeholder="ชื่อผู้รับ • ที่อยู่ • เบอร์โทร" required><?= h($_POST['address'] ?? '') ?></textarea>
        <button class="btn primary" style="margin-top:12px">ยืนยันสั่งซื้อ</button>
      </form>
    </div>

    <!-- สรุป -->
    <div class="card" style="padding:18px">
      <div class="h3">สรุปรายการ</div>

      <table class="table" style="margin-top:12px">
        <thead>
          <tr>
            <th>สินค้า</th>
            <th style="width:90px">จำนวน</th>
            <th style="width:140px">รวม</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $p):
            $pid = (int)$p['id'];
            $qty = (int)($cart[$pid] ?? 0);
            if ($qty <= 0) continue;
            $sub = $qty * (float)$p['price'];
          ?>
            <tr>
              <td><?= h($p['name']) ?></td>
              <td><?= $qty ?></td>
              <td>฿<?= number_format($sub, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="h2" style="margin-top:14px">
        รวม: ฿<?= number_format($total, 2) ?>
      </div>

      <div class="small" style="margin-top:8px; opacity:.85">
        หลังยืนยันสั่งซื้อ ระบบจะพาไปหน้า <b>pay.php</b> เพื่อสแกน QR + อัปโหลดสลิป
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
