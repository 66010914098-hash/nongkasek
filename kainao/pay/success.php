<?php
session_start();
define('BASE_URL', '/fertilizer-shop-ultra/kaipui/fertilizer-shop-ultra'); // ✅ ปรับให้ตรง
$order_id = (int)($_GET['order_id'] ?? 0);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ชำระเงินสำเร็จ</title>
</head>
<body style="font-family:Arial;margin:20px">
  <h2>ส่งสลิปแล้ว ✅</h2>
  <p>เลขออเดอร์: #<?= (int)$order_id ?></p>
  <p><a href="<?= BASE_URL ?>/pay/?order_id=<?= (int)$order_id ?>">กลับไปหน้าจ่ายเงิน</a></p>
  <p><a href="<?= BASE_URL ?>/orders.php">ไปหน้ารายการสั่งซื้อ</a></p>
</body>
</html>
