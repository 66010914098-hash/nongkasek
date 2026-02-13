<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id,full_name,email,phone,created_at FROM users";
$params = [];
if ($q !== '') { $sql .= " WHERE full_name LIKE ? OR email LIKE ? "; $params[]="%$q%"; $params[]="%$q%"; }
$sql .= " ORDER BY created_at DESC LIMIT 1000";
$st = $pdo->prepare($sql); $st->execute($params);
$users = $st->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="card panel">
  <div class="section-title">
    <div><div class="h2">ลูกค้า</div><div class="small">ค้นหา + รายการผู้ใช้</div></div>
    <a class="btn" href="<?= h(url('/admin/index.php')) ?>">กลับ</a>
  </div>

  <div style="margin-top:12px"></div>
  <form class="row wrap" method="get">
    <input class="input" style="max-width:360px" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อ/อีเมล">
    <button class="btn sky">ค้นหา</button>
    <a class="btn" href="<?= h(url('/admin/users.php')) ?>">ล้าง</a>
  </form>

  <div style="margin-top:12px"></div>
  <table class="table">
    <thead><tr><th>ID</th><th>ชื่อ</th><th>อีเมล</th><th>เบอร์</th><th>สมัครเมื่อ</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
      <tr>
        <td style="font-weight:950"><?= (int)$u['id'] ?></td>
        <td><?= h($u['full_name']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= h($u['phone'] ?? '-') ?></td>
        <td><?= h($u['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
