
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Default values
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

// --- SAVE DATA ---
if (isset($_POST['save'])) {
  $category_id = (int)($_POST['category_id'] ?? 1);
  $name        = trim($_POST['name'] ?? '');
  $slug        = trim($_POST['slug'] ?? '');
  $price       = (float)($_POST['price'] ?? 0);
  $stock       = (int)($_POST['stock'] ?? 0);
  $desc        = trim($_POST['description'] ?? '');
  $active      = isset($_POST['is_active']) ? 1 : 0;

  $check = $pdo->prepare("SELECT id FROM products WHERE slug=? AND id!=?");
$check->execute([$slug, $id]);
if ($check->fetch()) {
    die("Slug นี้ถูกใช้แล้ว กรุณาเปลี่ยนใหม่");
}


  if ($id) {
    // Update
    $pdo->prepare("UPDATE products SET category_id=?, name=?, slug=?, price=?, stock=?, description=?, is_active=? WHERE id=?")
        ->execute([$category_id, $name, $slug, $price, $stock, $desc, $active, $id]);
  } else {
    // Insert
    $pdo->prepare("INSERT INTO products(category_id,name,slug,price,stock,description,is_active) VALUES(?,?,?,?,?,?,?)")
        ->execute([$category_id, $name, $slug, $price, $stock, $desc, $active]);
    $id = (int)$pdo->lastInsertId();
  }

  // Handle Images
  if (!empty($_FILES['images']['name'][0])) {
    // สมมติว่า function ensure_dir และ safe_upload มีอยู่จริงตามโค้ดเดิม
    if(function_exists('ensure_dir')) ensure_dir(PRODUCT_IMG_DIR);
    
    $count = count($_FILES['images']['name']);
    $nextSort = (int)($_POST['next_sort'] ?? 0);
    
    for ($i=0; $i<$count; $i++) {
      $file = [
        'name'     => $_FILES['images']['name'][$i],
        'type'     => $_FILES['images']['type'][$i],
        'tmp_name' => $_FILES['images']['tmp_name'][$i],
        'error'    => $_FILES['images']['error'][$i],
        'size'     => $_FILES['images']['size'][$i],
      ];
      if (!empty($file['name'])) {
        // ตรวจสอบ function ก่อนเรียกใช้
        if(function_exists('safe_upload')) {
            $webPath = safe_upload($file, ['jpg','jpeg','png','webp'], PRODUCT_IMG_DIR, 'p'.$id.'_'.uniqid());
            $pdo->prepare("INSERT INTO product_images(product_id,image_path,sort_order) VALUES(?,?,?)")
                ->execute([$id, $webPath, $nextSort]);
            $nextSort++;
        }
      }
    }
  }

  echo "<script>alert('บันทึกข้อมูลสินค้าเรียบร้อยแล้ว'); window.location.href='product_form.php?id=$id';</script>";
  exit;
}

// --- DELETE IMAGE ---
if (isset($_POST['delete_img'])) {
  $imgId = (int)($_POST['img_id'] ?? 0);
  $pdo->prepare("DELETE FROM product_images WHERE id=?")->execute([$imgId]);
  echo "<script>window.location.href='product_form.php?id=$id';</script>";
  exit;
}

require __DIR__ . '/../includes/header.php';
?>

</div>

<style>
    /* --- Main Layout CSS --- */
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; font-family: sans-serif; }
    
    .admin-full-wrapper {
        display: flex; width: 100vw; height: 100vh;
        position: fixed; top: 0; left: 0;
        background: radial-gradient(circle at 10% 10%, rgba(34,197,94,0.05), transparent 40%),
                    linear-gradient(180deg, #07140c, #0b1f12);
        z-index: 999; color: #e9f6ee;
    }

    /* Sidebar */
    .sidebar-pane {
        width: 280px; background: rgba(7, 20, 12, 0.7); backdrop-filter: blur(20px);
        border-right: 1px solid rgba(255,255,255,0.1); padding: 24px;
        display: flex; flex-direction: column; flex-shrink: 0; height: 100%;
        overflow-y: auto;
    }
    .sidebar-header { margin-bottom: 30px; }
    .sidebar-menu { display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .sidebar-link {
        display: flex; align-items: center; gap: 14px; padding: 12px 16px; border-radius: 12px;
        color: rgba(233,246,238,0.7); text-decoration: none; transition: 0.2s; font-weight: 500; font-size: 15px;
    }
    .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(4px); }
    .sidebar-link.active {
        background: linear-gradient(90deg, #22c55e, #39d98a); color: #052012; font-weight: 700;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .logout-wrapper { margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
    .sidebar-link.logout { color: #ff4d4d; background: rgba(255, 77, 77, 0.05); border: 1px solid rgba(255, 77, 77, 0.15); justify-content: center; }
    .sidebar-link.logout:hover { background: rgba(255, 77, 77, 0.15); }

    /* Content */
    .content-pane { flex: 1; height: 100%; padding: 30px; display: flex; flex-direction: column; overflow-y: auto; }
    .content-inner { max-width: 1000px; width: 100%; margin: 0 auto; padding-bottom: 40px; }

    /* Page Header */
    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 24px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        padding: 20px 30px; border-radius: 20px; backdrop-filter: blur(10px);
    }

    /* Form Styles */
    .glass-panel {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-full { grid-column: span 2; }
    
    .form-group label { display: block; font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 8px; }
    .input {
        width: 100%; padding: 12px; border-radius: 12px;
        background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15);
        color: #fff; outline: none; box-sizing: border-box; transition: 0.2s;
    }
    .input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.15); }
    textarea.input { resize: vertical; min-height: 100px; }

    /* Switch Checkbox */
    .switch-label { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .switch-input { width: 20px; height: 20px; accent-color: #22c55e; }

    /* Buttons */
    .btn {
        padding: 10px 20px; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;
    }
    .btn.primary { background: #22c55e; color: #052012; width: 100%; height: 48px; font-size: 16px; margin-top: 10px; }
    .btn.primary:hover { background: #1eb855; transform: translateY(-2px); }
    .btn.ghost { background: transparent; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); }
    .btn.ghost:hover { background: rgba(255,255,255,0.1); color: #fff; }
    
    /* Image Grid */
    .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; margin-top: 15px; }
    .img-card {
        position: relative; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);
        aspect-ratio: 1/1; background: #000;
    }
    .img-card img { width: 100%; height: 100%; object-fit: cover; }
    .img-del-btn {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(255, 77, 77, 0.9); color: white; border: none;
        padding: 8px; cursor: pointer; font-size: 12px; text-align: center;
        transition: 0.2s; opacity: 0;
    }
    .img-card:hover .img-del-btn { opacity: 1; }

    /* File Input Styling */
    .file-upload-box {
        border: 2px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 20px;
        text-align: center; background: rgba(255,255,255,0.02); transition: 0.2s;
    }
    .file-upload-box:hover { border-color: #22c55e; background: rgba(34,197,94,0.05); }

    @media (max-width: 768px) {
        .admin-full-wrapper { flex-direction: column; overflow: auto; height: auto; }
        .sidebar-pane { width: 100%; height: auto; }
        .form-grid { grid-template-columns: 1fr; }
        .form-full { grid-column: span 1; }
    }
</style>

<div class="admin-full-wrapper">
    
    <aside class="sidebar-pane">
        <div class="sidebar-header">
            <div style="font-size:22px; font-weight:800; color:#22c55e;">Admin System</div>
            <div style="font-size:13px; color:rgba(255,255,255,0.5);">จัดการระบบร้านค้า 4 สหายขายปุ๋ย</div>
        </div>

        <nav class="sidebar-menu">
            <a href="<?= h(url('/admin/index.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                หน้าหลัก
            </a>
            <a href="<?= h(url('/admin/orders.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                ออเดอร์
            </a>
            <a href="<?= h(url('/admin/products.php')) ?>" class="sidebar-link active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                สินค้า
            </a>
            <a href="<?= h(url('/admin/categories.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                หมวดหมู่
            </a>
            <a href="<?= h(url('/admin/users.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                ลูกค้า
            </a>

            <div class="logout-wrapper">
                <a href="<?= h(url('/index.php')) ?>" class="sidebar-link" title="ไปหน้าบ้าน" onclick="window.open(this.href); return false;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    กลับหน้าร้าน
                </a>
                <hr>
                <a href="<?= h(url('/admin/logout.php')) ?>" class="sidebar-link logout" onclick="return confirm('ยืนยันการออกจากระบบ?')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    ออกจากระบบ
                </a>
            </div>
        </nav>
    </aside>

    <main class="content-pane">
        <div class="content-inner">
            
            <div class="page-header">
                <div class="page-title">
                    <h2 style="margin:0; font-size:24px;"><?= $id ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' ?></h2>
                    <span style="color:rgba(233,246,238,0.6); font-size:14px;">กรอกข้อมูลสินค้าและอัปโหลดรูปภาพ</span>
                </div>
                <a href="<?= h(url('/admin/products.php')) ?>" class="btn ghost">
                    ← กลับหน้ารายการ
                </a>
            </div>

            <div class="glass-panel">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label>ชื่อสินค้า</label>
                            <input class="input" name="name" value="<?= h((string)$p['name']) ?>" required placeholder="ชื่อสินค้า...">
                        </div>
                        <div class="form-group">
                            <label>หมวดหมู่</label>
                            <select class="input" name="category_id">
                                <?php foreach($cats as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>" <?= (int)$p['category_id']===(int)$c['id']?'selected':'' ?>>
                                        <?= h($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Slug (URL ภาษาอังกฤษ)</label>
                            <input class="input" name="slug" value="<?= h((string)$p['slug']) ?>" required placeholder="product-slug-url">
                        </div>
                        <div class="form-group">
                            <label>สถานะการขาย</label>
                            <div style="margin-top:12px">
                                <label class="switch-label">
                                    <input type="checkbox" class="switch-input" name="is_active" <?= (int)$p['is_active']===1?'checked':'' ?>>
                                    <span style="color:#fff; font-weight:500;">เปิดขายสินค้านี้</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>ราคา (บาท)</label>
                            <input class="input" type="number" step="0.01" name="price" value="<?= h((string)$p['price']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>จำนวนสต็อก (ชิ้น)</label>
                            <input class="input" type="number" name="stock" value="<?= h((string)$p['stock']) ?>" required>
                        </div>

                        <div class="form-group form-full">
                            <label>รายละเอียดสินค้า</label>
                            <textarea class="input" name="description" placeholder="รายละเอียดสินค้า..."><?= h((string)$p['description']) ?></textarea>
                        </div>

                        <div class="form-group form-full">
                            <label>อัปโหลดรูปภาพสินค้า (เลือกได้หลายรูป)</label>
                            <div class="file-upload-box">
                                <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple id="fileInput" style="display:none;" onchange="document.getElementById('fileNameDisplay').innerText = this.files.length + ' ไฟล์ถูกเลือก'">
                                <label for="fileInput" class="btn ghost" style="cursor:pointer; display:inline-block; margin-bottom:10px;">
                                    เลือกไฟล์รูปภาพ
                                </label>
                                <div id="fileNameDisplay" style="font-size:13px; color:rgba(255,255,255,0.5);">ยังไม่ได้เลือกไฟล์</div>
                            </div>
                            <input type="hidden" name="next_sort" value="<?= (int)count($imgs) ?>">
                        </div>
                        
                        <div class="form-group form-full">
                            <button class="btn primary" name="save" value="1">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                บันทึกข้อมูลสินค้า
                            </button>
                        </div>

                    </div>
                </form>

                <?php if($id && !empty($imgs)): ?>
                    <div style="margin-top:40px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;">
                        <label style="color:var(--green); font-weight:700;">รูปภาพที่มีอยู่ (<?= count($imgs) ?> รูป)</label>
                        <div class="img-grid">
                            <?php foreach($imgs as $im): ?>
                                <div class="img-card">
                                    <img src="<?= h(url('/' . $im['image_path'])) ?>" alt="">
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="img_id" value="<?= (int)$im['id'] ?>">
                                        <button class="img-del-btn" name="delete_img" value="1" onclick="return confirm('ต้องการลบรูปนี้หรือไม่?')">
                                            ลบรูป
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </main>
</div>

<div class="container">
<?php require __DIR__ . '/../includes/footer.php'; ?>