<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

// --- DELETE LOGIC ---
if (isset($_POST['delete'])) {
  $id = (int)($_POST['id'] ?? 0);
  $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  // ใช้ JS Alert + Redirect เพื่อความสมูท
  echo "<script>alert('ลบสินค้าเรียบร้อยแล้ว'); window.location.href='products.php';</script>";
  exit;
}

// --- SEARCH LOGIC ---
$q = trim($_GET['q'] ?? '');
// Join categories เพื่อเอาชื่อหมวดหมู่มาแสดง
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON c.id = p.category_id"; // ใช้ LEFT JOIN กันพลาดกรณีหมวดถูกลบ

$params = [];
if ($q !== '') { 
    $sql .= " WHERE p.name LIKE ? OR p.slug LIKE ? "; 
    $params[] = "%$q%"; 
    $params[] = "%$q%"; 
}
$sql .= " ORDER BY p.created_at DESC LIMIT 800";

$st = $pdo->prepare($sql); 
$st->execute($params);
$products = $st->fetchAll();

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
    .content-inner { max-width: 1400px; width: 100%; margin: 0 auto; }

    /* Header & Tools */
    .page-header {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
        margin-bottom: 24px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        padding: 20px 30px; border-radius: 20px; backdrop-filter: blur(10px);
    }
    .tools-wrapper { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    
    .search-box { display: flex; gap: 8px; }
    .input {
        padding: 10px 14px; border-radius: 10px; min-width: 240px;
        background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15);
        color: #fff; outline: none;
    }
    .input:focus { border-color: #22c55e; }

    .btn {
        padding: 10px 18px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; font-size: 14px; transition: 0.2s;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn.primary { background: #22c55e; color: #052012; }
    .btn.sky { background: rgba(92, 200, 255, 0.2); color: #5cc8ff; border: 1px solid rgba(92, 200, 255, 0.3); }
    .btn.sky:hover { background: rgba(92, 200, 255, 0.3); }
    .btn.ghost { background: transparent; color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.2); }
    .btn.danger-icon { background: rgba(255, 77, 77, 0.15); color: #ff8585; padding: 8px; border-radius: 8px; }
    .btn.danger-icon:hover { background: rgba(255, 77, 77, 0.3); color: #fff; }
    .btn.edit-icon { background: rgba(92, 200, 255, 0.15); color: #5cc8ff; padding: 8px; border-radius: 8px; }
    .btn.edit-icon:hover { background: rgba(92, 200, 255, 0.3); color: #fff; }

    /* Table */
    .glass-panel {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        overflow-x: auto;
    }
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; min-width: 800px; }
    .custom-table th { text-align: left; padding: 10px 15px; color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 500; }
    .custom-table td { background: rgba(255,255,255,0.05); padding: 12px 15px; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; border-left: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; border-right: 1px solid rgba(255,255,255,0.05); text-align: right; }

    /* Product Specifics */
    .product-thumb {
        width: 50px; height: 50px; border-radius: 8px; object-fit: cover;
        background: #000; border: 1px solid rgba(255,255,255,0.1);
    }
    .stock-badge {
        display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;
    }
    .stock-ok { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
    .stock-low { background: rgba(255, 209, 102, 0.2); color: #ffd166; }
    .stock-out { background: rgba(255, 77, 77, 0.2); color: #ff8585; }

    @media (max-width: 768px) {
        .admin-full-wrapper { flex-direction: column; overflow: auto; height: auto; }
        .sidebar-pane { width: 100%; height: auto; }
        .page-header { flex-direction: column; align-items: stretch; }
        .tools-wrapper { flex-direction: column; align-items: stretch; }
        .search-box { width: 100%; }
        .input { width: 100%; min-width: auto; }
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
                    <h2 style="margin:0; font-size:24px;">รายการสินค้า (Products)</h2>
                    <span style="color:rgba(233,246,238,0.6); font-size:14px;">จัดการสินค้าทั้งหมดในร้านค้า</span>
                </div>
                
                <div class="tools-wrapper">
                    <form method="get" class="search-box">
                        <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อสินค้า / slug">
                        <button class="btn sky" type="submit">ค้นหา</button>
                    </form>
                    <a href="<?= h(url('/admin/product_form.php')) ?>" class="btn primary">
                        + เพิ่มสินค้า
                    </a>
                </div>
            </div>

            <div class="glass-panel">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:50px">ID</th>
                            <th style="width:80px">รูปภาพ</th>
                            <th>ข้อมูลสินค้า</th>
                            <th style="width:120px">ราคา</th>
                            <th style="width:100px">สต็อก</th>
                            <th style="width:100px; text-align:right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!$products): ?>
                            <tr><td colspan="6" style="text-align:center; padding:40px; color:#777;">ไม่พบสินค้าในระบบ</td></tr>
                        <?php endif; ?>

                        <?php foreach($products as $p): 
                            $cover = product_cover($pdo, (int)$p['id']); 
                        ?>
                        <tr>
                            <td>
                                <span style="font-weight:900; color:rgba(255,255,255,0.7);">#<?= (int)$p['id'] ?></span>
                            </td>
                            <td>
                                <?php if($cover): ?>
                                    <img src="<?= h(url('/uploads/products/' . $cover)) ?>" class="product-thumb" alt="img">
                                <?php else: ?>
                                    <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;color:#555;font-size:10px;">NO IMG</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:700; font-size:15px; color:#fff;"><?= h($p['name']) ?></div>
                                <div style="font-size:13px; color:var(--green); opacity:0.8; margin-top:2px;"><?= h($p['category_name'] ?? 'ไม่มีหมวด') ?></div>
                            </td>
                            <td>
                                <div style="font-weight:700; color:#fff;">฿<?= number_format((float)$p['price'], 2) ?></div>
                            </td>
                            <td>
                                <?php 
                                    $stk = (int)$p['stock'];
                                    $sClass = 'stock-ok';
                                    if($stk == 0) $sClass = 'stock-out';
                                    elseif($stk < 10) $sClass = 'stock-low';
                                ?>
                                <span class="stock-badge <?= $sClass ?>">
                                    <?= $stk ?> ชิ้น
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; justify-content:flex-end; gap:8px;">
                                    <a href="<?= h(url('/admin/product_form.php?id='.(int)$p['id'])) ?>" class="btn edit-icon" title="แก้ไข">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>

                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button class="btn danger-icon" name="delete" value="1" onclick="return confirm('ยืนยันที่จะลบสินค้า: <?= h($p['name']) ?> ?')" title="ลบสินค้า">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</div>

<div class="container">
<?php require __DIR__ . '/../includes/footer.php'; ?>