<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
$err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if (!$u || !password_verify($pass, $u['password_hash'])) $err = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
  else {
    $_SESSION['user_id'] = (int)$u['id'];
    $_SESSION['user_name'] = $u['full_name'];
    set_flash('ok','เข้าสู่ระบบสำเร็จ');
    redirect('/account.php');
  }
}
require __DIR__ . '/includes/header.php';
?>
<div class="card panel" style="max-width:560px;margin:0 auto">
  <div class="h2">เข้าสู่ระบบ</div>
  <div class="small">เข้าสู่ระบบเพื่อชำระเงินและดูประวัติออเดอร์</div>
  <div style="margin-top:12px"></div>
  <?php if($err): ?>
    <div class="toast err" style="position:static;max-width:none">
      <div class="t">ผิดพลาด</div><div class="m"><?= h($err) ?></div>
    </div>
    <div style="margin-top:12px"></div>
  <?php endif; ?>
  <form method="post">
    <label class="small">อีเมล</label><input class="input" name="email" type="email" required>
    <div style="margin-top:12px"></div>
    <label class="small">รหัสผ่าน</label><input class="input" name="password" type="password" required>
    <div class="row wrap" style="margin-top:14px">
      <button class="btn primary">เข้าใช้งาน</button>
      <a class="btn" href="<?= h(url('/register.php')) ?>">สมัครสมาชิก</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
