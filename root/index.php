<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$featured = $pdo->query("SELECT p.*, c.name AS category_name
                         FROM products p JOIN categories c ON c.id=p.category_id
                         WHERE p.is_active=1
                         ORDER BY p.created_at DESC
                         LIMIT 8")->fetchAll();
$stat_products = (int)$pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'];
$stat_orders = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'];
$stat_users = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
require __DIR__ . '/includes/header.php';
?>
<section class="card hero">
  <div class="hero-inner">
    <div>
      <h1 class="h1">เว็บขายปุ๋ย <span style="color:#bfffdc">ULTRA</span> • สวยเหมือนเว็บจริง</h1>
      <p class="small" style="font-size:14px">
        ร้านค้าสำหรับขายอุปกรณ์การเกษตร ปุ๋ย เมล็ดพันธุ์ ผสมผสานดีไซน์ที่ทันสมัยและฟีเจอร์ครบครัน
      </p>
      <div class="kpi" style="margin-top:12px">
        <span class="chip">✅ อุปกรณ์การเกษตร</span>
        <span class="chip">✅ ปุ๋ย</span>
        <span class="chip">✅ เมล็ดพันธุ์</span>
        <span class="chip">✅ สารจำกัดศัตรูพืช</span>
      </div>
      <div class="row wrap" style="margin-top:14px">
        <a class="btn primary" href="<?= h(url('/products.php')) ?>">ดูสินค้าทั้งหมด</a>
        <a class="btn sky" href="<?= h(url('/admin/login.php')) ?>">เข้าหลังร้าน</a>
      </div>
      <div class="hr"></div>
      <div class="stat">
        <div class="box"><div class="small">สินค้า</div><div style="font-weight:950;font-size:22px"><?= $stat_products ?></div></div>
        <div class="box"><div class="small">ออเดอร์</div><div style="font-weight:950;font-size:22px"><?= $stat_orders ?></div></div>
        <div class="box"><div class="small">สมาชิก</div><div style="font-weight:950;font-size:22px"><?= $stat_users ?></div></div>
        <div class="box"><div class="small">พร้อมใช้งาน</div><div style="font-weight:950;font-size:22px">100%</div></div>
      </div>
    </div>
    <div class="card panel" style="box-shadow:none">
      <div style="font-weight:950;font-size:18px;margin-bottom:8px">หมวดหมู่ยอดนิยม</div>
      <div class="small" style="margin-bottom:10px">คลิกเพื่อกรองสินค้า</div>
      <div class="row wrap">
        <?php foreach($cats as $c): ?>
          <a class="pill" href="<?= h(url('/products.php?cat='.(int)$c['id'])) ?>"><?= h($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="hr"></div>
      <div class="small">✅ ราคาดีและมีคุณภาพ (เสี่ยเตอร์การันตี)</div>
    </div>
  </div>
</section>

<div class="section-title" style="margin-top:18px">
  <div>
    <div class="h2">สินค้าแนะนำ</div>
    <div class="small">อัปเดตล่าสุด</div>
  </div>
  <a class="btn" href="<?= h(url('/products.php')) ?>">ไปหน้าสินค้า</a>
</div>

<div class="grid" style="margin-top:12px">
  <?php foreach($featured as $p): $cover = product_cover($pdo, (int)$p['id']); ?>
    <article class="card product">
      <div class="thumb">
        <?php if($cover): ?><img src="<?= h(url($cover)) ?>" alt=""><?php else: ?><div class="small">ไม่มีรูปสินค้า</div><?php endif; ?>
      </div>
      <span class="badge"><?= h($p['category_name']) ?></span>
      <div style="font-weight:950"><?= h($p['name']) ?></div>
      <div class="price">฿<?= number_format((float)$p['price'],2) ?></div>
      <div class="small">คงเหลือ <?= (int)$p['stock'] ?> ชิ้น</div>
      <div class="row wrap">
        <a class="btn sky" href="<?= h(url('/product.php?slug='.urlencode($p['slug']))) ?>">รายละเอียด</a>
        <form method="post" action="<?= h(url('/quick_add.php')) ?>">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn primary">หยิบใส่ตะกร้า</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
