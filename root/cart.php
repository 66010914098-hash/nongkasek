<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
cart_init();

if (isset($_POST['update'])) {
  foreach ($_POST['qty'] as $pid => $qty) {
    $pid = (int)$pid; $qty = (int)$qty;
    if ($qty <= 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid] = min(999, $qty);
  }
  set_flash('ok','อัปเดตตะกร้าแล้ว');
  redirect('/cart.php');
}

$ids = array_keys($_SESSION['cart']);
$rows = [];
$total = 0.0;

if ($ids) {
  $in = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id,name,price FROM products WHERE id IN ($in)");
  $stmt->execute($ids);
  foreach ($stmt->fetchAll() as $p) {
    $qty = (int)($_SESSION['cart'][(int)$p['id']] ?? 0);
    $sub = $qty * (float)$p['price'];
    $total += $sub;
    $rows[] = ['p'=>$p,'qty'=>$qty,'sub'=>$sub];
  }
}

require __DIR__ . '/includes/header.php';
?>
<div class="section-title">
  <div>
    <div class="h2">ตะกร้าสินค้า</div>
    <div class="small">ปรับจำนวนได้ • ใส่ 0 เพื่อลบ</div>
  </div>
  <a class="btn" href="<?= h(url('/products.php')) ?>">เลือกสินค้าเพิ่ม</a>
</div>

<div class="card panel" style="margin-top:12px">
<?php if(!$rows): ?>
  <div class="small">ยังไม่มีสินค้าในตะกร้า</div>
<?php else: ?>
  <form method="post">
    <table class="table">
      <thead><tr><th>สินค้า</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td style="font-weight:950"><?= h($r['p']['name']) ?></td>
            <td>฿<?= number_format((float)$r['p']['price'],2) ?></td>
            <td style="width:160px"><input class="input" type="number" min="0" name="qty[<?= (int)$r['p']['id'] ?>]" value="<?= (int)$r['qty'] ?>"></td>
            <td>฿<?= number_format((float)$r['sub'],2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="row between wrap" style="margin-top:12px">
      <div class="price" style="font-size:22px">รวมทั้งหมด: ฿<?= number_format((float)$total,2) ?></div>
      <div class="row wrap">
        <button class="btn" name="update" value="1">อัปเดตตะกร้า</button>
        <a class="btn primary" href="<?= h(url('/checkout.php')) ?>">ไปชำระเงิน</a>
      </div>
    </div>
  </form>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
