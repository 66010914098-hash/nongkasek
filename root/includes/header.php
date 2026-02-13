<?php
require_once __DIR__ . '/functions.php';
$f = get_flash();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= h(url('/assets/css/ultra.css')) ?>">
</head>
<body>

<header class="topbar">
  <div class="container row between wrap">

    <a class="brand" href="<?= h(url('/')) ?>">
      <img class="logo-img" src="<?= h(url('/assets/img/logo.png')) ?>" alt="logo">
      <span class="brand-name">4สหายขายปุ๋ย</span>
    </a>

    <nav class="nav">
      <a class="btn" href="<?= h(url('/')) ?>">🏠 หน้าหลัก</a>
      <a href="<?= h(url('/products.php')) ?>">สินค้า</a>
      <a href="<?= h(url('/cart.php')) ?>">ตะกร้า <span class="pill"><?= (int)cart_count() ?></span></a>

      <?php if (is_logged_in()): ?>
        <a href="<?= h(url('/orders.php')) ?>">ออเดอร์</a>
        <a class="btn danger" href="<?= h(url('/logout.php')) ?>">ออก</a>
      <?php else: ?>
        <a href="<?= h(url('/login.php')) ?>">เข้าสู่ระบบ</a>
        <a class="btn primary" href="<?= h(url('/register.php')) ?>">สมัครสมาชิก</a>
      <?php endif; ?>

      <a class="btn sky" href="<?= h(url('/admin/login.php')) ?>">หลังร้าน</a>
    </nav>

  </div>
</header>

<main class="container">

<?php if ($f): ?>
  <div class="toast <?= h($f['type']) ?>">
    <b><?= h($f['type']==='ok' ? 'สำเร็จ' : ($f['type']==='err' ? 'ผิดพลาด' : 'แจ้งเตือน')) ?></b>
    <div><?= h($f['msg']) ?></div>
  </div>
<?php endif; ?>
