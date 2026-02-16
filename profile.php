<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pdo = db();

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

require __DIR__ . '/includes/header.php';
?>

<div class="card panel" style="max-width:720px; margin:40px auto;">

  <div style="text-align:center; margin-bottom:25px">
    <div style="
      width:90px;
      height:90px;
      border-radius:50%;
      background:linear-gradient(135deg,#00c853,#64dd17);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-size:32px;
      font-weight:bold;
      color:#fff;">
      <?= strtoupper(substr($user['full_name'],0,1)) ?>
    </div>

    <div class="h2" style="margin-top:15px">
      <?= h($user['full_name']) ?>
    </div>

    <div class="small" style="opacity:.8">
      <?= h($user['email']) ?>
    </div>
  </div>

  <div style="margin-top:20px">

    <div class="card" style="padding:16px; margin-bottom:12px">
      <div class="small">เบอร์โทร</div>
      <div class="h3"><?= h($user['phone'] ?: '-') ?></div>
    </div>

  </div>

  <div style="text-align:center; margin-top:25px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">

  <a href="profile_edit.php" class="btn primary">
    ✏ แก้ไขข้อมูล
  </a>

  <a href="orders.php" class="btn sky">
    📦 ดูสถานะออเดอร์
  </a>

</div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

