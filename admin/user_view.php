<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if(!$user){
    die("ไม่พบข้อมูลลูกค้า");
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>รายละเอียดลูกค้า</h2>
    <p><strong>ชื่อ:</strong> <?= h($user['full_name']) ?></p>
    <p><strong>Email:</strong> <?= h($user['email']) ?></p>
    <p><strong>เบอร์โทร:</strong> <?= h($user['phone']) ?: '-' ?></p>
    <p><strong>วันที่สมัคร:</strong> <?= h($user['created_at']) ?></p>

    <a href="<?= h(url('/admin/users.php')) ?>" class="btn">กลับ</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
