<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

// --- Logic PHP เดิม (Search Users) ---
$q = trim($_GET['q'] ?? '');
$sql = "SELECT id,full_name,email,phone,created_at FROM users";
$params = [];

if ($q !== '') { 
    $sql .= " WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? "; 
    $params[]="%$q%"; 
    $params[]="%$q%"; 
    $params[]="%$q%"; // เพิ่มค้นหาเบอร์โทรให้ด้วย
}

$sql .= " ORDER BY created_at DESC LIMIT 1000";
$st = $pdo->prepare($sql); 
$st->execute($params);
$users = $st->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

</div>

<style>
    /* --- Main Layout CSS (ชุดเดิม) --- */
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
    .content-inner { max-width: 1200px; width: 100%; margin: 0 auto; }

    /* Header & Search */
    .page-header {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
        margin-bottom: 24px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
        padding: 20px 30px; border-radius: 20px; backdrop-filter: blur(10px);
    }
    .search-box { display: flex; gap: 10px; flex: 1; max-width: 400px; }
    .input {
        width: 100%; padding: 10px 14px; border-radius: 10px;
        background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.15);
        color: #fff; outline: none;
    }
    .input:focus { border-color: #22c55e; }
    .btn {
        padding: 10px 18px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; font-size: 14px;
    }
    .btn.sky { background: rgba(92, 200, 255, 0.2); color: #5cc8ff; border: 1px solid rgba(92, 200, 255, 0.3); }
    .btn.sky:hover { background: rgba(92, 200, 255, 0.3); }
    .btn.ghost { background: transparent; color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.2); }

    /* Table */
    .glass-panel {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .custom-table th { text-align: left; padding: 10px 15px; color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 500; }
    .custom-table td { background: rgba(255,255,255,0.05); padding: 15px; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; border-left: 1px solid rgba(255,255,255,0.05); }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; border-right: 1px solid rgba(255,255,255,0.05); }

    /* User Avatar Placeholder */
    .user-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.1);
        display: flex; align-items: center; justify-content: center; color: var(--green);
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    @media (max-width: 768px) {
        .admin-full-wrapper { flex-direction: column; overflow: auto; height: auto; }
        .sidebar-pane { width: 100%; height: auto; }
        .page-header { flex-direction: column; align-items: stretch; }
        .search-box { max-width: 100%; }
        /* Table Responsive Scroll */
        .glass-panel { overflow-x: auto; }
        .custom-table { min-width: 600px; }
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
            <a href="<?= h(url('/admin/categories.php')) ?>" class="sidebar-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                หมวดหมู่
            </a>
            
            <a href="<?= h(url('/admin/users.php')) ?>" class="sidebar-link active">
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
                    <h2 style="margin:0; font-size:24px;">รายชื่อสมาชิก (Users)</h2>
                    <span style="color:rgba(233,246,238,0.6); font-size:14px;">ดูข้อมูลลูกค้าและประวัติการสมัครสมาชิก</span>
                </div>
                
                <form method="get" class="search-box">
                    <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="ค้นหา: ชื่อ / อีเมล / เบอร์โทร">
                    <button class="btn sky" type="submit">ค้นหา</button>
                    <?php if($q !== ''): ?>
                        <a class="btn ghost" href="<?= h(url('/admin/users.php')) ?>">ล้าง</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="glass-panel">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:70px">ID</th>
                            <th>ข้อมูลส่วนตัว (ชื่อ / อีเมล)</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th>วันที่สมัคร</th>
                            <th style="width:220px">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!$users): ?>
                            <tr><td colspan="5" style="text-align:center; color:#777; padding:40px">ไม่พบข้อมูลลูกค้า</td></tr>
                        <?php endif; ?>

                        <?php foreach($users as $u): ?>
                        <tr>
                            <td>
                                <span style="font-weight:900; color:rgba(255,255,255,0.7);">#<?= (int)$u['id'] ?></span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="user-avatar">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div>
                                        <div style="font-weight:700; font-size:15px; color:#fff;"><?= h($u['full_name']) ?></div>
                                        <div style="font-size:13px; color:var(--green); opacity:0.9;"><?= h($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if(!empty($u['phone'])): ?>
                                    <span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:6px; font-size:13px;">
                                        📞 <?= h($u['phone']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="opacity:0.5">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:rgba(255,255,255,0.6); font-size:14px;">
                                <?= h($u['created_at']) ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">        
                                    <a href="<?= h(url('/admin/user_view.php?id=' . $u['id'])) ?>"
                                    class="btn ghost">
                                    ดู
                                    </a>
                                    <a href="<?= h(url('/admin/user_edit.php?id=' . $u['id'])) ?>"
                                    class="btn sky">
                                    แก้ไข
                                    </a>
                                    <a href="<?= h(url('/admin/user_delete.php?id=' . $u['id'])) ?>"
                                    class="btn"
                                    style="background:rgba(255,77,77,0.2); color:#ff4d4d;"
                                    onclick="return confirm('ลบลูกค้านี้ใช่หรือไม่?')">
                                     ลบ
                                    </a>
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