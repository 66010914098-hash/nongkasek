<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

/* =========================
   UPDATE ORDER STATUS
========================= */
if (isset($_POST['update'])) {
  $id   = (int)($_POST['id'] ?? 0);
  $pay  = trim((string)($_POST['payment_status'] ?? 'pending'));
  $ship = trim((string)($_POST['shipping_status'] ?? 'processing'));

  $allowedPay  = ['unpaid','pending','paid','rejected','refunded'];
  $allowedShip = ['processing','packing','shipped','delivered','cancelled'];

  if ($id > 0) {
    if (!in_array($pay, $allowedPay, true))  $pay  = 'pending';
    if (!in_array($ship, $allowedShip, true)) $ship = 'processing';

    $st = $pdo->prepare("UPDATE orders SET payment_status=?, shipping_status=? WHERE id=?");
    $st->execute([$pay, $ship, $id]);

    set_flash('ok', 'อัปเดตออเดอร์แล้ว');
  } else {
    set_flash('err', 'ไม่พบไอดีออเดอร์');
  }
  redirect('/admin/orders.php');
}

/* =========================
   SEARCH
========================= */
$q = trim((string)($_GET['q'] ?? ''));

$sql = "SELECT o.*, u.full_name, u.email
        FROM orders o
        JOIN users u ON u.id = o.user_id";

$params = [];
if ($q !== '') {
  $sql .= " WHERE u.full_name LIKE ? OR u.email LIKE ? OR o.id LIKE ? OR o.shipping_address LIKE ?";
  $like = "%$q%";
  $params = [$like, $like, $like, $like];
}

$sql .= " ORDER BY o.created_at DESC LIMIT 500";

$st = $pdo->prepare($sql);
$st->execute($params);
$orders = $st->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="card panel">
  <div class="section-title">
    <div>
      <div class="h2">จัดการออเดอร์ (หลังร้าน)</div>
      <div class="small">ปรับสถานะการชำระเงิน + จัดส่ง • ค้นหาได้ • ดูสลิปได้</div>
    </div>
    <a class="btn" href="<?= h(url('/admin/index.php')) ?>">← กลับแดชบอร์ด</a>
  </div>

  <form method="get" class="row wrap" style="margin-top:14px; gap:10px">
    <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อลูกค้า / อีเมล / ไอดีออเดอร์ / ที่อยู่จัดส่ง">
    <button class="btn" type="submit">ค้นหา</button>
    <a class="btn ghost" href="<?= h(url('/admin/orders.php')) ?>">ล้าง</a>
  </form>

  <div style="margin-top:14px; overflow:auto">
    <table class="table">
      <thead>
        <tr>
          <th style="width:70px">#</th>
          <th>ลูกค้า</th>
          <th style="width:140px">ยอด</th>
          <th style="width:140px">ชำระเงิน</th>
          <th style="width:140px">จัดส่ง</th>
          <th style="width:140px">สลิป</th>
          <th style="width:220px">อัปเดต</th>
        </tr>
      </thead>
      <tbody>
      <?php if(!$orders): ?>
        <tr><td colspan="7" class="small">ยังไม่มีออเดอร์</td></tr>
      <?php endif; ?>

      <?php foreach($orders as $o): ?>
        <tr>
          <td><b>#<?= (int)$o['id'] ?></b></td>

          <td>
            <div style="font-weight:800"><?= h($o['full_name'] ?? '-') ?></div>
            <div class="small" style="opacity:.85"><?= h($o['email'] ?? '-') ?></div>
            <div class="small" style="margin-top:6px; white-space:pre-wrap; opacity:.9">
              <b>ที่อยู่จัดส่ง:</b><br>
              <?= h($o['shipping_address'] ?? '-') ?>
            </div>
            <div class="small" style="margin-top:6px; opacity:.8">
              สร้างเมื่อ: <?= h($o['created_at'] ?? '-') ?>
            </div>
          </td>

          <td>฿<?= number_format((float)($o['total'] ?? 0), 2) ?></td>

          <td>
            <span class="badge"><?= h($o['payment_status'] ?? '-') ?></span>
            <?php if(!empty($o['slip_uploaded_at'])): ?>
              <div class="small" style="opacity:.8; margin-top:6px">
                อัปโหลด: <?= h($o['slip_uploaded_at']) ?>
              </div>
            <?php endif; ?>
          </td>

          <td>
            <span class="badge"><?= h($o['shipping_status'] ?? '-') ?></span>
          </td>

          <td>
            <?php if(!empty($o['slip_path'])): ?>
              <a class="btn sky" target="_blank" href="<?= h(url($o['slip_path'])) ?>">ดูสลิป</a>
            <?php else: ?>
              <span class="small" style="opacity:.7">-</span>
            <?php endif; ?>
          </td>

          <td>
            <form method="post" class="row wrap" style="gap:10px; align-items:center">
              <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">

              <select class="input" name="payment_status" style="width:150px">
                <?php
                  $payList = ['unpaid','pending','paid','rejected','refunded'];
                  foreach($payList as $p):
                ?>
                  <option value="<?= h($p) ?>" <?= ($o['payment_status']===$p?'selected':'') ?>>
                    <?= h($p) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <select class="input" name="shipping_status" style="width:150px">
                <?php
                  $shipList = ['processing','packing','shipped','delivered','cancelled'];
                  foreach($shipList as $s):
                ?>
                  <option value="<?= h($s) ?>" <?= ($o['shipping_status']===$s?'selected':'') ?>>
                    <?= h($s) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <button class="btn primary" name="update" value="1">บันทึก</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>

      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
