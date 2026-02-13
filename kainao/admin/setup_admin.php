<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = db();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? 'admin');
  $password = $_POST['password'] ?? 'admin123';
  if ($username === '' || strlen($password) < 6) $msg = 'กรุณาใส่ username และรหัสผ่านอย่างน้อย 6 ตัว';
  else {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username=?");
    $stmt->execute([$username]);
    $exists = $stmt->fetch();
    if ($exists) {
      $pdo->prepare("UPDATE admins SET password_hash=? WHERE username=?")->execute([$hash, $username]);
      $msg = "อัปเดตรหัสผ่านแอดมินสำเร็จ: {$username}";
    } else {
      $pdo->prepare("INSERT INTO admins(username, password_hash) VALUES(?,?)")->execute([$username, $hash]);
      $msg = "สร้างแอดมินสำเร็จ: {$username}";
    }
  }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="card panel" style="max-width:720px;margin:0 auto">
  <div class="h2">ตั้งค่าแอดมิน (ครั้งแรก)</div>
  <div class="small">แนะนำ: สร้างเสร็จแล้วลบไฟล์ setup_admin.php เพื่อความปลอดภัย</div>
  <div style="margin-top:12px"></div>
  <?php if($msg): ?>
    <div class="toast ok" style="position:static;max-width:none"><div class="t">สำเร็จ</div><div class="m"><?= h($msg) ?></div></div>
    <div style="margin-top:12px"></div>
  <?php endif; ?>
  <form method="post">
    <label class="small">Username</label><input class="input" name="username" value="admin" required>
    <div style="margin-top:12px"></div>
    <label class="small">Password</label><input class="input" name="password" type="password" value="admin123" required>
    <div class="row wrap" style="margin-top:14px">
      <button class="btn primary">สร้าง/อัปเดตแอดมิน</button>
      <a class="btn sky" href="<?= h(url('/admin/login.php')) ?>">ไปหน้า Login</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
