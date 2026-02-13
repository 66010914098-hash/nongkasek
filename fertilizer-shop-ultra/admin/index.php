<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();
$stat_products = (int)$pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'];
$stat_orders = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'];
$stat_users = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$stat_paid = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders WHERE payment_status='paid'")->fetch()['c'];
require __DIR__ . '/../includes/header.php';
?>
<div class="card panel">
  <div class="section-title">
    <div>
      <div class="h2">แดชบอร์ดหลังร้าน</div>
      <div class="small">สวัสดี <?= h($_SESSION['admin_username'] ?? 'admin') ?></div>
    </div>
    <a class="btn danger" href="<?= h(url('/admin/logout.php')) ?>">ออก</a>
  </div>

  <div class="stat" style="margin-top:12px">
    <div class="box"><div class="small">สินค้า</div><div style="font-weight:950;font-size:22px"><?= $stat_products ?></div></div>
    <div class="box"><div class="small">ออเดอร์ทั้งหมด</div><div style="font-weight:950;font-size:22px"><?= $stat_orders ?></div></div>
    <div class="box"><div class="small">ชำระแล้ว</div><div style="font-weight:950;font-size:22px"><?= $stat_paid ?></div></div>
    <div class="box"><div class="small">สมาชิก</div><div style="font-weight:950;font-size:22px"><?= $stat_users ?></div></div>
  </div>

  <div class="hr"></div>
  <div class="row wrap">
    <a class="btn primary" href="<?= h(url('/admin/orders.php')) ?>">ออเดอร์</a>
    <a class="btn sky" href="<?= h(url('/admin/products.php')) ?>">สินค้า</a>
    <a class="btn" href="<?= h(url('/admin/categories.php')) ?>">หมวด</a>
    <a class="btn" href="<?= h(url('/admin/users.php')) ?>">ลูกค้า</a>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
