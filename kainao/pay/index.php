<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_URL', '/fertilizer-shop-ultra/kaipui/fertilizer-shop-ultra'); // ✅ ปรับให้ตรง root เว็บคุณ

require_once __DIR__ . '/../includes/functions.php';

try {
  $pdo = db();
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>DB connect error</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
  exit;
}

$user_id  = (int)($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0);
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$user_id) {
  header("Location: " . BASE_URL . "/login.php");
  exit;
}
if ($order_id <= 0) {
  http_response_code(400);
  echo "Missing order_id";
  exit;
}

// โหลดออเดอร์ (กันคนอื่นแอบดู)
try {
  $st = $pdo->prepare("SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1");
  $st->execute([$order_id, $user_id]);
  $order = $st->fetch(PDO::FETCH_ASSOC);
  if (!$order) {
    http_response_code(404);
    echo "Order not found";
    exit;
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo "<h3>SQL error (orders)</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
  exit;
}

// flash แบบง่าย
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>จ่ายเงิน #<?= (int)$order_id ?></title>
  <style>
    body{font-family:Arial;margin:20px;background:#f6f6f6}
    .wrap{max-width:820px;margin:0 auto}
    .card{background:#fff;border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:14px}
    .msg{padding:10px;border-radius:10px;border:1px solid #ddd;margin-bottom:12px}
    img{max-width:100%;border-radius:12px;border:1px solid #eee}
    button{padding:10px 14px;border:0;border-radius:10px;cursor:pointer}
  </style>
</head>
<body>
<div class="wrap">

  <div class="card">
    <h2>จ่ายเงินคำสั่งซื้อ #<?= (int)$order_id ?></h2>

    <?php if ($flash): ?>
      <div class="msg"><?= htmlspecialchars((string)$flash) ?></div>
    <?php endif; ?>

    <p><b>ยอดรวม:</b> <?= htmlspecialchars((string)($order['total_amount'] ?? $order['total'] ?? '0')) ?></p>
    <p><b>สถานะ:</b> <?= htmlspecialchars((string)($order['payment_status'] ?? $order['status'] ?? '-')) ?></p>

    <?php if (!empty($order['slip_path'])): ?>
      <p><b>สลิปที่อัปโหลด:</b></p>
      <img src="<?= htmlspecialchars((string)$order['slip_path']) ?>" alt="slip">
    <?php else: ?>
      <p><i>ยังไม่มีสลิป</i></p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>อัปโหลดสลิป</h3>
    <form method="post" action="<?= BASE_URL ?>/pay/upload.php?order_id=<?= (int)$order_id ?>" enctype="multipart/form-data">
      <input type="file" name="slip" accept="image/*" required>
      <br><br>
      <button type="submit" name="upload_slip">อัปโหลด</button>
    </form>
  </div>

  <div class="card">
    <a href="<?= BASE_URL ?>/orders.php">← กลับหน้ารายการสั่งซื้อ</a>
  </div>

</div>
</body>
</html>
