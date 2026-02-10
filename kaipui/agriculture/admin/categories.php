<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

if (isset($_POST['create'])) {
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  if ($name && $slug) {
    $pdo->prepare("INSERT INTO categories(name,slug) VALUES(?,?)")->execute([$name,$slug]);
    set_flash('ok','เพิ่มหมวดแล้ว');
  }
  redirect('/admin/categories.php');
}

if (isset($_POST['delete'])) {
  $id = (int)($_POST['id'] ?? 0);
  $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
  set_flash('ok','ลบหมวดแล้ว');
  redirect('/admin/categories.php');
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="card panel">
  <div class="section-title">
    <div><div class="h2">หมวดสินค้า</div><div class="small">เพิ่ม/ลบหมวด</div></div>
    <a class="btn" href="<?= h(url('/admin/index.php')) ?>">กลับ</a>
  </div>

  <div style="margin-top:12px"></div>
  <form method="post" class="row wrap" style="align-items:flex-end">
    <div style="flex:1;min-width:220px">
      <label class="small">ชื่อหมวด</label>
      <input class="input" name="name" required>
    </div>
    <div style="flex:1;min-width:220px">
      <label class="small">slug</label>
      <input class="input" name="slug" required>
    </div>
    <button class="btn primary" name="create" value="1">เพิ่มหมวด</button>
  </form>

  <div style="margin-top:14px"></div>
  <table class="table">
    <thead><tr><th>ID</th><th>ชื่อ</th><th>Slug</th><th></th></tr></thead>
    <tbody>
    <?php foreach($cats as $c): ?>
      <tr>
        <td style="font-weight:950"><?= (int)$c['id'] ?></td>
        <td><?= h($c['name']) ?></td>
        <td><?= h($c['slug']) ?></td>
        <td style="width:110px">
          <form method="post">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn danger" name="delete" value="1" onclick="return confirm('ลบหมวดนี้?')">ลบ</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
