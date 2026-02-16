<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

// --- Logic PHP เดิม (จัดการ Create / Delete) ---
if (isset($_POST['create'])) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if ($name && $slug) {
        $check = $pdo->prepare("SELECT id FROM categories WHERE slug=?");
$check->execute([$slug]);
if ($check->fetch()) {
    echo "<script>alert('Slug นี้มีอยู่แล้ว');</script>";
} else {
    $pdo->prepare("INSERT INTO categories(name,slug) VALUES(?,?)")
        ->execute([$name,$slug]);
    echo "<script>alert('เพิ่มหมวดเรียบร้อย');</script>";
}
 
    }
    // redirect กลับมาที่หน้าเดิมเพื่อป้องกันการ resubmit
    echo "<script>window.location.href='categories.php';</script>";
    exit;
}

if (isset($_POST['delete'])) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    echo "<script>window.location.href='categories.php';</script>";
    exit;
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

</div>

<style>
    /* --- CSS หลัก (เหมือนหน้า Dashboard) --- */
    html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; font-family: sans-serif; }
    
    .admin-full-wrapper {
        display: flex;
        width: 100vw; height: 100vh;
        position: fixed; top: 0; left: 0;
        background: radial-gradient(circle at 10% 10%, rgba(34,197,94,0.05), transparent 40%),
                    linear-gradient(180deg, #07140c, #0b1f12);
        z-index: 999;
        color: #e9f6ee;
    }

    /* Sidebar */
    .sidebar-pane {
        width: 280px;
        background: rgba(7, 20, 12, 0.7);
        backdrop-filter: blur(20px);
        border-right: 1px solid rgba(255,255,255,0.1);
        padding: 24px;
        display: flex; flex-direction: column; flex-shrink: 0;
        height: 100%;
    }
    .sidebar-header { margin-bottom: 30px; }
    .sidebar-menu { display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .sidebar-link {
        display: flex; align-items: center; gap: 14px;
        padding: 12px 16px; border-radius: 12px;
        color: rgba(233,246,238,0.7); text-decoration: none;
        transition: all 0.2s; font-weight: 500; font-size: 15px;
    }
    .sidebar-link:hover { background: rgba(255,255,255,0.05); color: #fff; transform: translateX(4px); }
    .sidebar-link.active {
        background: linear-gradient(90deg, #22c55e, #39d98a);
        color: #052012; font-weight: 700;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
    }
    .logout-wrapper { margin-top: auto; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
    .sidebar-link.logout { color: #ff4d4d; background: rgba(255, 77, 77, 0.05); border: 1px solid rgba(255, 77, 77, 0.15); justify-content: center; }
    .sidebar-link.logout:hover { background: rgba(255, 77, 77, 0.15); }

    /* Content Area */
    .content-pane {
        flex: 1; height: 100%; padding: 40px;
        display: flex; flex-direction: column;
        overflow-y: auto;
    }
    .content-inner { max-width: 1200px; width: 100%; margin: 0 auto; }

    /* Page Title Section */
    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 24px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 20px 30px; border-radius: 20px;
        backdrop-filter: blur(10px);
    }
    .page-title h2 { margin: 0; font-size: 24px; }
    .page-title span { color: rgba(233,246,238,0.6); font-size: 14px; }

    /* Form & Table Styles */
    .glass-panel {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 30px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    /* Inputs & Buttons */
    .form-row { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; }
    .form-group { flex: 1; min-width: 200px; }
    .form-group label { display: block; font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 6px; }
    
    .input {
        width: 100%; padding: 12px; border-radius: 12px;
        background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15);
        color: #fff; outline: none; transition: 0.2s;
    }
    .input:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.15); }

    .btn {
        padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer;
        font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: transform 0.1s;
    }
    .btn:active { transform: scale(0.96); }
    .btn.primary { background: #22c55e; color: #052012; }
    .btn.primary:hover { background: #1eb855; }
    .btn.danger { background: rgba(255, 77, 77, 0.2); color: #ff4d4d; border: 1px solid rgba(255,77,77,0.3); padding: 6px 14px; font-size: 13px; }
    .btn.danger:hover { background: rgba(255, 77, 77, 0.3); }

    /* Custom Table */
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .custom-table th { text-align: left; padding: 10px 15px; color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 500; }
    .custom-table td { background: rgba(255,255,255,0.05); padding: 15px; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; border-left: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; border-right: 1px solid rgba(255,255,255,0.05); text-align: right; }
    .badge-id { display: inline-block; padding: 2px 8px; background: rgba(255,255,255,0.1); border-radius: 6px; font-size: 12px; color: rgba(255,255,255,0.7); }

    @media (max-width: 768px) {
        .admin-full-wrapper { flex-direction: column; overflow: auto; height: auto; }
        .sidebar-pane { width: 100%; height: auto; }
        .content-pane { padding: 20px; height: auto; }
        .form-row { flex-direction: column; align-items: stretch; }
        .btn { width: 100%; justify-content: center; }
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
            <a href="<?= h(url('/admin/products.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                สินค้า
            </a>
            
            <a href="<?= h(url('/admin/categories.php')) ?>" class="sidebar-link active">
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
                    <h2>หมวดสินค้า (Categories)</h2>
                    <span>จัดการ เพิ่ม ลบ หมวดหมู่สินค้าในระบบ</span>
                </div>
            </div>

            <div class="glass-panel">
                
                <form method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label>ชื่อหมวดหมู่ (Name)</label>
                            <input class="input" type="text" name="name" placeholder="เช่น ปุ๋ยเคมี, ปุ๋ยอินทรีย์" required>
                        </div>
                        <div class="form-group">
                            <label>Slug (URL ภาษาอังกฤษ)</label>
                            <input class="input" type="text" name="slug" placeholder="เช่น chemical, organic" required>
                        </div>
                        <button class="btn primary" name="create" value="1" style="height:42px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            เพิ่มหมวด
                        </button>
                    </div>
                </form>

                <div style="border-top:1px solid rgba(255,255,255,0.1); margin: 20px 0;"></div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>ชื่อหมวด</th>
                            <th>Slug</th>
                            <th width="100" style="text-align:right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(count($cats) > 0): ?>
                        <?php foreach($cats as $c): ?>
                        <tr>
                            <td><span class="badge-id">#<?= (int)$c['id'] ?></span></td>
                            <td style="font-weight:600; font-size:15px;"><?= h($c['name']) ?></td>
                            <td style="color:var(--green); font-family:monospace;"><?= h($c['slug']) ?></td>
                            <td>
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn danger" name="delete" value="1" onclick="return confirm('ต้องการลบหมวด <?= h($c['name']) ?> ใช่หรือไม่? หากลบแล้วสินค้าในหมวดนี้อาจได้รับผลกระทบ')">
                                        ลบ
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:30px; color:rgba(255,255,255,0.3);">
                                ยังไม่มีหมวดหมู่สินค้าในระบบ
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

            </div>

        </div>
    </main>
</div>

<div class="container">
<?php require __DIR__ . '/../includes/footer.php'; ?>