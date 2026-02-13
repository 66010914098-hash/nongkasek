<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

if (isset($_POST['delete'])) {
  $id = (int)($_POST['id'] ?? 0);
  $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  set_flash('ok','ลบสินค้าแล้ว');
  redirect('/admin/products.php');
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id";
$params = [];
if ($q !== '') { $sql .= " WHERE p.name LIKE ? OR p.slug LIKE ? "; $params[]="%$q%"; $params[]="%$q%"; }
$sql .= " ORDER BY p.created_at DESC LIMIT 800";
$st = $pdo->prepare($sql); $st->execute($params);
$products = $st->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="card panel">
  <div class="section-title">
    <div><div class="h2">สินค้า</div><div class="small">เพิ่ม/แก้ไข/ลบ + รูปหลายรูป</div></div>
    <div class="row wrap">
      <a class="btn primary" href="<?= h(url('/admin/product_form.php')) ?>">เพิ่มสินค้า</a>
      <a class="btn" href="<?= h(url('/admin/index.php')) ?>">กลับ</a>
    </div>
  </div>

  <div style="margin-top:12px"></div>
  <form class="row wrap" method="get">
    <input class="input" style="max-width:360px" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อสินค้า/slug">
    <button class="btn sky">ค้นหา</button>
    <a class="btn" href="<?= h(url('/admin/products.php')) ?>">ล้าง</a>
  </form>

  <div style="margin-top:12px"></div>
  <table class="table">
    <thead><tr><th>ID</th><th>สินค้า</th><th>หมวด</th><th>ราคา</th><th>สต็อก</th><th>รูป</th><th></th></tr></thead>
    <tbody>
      <?php foreach($products as $p): $cover = product_cover($pdo, (int)$p['id']); ?>
        <tr>
          <td style="font-weight:950"><?= (int)$p['id'] ?></td>
          <td><?= h($p['name']) ?></td>
          <td><?= h($p['category_name']) ?></td>
          <td>฿<?= number_format((float)$p['price'],2) ?></td>
          <td><?= (int)$p['stock'] ?></td>
          <td><?php if($cover): ?><a class="btn sky" style="padding:7px 10px" href="<?= h(url($cover)) ?>" target="_blank">ดู</a><?php else: ?><span class="small">-</span><?php endif; ?></td>
          <td style="width:260px">
            <div class="row wrap">
              <a class="btn sky" href="<?= h(url('/admin/product_form.php?id='.(int)$p['id'])) ?>">แก้ไข</a>
              <form method="post">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn danger" name="delete" value="1" onclick="return confirm('ลบสินค้านี้?')">ลบ</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
