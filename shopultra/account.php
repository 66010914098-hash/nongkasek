<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require __DIR__ . '/includes/header.php';
?>
<div class="card panel">
  <div style="font-size:24px;font-weight:950">สวัสดี, <?= h($_SESSION['user_name'] ?? 'สมาชิก') ?></div>
  <div class="small">เมนูบัญชี</div>
  <div class="row wrap" style="margin-top:14px">
    <a class="btn sky" href="<?= h(url('/orders.php')) ?>">ประวัติการสั่งซื้อ</a>
    <a class="btn" href="<?= h(url('/cart.php')) ?>">ตะกร้า</a>
    <a class="btn danger" href="<?= h(url('/logout.php')) ?>">ออกจากระบบ</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
