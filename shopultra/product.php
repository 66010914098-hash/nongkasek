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

// add to cart
if ($_SERVER['REQUEST_METHOD']==='POST') {
  cart_init();
  $pid = (int)$p['id'];
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  $_SESSION['cart'][$pid] = min(999, (int)($_SESSION['cart'][$pid] ?? 0) + $qty);
  set_flash('ok','เพิ่มสินค้าในตะกร้าแล้ว');
  redirect('/cart.php');
}

require __DIR__ . '/includes/header.php';

// ===== build gallery sources =====
$gallery = [];
if (!empty($imgs)) {
  foreach ($imgs as $im) {
    // สำคัญ: รูปอยู่ที่ /uploads/products/
    $gallery[] = url('/uploads/products/' . $im['image_path']);
  }
} elseif (!empty($p['cover_image'])) {
  $gallery[] = url('/uploads/products/' . $p['cover_image']);
}
$mainSrc = $gallery[0] ?? '';
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

    <div class="thumb ultra-gallery" data-gallery-wrap>
      <?php if($mainSrc): ?>
        <div class="ultra-main-wrap">
          <button type="button" class="ultra-nav prev" data-prev aria-label="ก่อนหน้า">‹</button>

          <img
            data-gallery-main
            src="<?= h($mainSrc) ?>"
            alt="<?= h($p['name']) ?>"
            loading="lazy"
          >

          <button type="button" class="ultra-nav next" data-next aria-label="ถัดไป">›</button>

          <div class="ultra-counter" data-counter>1 / <?= (int)count($gallery) ?></div>
          <div class="ultra-hint">แตะ/คลิกเพื่อขยาย</div>
        </div>

        <?php if(count($gallery) > 1): ?>
          <div class="gallery ultra-thumbs" data-thumbs>
            <?php foreach($gallery as $i => $src): ?>
              <button
                type="button"
                class="g <?= $i === 0 ? 'active' : '' ?>"
                data-gallery-thumb
                data-index="<?= (int)$i ?>"
                data-src="<?= h($src) ?>"
                aria-label="ดูรูปที่ <?= (int)($i+1) ?>"
              >
                <img src="<?= h($src) ?>" alt="">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="small">ยังไม่มีรูปสินค้า</div>
      <?php endif; ?>
    </div>

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
    <div class="small">การชำระเงิน: อัปโหล</div>
  </div>
</div>

<!-- Lightbox (เทพสุด: Zoom / Pan / Pinch / Auto Slide) -->
<div class="ultra-lightbox" data-lightbox hidden>
  <div class="ultra-lb-topbar">
    <div class="ultra-lb-title"><?= h($p['name']) ?></div>

    <button class="ultra-lb-btn" data-zoom-out type="button" aria-label="ซูมออก">−</button>
    <button class="ultra-lb-btn" data-zoom-in type="button" aria-label="ซูมเข้า">+</button>

    <button class="ultra-lb-btn" data-auto type="button" aria-label="เล่นสไลด์อัตโนมัติ">▶</button>
    <button class="ultra-lb-btn ultra-lb-close" data-close type="button" aria-label="ปิด">×</button>
  </div>

  <button class="ultra-lb-nav prev" data-lb-prev aria-label="ก่อนหน้า">‹</button>

  <div class="ultra-lb-stage" data-stage>
    <img data-lb-img alt="">
  </div>

  <button class="ultra-lb-nav next" data-lb-next aria-label="ถัดไป">›</button>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
