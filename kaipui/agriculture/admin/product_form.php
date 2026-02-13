<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

function upload_error_text(int $code): string {
  switch ($code) {
    case UPLOAD_ERR_OK: return 'OK';
    case UPLOAD_ERR_INI_SIZE: return 'ไฟล์ใหญ่เกินค่า upload_max_filesize (php.ini)';
    case UPLOAD_ERR_FORM_SIZE: return 'ไฟล์ใหญ่เกิน MAX_FILE_SIZE';
    case UPLOAD_ERR_PARTIAL: return 'อัปโหลดมาไม่ครบ (partial)';
    case UPLOAD_ERR_NO_FILE: return 'ไม่ได้เลือกไฟล์';
    case UPLOAD_ERR_NO_TMP_DIR: return 'ไม่มีโฟลเดอร์ temp บนเซิร์ฟเวอร์';
    case UPLOAD_ERR_CANT_WRITE: return 'เขียนไฟล์ลงดิสก์ไม่ได้ (permission)';
    case UPLOAD_ERR_EXTENSION: return 'ถูก PHP extension บล็อก';
    default: return 'ไม่ทราบสาเหตุ (code '.$code.')';
  }
}

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
  $description = trim($_POST['description'] ?? '');
  $is_active = (int)($_POST['is_active'] ?? 0);

  if ($name === '') {
    set_flash('err', 'กรุณากรอกชื่อสินค้า');
    redirect('/agriculture/admin/product_form.php' . ($id ? '?id=' . $id : ''));
  }

  // slug ถ้าไม่กรอกให้สร้างแบบง่าย ๆ
  if ($slug === '') {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    $slug = trim($slug, '-');
  }

  if ($id) {
    $pdo->prepare("
      UPDATE products
      SET category_id=?, name=?, slug=?, price=?, stock=?, description=?, is_active=?
      WHERE id=?
    ")->execute([$category_id, $name, $slug, $price, $stock, $description, $is_active, $id]);
  } else {
    $pdo->prepare("
      INSERT INTO products (category_id, name, slug, price, stock, description, is_active, created_at)
      VALUES (?,?,?,?,?,?,?, NOW())
    ")->execute([$category_id, $name, $slug, $price, $stock, $description, $is_active]);
    $id = (int)$pdo->lastInsertId();
  }

  // ==== อัปโหลดรูป (input name="images[]" multiple) ====
  if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $dir = __DIR__ . '/../uploads/products';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    if (!is_dir($dir) || !is_writable($dir)) {
      set_flash('err', 'โฟลเดอร์ uploads/products ใช้งานไม่ได้ (permission)');
      redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
    }

    $count = count($_FILES['images']['name']);
    for ($i = 0; $i < $count; $i++) {
      $err = (int)($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE);
      if ($err === UPLOAD_ERR_NO_FILE) continue;

      if ($err !== UPLOAD_ERR_OK) {
        set_flash('err', 'อัปโหลดรูปผิดพลาด: ' . upload_error_text($err));
        redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
      }

      $tmp  = $_FILES['images']['tmp_name'][$i];
      $size = (int)($_FILES['images']['size'][$i] ?? 0);

      if ($size > 5 * 1024 * 1024) {
        set_flash('err', 'ไฟล์รูปใหญ่เกิน 5MB');
        redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
      }

      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $tmp);
      finfo_close($finfo);

      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      if (!isset($allowed[$mime])) {
        set_flash('err', 'อนุญาตเฉพาะ JPG/PNG/WEBP');
        redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
      }

      $ext  = $allowed[$mime];
      $namef = 'p' . (int)$id . '_' . date('Ymd_His') . '_' . $i . '.' . $ext;
      $fs   = $dir . '/' . $namef;
      $web  = 'uploads/products/' . $namef;

      if (!move_uploaded_file($tmp, $fs)) {
        set_flash('err', 'ย้ายไฟล์ไม่สำเร็จ');
        redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
      }

      // บันทึกลงตาราง product_images
      $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?,?,?)")
          ->execute([(int)$id, $web, $i]);
    }
  }

  set_flash('ok', 'บันทึกเรียบร้อย');
  redirect('/agriculture/admin/product_form.php?id=' . (int)$id);
}

?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ฟอร์มสินค้า</title>
  <style>
    body{font-family:Arial,sans-serif;margin:20px;background:#f7f7f7}
    .wrap{max-width:980px;margin:0 auto}
    .card{background:#fff;border:1px solid #ddd;border-radius:12px;padding:16px;margin-bottom:14px}
    label{display:block;margin-top:10px}
    input,select,textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:10px}
    button{margin-top:12px;padding:10px 14px;border:0;border-radius:10px;cursor:pointer}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    img{max-width:150px;border-radius:12px;border:1px solid #eee}
  </style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h2><?php echo $id ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า'; ?></h2>

    <?php $flash = get_flash(); if (!empty($flash)): ?>
      <div style="padding:10px;border-radius:10px;margin:10px 0;border:1px solid #ddd;">
        <?php echo htmlspecialchars($flash['msg']); ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="grid">
        <div>
          <label>หมวดหมู่</label>
          <select name="category_id">
            <?php foreach ($cats as $c): ?>
              <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$p['category_id']===(int)$c['id'])?'selected':''; ?>>
                <?php echo htmlspecialchars($c['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label>ชื่อสินค้า</label>
          <input name="name" value="<?php echo htmlspecialchars((string)$p['name']); ?>">

          <label>Slug</label>
          <input name="slug" value="<?php echo htmlspecialchars((string)$p['slug']); ?>">

          <label>ราคา</label>
          <input name="price" value="<?php echo htmlspecialchars((string)$p['price']); ?>">

          <label>สต็อก</label>
          <input name="stock" value="<?php echo htmlspecialchars((string)$p['stock']); ?>">

          <label>เปิดขาย</label>
          <select name="is_active">
            <option value="1" <?php echo ((int)$p['is_active']===1)?'selected':''; ?>>ใช่</option>
            <option value="0" <?php echo ((int)$p['is_active']===0)?'selected':''; ?>>ไม่</option>
          </select>
        </div>

        <div>
          <label>รายละเอียด</label>
          <textarea name="description" rows="12"><?php echo htmlspecialchars((string)$p['description']); ?></textarea>

          <label>อัปโหลดรูปสินค้า (หลายรูปได้)</label>
          <input type="file" name="images[]" multiple accept="image/*">
        </div>
      </div>

      <button type="submit" name="save">บันทึก</button>
    </form>
  </div>

  <?php if (!empty($imgs)): ?>
    <div class="card">
      <h3>รูปสินค้า</h3>
      <?php foreach ($imgs as $im): ?>
        <img src="<?php echo htmlspecialchars((string)$im['image_path']); ?>" alt="img">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
