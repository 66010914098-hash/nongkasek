<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$p = ['category_id'=>($cats[0]['id'] ?? 1), 'name'=>'','slug'=>'','price'=>'0','stock'=>'0','description'=>'','is_active'=>1];
if ($id) {
  $st = $pdo->prepare("SELECT * FROM products WHERE id=?");
  $st->execute([$id]);
  $row = $st->fetch();
  if ($row) $p = $row;
}

$imgs = [];
if ($id) {
  $st = $pdo->prepare("SELECT * FROM product_images WHERE product_id=? ORDER BY sort_order ASC, id ASC");
  $st->execute([$id]);
  $imgs = $st->fetchAll();
}

if (isset($_POST['save'])) {
  $category_id = (int)($_POST['category_id'] ?? 1);
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $stock = (int)($_POST['stock'] ?? 0);
  $desc = trim($_POST['description'] ?? '');
  $active = isset($_POST['is_active']) ? 1 : 0;

  if ($id) {
    $pdo->prepare("UPDATE products SET category_id=?, name=?, slug=?, price=?, stock=?, description=?, is_active=? WHERE id=?")
        ->execute([$category_id,$name,$slug,$price,$stock,$desc,$active,$id]);
  } else {
    $pdo->prepare("INSERT INTO products(category_id,name,slug,price,stock,description,is_active) VALUES(?,?,?,?,?,?,?)")
        ->execute([$category_id,$name,$slug,$price,$stock,$desc,$active]);
    $id = (int)$pdo->lastInsertId();
  }

  if (!empty($_FILES['images']['name'][0])) {
    ensure_dir(PRODUCT_IMG_DIR);
    $count = count($_FILES['images']['name']);
    $nextSort = (int)($_POST['next_sort'] ?? 0);
    for ($i=0; $i<$count; $i++) {
      $file = [
        'name' => $_FILES['images']['name'][$i],
        'type' => $_FILES['images']['type'][$i],
        'tmp_name' => $_FILES['images']['tmp_name'][$i],
        'error' => $_FILES['images']['error'][$i],
        'size' => $_FILES['images']['size'][$i],
      ];
      if (!empty($file['name'])) {
        $webPath = safe_upload($file, ['jpg','jpeg','png','webp'], PRODUCT_IMG_DIR, 'p'.$id);
        $pdo->prepare("INSERT INTO product_images(product_id,image_path,sort_order) VALUES(?,?,?)")
            ->execute([$id,$webPath,$nextSort]);
        $nextSort++;
      }
    }
  }

  set_flash('ok','บันทึกสินค้าแล้ว');
  redirect('/admin/product_form.php?id='.$id);
}

if (isset($_POST['delete_img'])) {
  $imgId = (int)($_POST['img_id'] ?? 0);
  $pdo->prepare("DELETE FROM product_images WHERE id=?")->execute([$imgId]);
  set_flash('ok','ลบรูปแล้ว');
  redirect('/admin/product_form.php?id='.$id);
}

require __DIR__ . '/../includes/header.php';
?>
<div class="card panel" style="max-width:980px;margin:0 auto">
  <div class="section-title">
    <div><div class="h2"><?= $id ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า' ?></div><div class="small">อัปโหลดรูปได้หลายรูป</div></div>
    <a class="btn" href="<?= h(url('/admin/products.php')) ?>">กลับ</a>
  </div>

  <div style="margin-top:12px"></div>
  <form method="post" enctype="multipart/form-data">
    <label class="small">หมวดสินค้า</label>
    <select class="input" name="category_id">
      <?php foreach($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$p['category_id']===(int)$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <div style="margin-top:12px"></div>
    <label class="small">ชื่อสินค้า</label>
    <input class="input" name="name" value="<?= h((string)$p['name']) ?>" required>

    <div style="margin-top:12px"></div>
    <label class="small">Slug</label>
    <input class="input" name="slug" value="<?= h((string)$p['slug']) ?>" required>

    <div class="row wrap" style="margin-top:12px;align-items:flex-end">
      <div style="flex:1;min-width:180px"><label class="small">ราคา</label><input class="input" type="number" step="0.01" name="price" value="<?= h((string)$p['price']) ?>" required></div>
      <div style="flex:1;min-width:180px"><label class="small">สต็อก</label><input class="input" type="number" name="stock" value="<?= h((string)$p['stock']) ?>" required></div>
      <div style="flex:1;min-width:180px"><label class="small">สถานะ</label><br><label class="small"><input type="checkbox" name="is_active" <?= (int)$p['is_active']===1?'checked':'' ?>> เปิดขาย</label></div>
    </div>

    <div style="margin-top:12px"></div>
    <label class="small">รายละเอียด</label>
    <textarea class="input" name="description" rows="5"><?= h((string)$p['description']) ?></textarea>

    <div style="margin-top:12px"></div>
    <label class="small">อัปโหลดรูปสินค้า (หลายรูป)</label>
    <input class="input" type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
    <input type="hidden" name="next_sort" value="<?= (int)count($imgs) ?>">

    <div style="margin-top:14px"></div>
    <button class="btn primary" name="save" value="1">บันทึก</button>
  </form>

  <?php if($id): ?>
    <div class="hr"></div>
    <div style="font-weight:950;margin-bottom:8px">รูปสินค้า</div>
    <?php if(!$imgs): ?>
      <div class="small">ยังไม่มีรูป</div>
    <?php else: ?>
      <div class="row wrap">
        <?php foreach($imgs as $im): ?>
          <div class="card panel" style="width:220px;box-shadow:none">
            <div class="thumb"><img src="<?= h(url($im['image_path'])) ?>" alt=""></div>
            <div style="margin-top:12px"></div>
            <form method="post">
              <input type="hidden" name="img_id" value="<?= (int)$im['id'] ?>">
              <button class="btn danger" name="delete_img" value="1" onclick="return confirm('ลบรูปนี้?')">ลบรูป</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
