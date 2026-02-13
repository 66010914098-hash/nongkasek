<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = db();

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'new';
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 12;

$orderBy = "p.created_at DESC";
if ($sort === 'price_asc') $orderBy = "p.price ASC";
if ($sort === 'price_desc') $orderBy = "p.price DESC";
if ($sort === 'stock') $orderBy = "p.stock DESC";

$where = " WHERE p.is_active=1 ";
$params = [];

if ($cat > 0) { $where .= " AND p.category_id=? "; $params[] = $cat; }
if ($q !== '') { $where .= " AND (p.name LIKE ? OR p.description LIKE ?) "; $params[]="%$q%"; $params[]="%$q%"; }

$countStmt = $pdo->prepare("SELECT COUNT(*) AS c FROM products p $where");
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($totalRows / $per));
$page = min($page, $totalPages);
$offset = ($page - 1) * $per;

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        $where
        ORDER BY {$orderBy}
        LIMIT {$per} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

function build_qs(array $extra): string {
  $q = $_GET;
  foreach($extra as $k=>$v){ $q[$k] = $v; }
  return http_build_query($q);
}

require __DIR__ . '/includes/header.php';
?>
<div class="section-title">
  <div>
    <div class="h2">สินค้า</div>
    <div class="small">ค้นหา • กรอง • เรียง • แบ่งหน้า</div>
  </div>
  <div class="pill green">พบ <?= (int)$totalRows ?> รายการ</div>
</div>

<div class="split" style="margin-top:12px">
  <aside class="card panel">
    <div style="font-weight:950;font-size:18px;margin-bottom:10px">ตัวกรอง</div>
    <form method="get">
      <label class="small">ค้นหา</label>
      <input class="input" name="q" value="<?= h($q) ?>" placeholder="เช่น 16-16-16, อินทรีย์">

      <div style="margin-top:12px"></div>
      <label class="small">หมวดสินค้า</label>
      <select class="input" name="cat">
        <option value="0">ทั้งหมด</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <div style="margin-top:12px"></div>
      <label class="small">เรียงลำดับ</label>
      <select class="input" name="sort">
        <option value="new" <?= $sort==='new'?'selected':'' ?>>ใหม่ล่าสุด</option>
        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>ราคา: น้อย→มาก</option>
        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>ราคา: มาก→น้อย</option>
        <option value="stock" <?= $sort==='stock'?'selected':'' ?>>คงเหลือมากสุด</option>
      </select>

      <div class="row wrap" style="margin-top:14px">
        <button class="btn primary" type="submit">ใช้ตัวกรอง</button>
        <a class="btn" href="<?= h(url('/products.php')) ?>">ล้าง</a>
      </div>
    </form>
  </aside>

  <section>
    <?php if(!$products): ?>
      <div class="card panel"><div class="small">ไม่พบสินค้า</div></div>
    <?php else: ?>
      <div class="grid">
        <?php foreach($products as $p): $cover = product_cover($pdo, (int)$p['id']); ?>
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

      <div class="row between wrap" style="margin-top:14px">
        <div class="small">หน้า <?= $page ?> / <?= $totalPages ?></div>
        <div class="pagination">
          <?php for($i=1;$i<=$totalPages;$i++): ?>
            <a class="<?= $i===$page?'active':'' ?>" href="<?= h(url('/products.php?'.build_qs(['page'=>$i]))) ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
