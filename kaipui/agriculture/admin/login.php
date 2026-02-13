<?php
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim((string)($_POST['username'] ?? ''));
  $password = (string)($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $err = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
  } else {
    $st = $pdo->prepare("SELECT * FROM admins WHERE username=? LIMIT 1");
    $st->execute([$username]);
    $admin = $st->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
      $_SESSION['is_admin'] = 1;
      $_SESSION['admin_id'] = (int)$admin['id'];
      $_SESSION['admin_username'] = $admin['username'];

      set_flash('ok', 'เข้าสู่ระบบหลังบ้านแล้ว');
      redirect('/admin/index.php');
    } else {
      $err = 'Username หรือ Password ไม่ถูกต้อง';
    }
  }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="card panel" style="max-width:520px; margin:0 auto">
  <div class="section-title">
    <div>
      <div class="h2">หลังร้าน (Admin)</div>
      <div class="small">เข้าสู่ระบบเพื่อจัดการสินค้า/ออเดอร์</div>
    </div>
    <a class="btn" href="<?= h(url('/')) ?>">🏠 กลับหน้าหลัก</a>
  </div>

  <?php if($err): ?>
    <div class="toast err"><b>ผิดพลาด</b><div><?= h($err) ?></div></div>
  <?php endif; ?>

  <form method="post" style="margin-top:14px">
    <div class="small">Username</div>
    <input class="input" name="username" required>

    <div class="small" style="margin-top:10px">Password</div>
    <input class="input" type="password" name="password" required>

    <button class="btn primary" style="margin-top:14px; width:100%">เข้าสู่ระบบ</button>

    <div class="small" style="margin-top:10px; opacity:.8">
      ถ้ายังไม่มีแอดมิน ให้ไปที่ <a href="<?= h(url('/admin/setup_admin.php')) ?>">setup_admin.php</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
