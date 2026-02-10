<?php
require_once __DIR__ . '/includes/functions.php';
cart_init();
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
  $_SESSION['cart'][$id] = min(999, (int)($_SESSION['cart'][$id] ?? 0) + 1);
  set_flash('ok','เพิ่มสินค้าในตะกร้าแล้ว');
}
redirect('/cart.php');
