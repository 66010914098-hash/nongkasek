<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
$err = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($name==='' || $email==='' || strlen($pass)<6) $err = 'กรอกข้อมูลให้ครบ (รหัสผ่านอย่างน้อย 6 ตัว)';
  else {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    try {
      $stmt = $pdo->prepare("INSERT INTO users(full_name,email,password_hash) VALUES(?,?,?)");
      $stmt->execute([$name,$email,$hash]);
      set_flash('ok','สมัครสมาชิกสำเร็จ โปรดเข้าสู่ระบบ');
      redirect('/login.php');
    } catch (Throwable $e) { $err = 'อีเมลนี้ถูกใช้งานแล้ว'; }
  }
}
require __DIR__ . '/includes/header.php';
?>
<div class="card panel" style="max-width:560px;margin:0 auto">
  <div class="h2">สมัครสมาชิก</div>
  <div class="small">สร้างบัญชีเพื่อดูประวัติการสั่งซื้อ</div>
  <div style="margin-top:12px"></div>
  <?php if($err): ?>
    <div class="toast err" style="position:static;max-width:none">
      <div class="t">ผิดพลาด</div><div class="m"><?= h($err) ?></div>
    </div>
    <div style="margin-top:12px"></div>
  <?php endif; ?>
  <form method="post">
    <label class="small">ชื่อ-สกุล</label><input class="input" name="full_name" required>
    <div style="margin-top:12px"></div>
    <label class="small">อีเมล</label><input class="input" name="email" type="email" required>
    <div style="margin-top:12px"></div>
    <label class="small">รหัสผ่าน</label><input class="input" name="password" type="password" required>
    <div class="row wrap" style="margin-top:14px">
      <button class="btn primary">สมัคร</button>
      <a class="btn" href="<?= h(url('/login.php')) ?>">มีบัญชีแล้ว</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
