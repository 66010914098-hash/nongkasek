<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();

$slug = trim($_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name
                       FROM products p JOIN categories c ON c.id=p.category_id
                       WHERE p.slug=? AND p.is_active=1");
$stmt->execute([$slug]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); exit('ไม่พบสินค้า'); }

$imgs = product_images($pdo, (int)$p['id']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  cart_init();
  $pid = (int)$p['id'];
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  $_SESSION['cart'][$pid] = min(999, (int)($_SESSION['cart'][$pid] ?? 0) + $qty);
  set_flash('ok','เพิ่มสินค้าในตะกร้าแล้ว');
  redirect('/cart.php');
}

require __DIR__ . '/includes/header.php';
?>
<div class="section-title">
  <div>
    <div class="pill"><?= h($p['category_name']) ?></div>
    <div style="font-size:28px;font-weight:950;margin-top:10px"><?= h($p['name']) ?></div>
    <div class="price" style="font-size:22px">฿<?= number_format((float)$p['price'],2) ?></div>
    <div class="small">คงเหลือ <?= (int)$p['stock'] ?> ชิ้น</div>
  </div>
  <a class="btn" href="<?= h(url('/products.php')) ?>">← กลับหน้าสินค้า</a>
</div>

<div class="split" style="margin-top:12px">
  <div class="card panel">
    <div class="thumb" style="aspect-ratio: 16/10">
      <?php if(!empty($imgs)): ?>
        <img data-gallery-main src="<?= h(url($imgs[0]['image_path'])) ?>" alt="">
      <?php else: ?>
        <div class="small">ยังไม่มีรูปสินค้า (เพิ่มได้จากหลังร้าน)</div>
      <?php endif; ?>
    </div>

    <?php if(count($imgs) > 1): ?>
      <div class="gallery" style="margin-top:12px">
        <?php foreach($imgs as $im): ?>
          <button class="g" style="background:none;border:none;padding:0;cursor:pointer"
                  data-gallery-thumb data-src="<?= h(url($im['image_path'])) ?>">
            <img src="<?= h(url($im['image_path'])) ?>" alt="">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="hr"></div>
    <div style="font-weight:950;margin-bottom:8px">รายละเอียดสินค้า</div>
    <div class="small"><?= nl2br(h($p['description'] ?? '')) ?></div>
  </div>

  <div class="card panel">
    <div style="font-weight:950;font-size:18px;margin-bottom:10px">สั่งซื้อ</div>
    <form method="post">
      <label class="small">จำนวน</label>
      <input class="input" type="number" name="qty" min="1" value="1">
      <div style="margin-top:14px"></div>
      <button class="btn primary" type="submit">เพิ่มลงตะกร้า</button>
      <a class="btn sky" href="<?= h(url('/cart.php')) ?>">ไปที่ตะกร้า</a>
    </form>
    <div class="hr"></div>
    <div class="small">เดโมการชำระเงิน: อัปโหลดสลิปในหน้า Checkout</div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
