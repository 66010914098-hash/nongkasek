<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = db();   // << ต้องมีบรรทัดนี้

if(empty($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];



$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if($password){
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("
            UPDATE users 
            SET full_name=?, phone=?, password=? 
            WHERE id=?
        ");

        $update->execute([$full_name, $phone, $hash, $user_id]);

    }else{

        $update = $pdo->prepare("
            UPDATE users 
            SET full_name=?, phone=? 
            WHERE id=?
        ");

        $update->execute([$full_name, $phone, $user_id]);
    }

    $_SESSION['success'] = "อัปเดตข้อมูลสำเร็จ";

    header("Location: profile.php");
    exit;
}

require __DIR__ . '/includes/header.php';
?>

<div class="card panel" style="max-width:720px; margin:40px auto;">
  <div class="section-title">
    <div>
      <div class="h2">แก้ไขข้อมูลส่วนตัว</div>
      <div class="small">อัปเดตชื่อ เบอร์โทร หรือเปลี่ยนรหัสผ่าน</div>
    </div>
  </div>

  <?php if(!empty($_SESSION['success'])): ?>
    <div class="toast ok" style="margin-top:14px">
      <b>สำเร็จ</b>
      <div><?= $_SESSION['success'] ?></div>
    </div>
  <?php unset($_SESSION['success']); endif; ?>

  <form method="post" style="margin-top:20px">

    <div style="margin-bottom:16px">
      <label class="small">ชื่อ</label>
      <input class="input" type="text" name="full_name"
        value="<?= h($user['full_name']) ?>" required>
    </div>

    <div style="margin-bottom:16px">
      <label class="small">Email (แก้ไม่ได้)</label>
      <input class="input" type="text"
        value="<?= h($user['email']) ?>" disabled>
    </div>

    <div style="margin-bottom:16px">
      <label class="small">เบอร์โทร</label>
      <input class="input" type="text"
        name="phone"
        value="<?= h($user['phone']) ?>">
    </div>

    <div style="margin-bottom:20px">
      <label class="small">รหัสผ่านใหม่ (ไม่กรอก = ไม่เปลี่ยน)</label>
      <input class="input" type="password" name="password">
    </div>

    <button class="btn primary">บันทึกข้อมูล</button>

  </form>
</div>


<?php require __DIR__ . '/includes/footer.php'; ?>
