<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pdo = db();

$user_id = current_user_id();

// ออเดอร์ของ user นี้เท่านั้น
$st = $pdo->prepare("
  SELECT *
  FROM orders
  WHERE user_id=?
  ORDER BY created_at DESC
  LIMIT 200
");
$st->execute([$user_id]);
$orders = $st->fetchAll();

// items map (ถ้ามีตาราง order_items)
$order_items_map = [];
try {
  if ($orders) {
    $ids = array_map(function($o){ return (int)$o['id']; }, $orders);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $sti = $pdo->prepare("
      SELECT order_id, product_name, qty, subtotal
      FROM order_items
      WHERE order_id IN ($ph)
      ORDER BY id ASC
    ");
    $sti->execute($ids);
    $rows = $sti->fetchAll();
    foreach ($rows as $r) {
      $oid = (int)$r['order_id'];
      if (!isset($order_items_map[$oid])) $order_items_map[$oid] = [];
      $order_items_map[$oid][] = $r;
    }
  }
} catch (Exception $e) { /* ไม่มีตารางก็ข้าม */ }

function status_th($s) {
  if ($s === 'paid') return 'ชำระแล้ว';
  if ($s === 'rejected') return 'ตีกลับ/ไม่ผ่าน';
  if ($s === 'unpaid') return 'ค้างชำระ';
  if ($s === 'pending') return 'ค้างชำระ/รอตรวจ';
  return (string)$s;
}

require __DIR__ . '/includes/header.php';
?>

<div class="card panel">
  <div class="section-title">
    <div>
      <div class="h2">ออเดอร์ของฉัน</div>
      <div class="small">ค้างชำระ / ชำระแล้ว / ดูสลิป</div>
    </div>
  </div>

  <?php if(!$orders): ?>
    <div class="card" style="padding:18px;margin-top:14px">
      <div class="h3">ยังไม่มีออเดอร์</div>
      <a class="btn primary" style="margin-top:12px" href="<?= h(url('/products.php')) ?>">ไปหน้าสินค้า</a>
    </div>
  <?php else: ?>

    <?php foreach($orders as $o):
      $oid = (int)$o['id'];
      $pay = (string)($o['payment_status'] ?? 'pending');
      $is_pending = in_array($pay, ['pending','unpaid'], true);
    ?>
      <div class="card" style="padding:18px;margin-top:14px">
        <div class="row between wrap" style="gap:12px">
          <div>
            <div class="h3">ออเดอร์ #<?= $oid ?></div>
            <div class="small" style="opacity:.9;margin-top:6px">
              วันที่: <?= h($o['created_at'] ?? '-') ?> • สถานะชำระเงิน: <b><?= h(status_th($pay)) ?></b>
            </div>
          </div>

          <div class="row wrap" style="gap:10px;align-items:center">
            <span class="pill">ยอดรวม ฿<?= number_format((float)($o['total'] ?? 0),2) ?></span>

            <?php if($is_pending): ?>
              <a class="btn primary" href="<?= h(url('/pay.php?order_id='.$oid)) ?>">ไปจ่าย</a>
            <?php endif; ?>

            <?php if(!empty($o['slip_path'])): ?>
              <a class="btn sky" href="<?= h(url($o['slip_path'])) ?>" target="_blank">ดูสลิป</a>
            <?php endif; ?>
          </div>
        </div>

        <details style="margin-top:12px">
          <summary class="btn" style="padding:8px 12px">ดูที่อยู่จัดส่ง</summary>
          <div class="small" style="margin-top:10px;white-space:pre-wrap">
<?= h($o['shipping_address'] ?? '-') ?>
          </div>
        </details>

        <div style="margin-top:14px">
          <div class="h3">รายการสินค้า</div>
          <?php if(empty($order_items_map[$oid])): ?>
            <div class="small" style="margin-top:8px;opacity:.85">ไม่พบรายการสินค้า</div>
          <?php else: ?>
            <table class="table" style="margin-top:10px">
              <thead><tr><th>สินค้า</th><th style="width:90px">จำนวน</th><th style="width:140px">รวม</th></tr></thead>
              <tbody>
                <?php foreach($order_items_map[$oid] as $it): ?>
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
    <?php endforeach; ?>

  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
