<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

// ดึงข้อมูลสถิติ
$stat_products = (int)$pdo->query("SELECT COUNT(*) AS c FROM products")->fetch()['c'];
$stat_orders   = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders")->fetch()['c'];
$stat_users    = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$stat_paid     = (int)$pdo->query("SELECT COUNT(*) AS c FROM orders WHERE payment_status='paid'")->fetch()['c'];

require __DIR__ . '/../includes/header.php';
?>

</div> 

<style>
    /* 1. ล็อคหน้าจอ Browser ไม่ให้เลื่อน */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden; /* คำสั่งสำคัญ: ห้าม Scroll ทั้งหน้า */
    }

    /* Layout หลัก เต็มจอ 100% */
    .admin-full-wrapper {
        display: flex;
        width: 100vw;
        height: 100vh; /* สูงเต็มจอพอดีเป๊ะ */
        position: fixed; /* ตรึงตำแหน่ง */
        top: 0;
        left: 0;
        background: radial-gradient(circle at 10% 10%, rgba(34,197,94,0.05), transparent 40%),
                    linear-gradient(180deg, var(--bg0), var(--bg1));
        z-index: 999; /* ให้ทับ Header/Navbar เดิมถ้ามีหลุดมา */
    }

    /* Sidebar ด้านซ้าย */
    .sidebar-pane {
        width: 280px;
        background: rgba(7, 20, 12, 0.7); /* เพิ่มความเข้มพื้นหลังอีกนิด */
        backdrop-filter: blur(20px);
        border-right: 1px solid var(--line);
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        height: 100%;
        overflow-y: auto; /* ถ้าเมนูเยอะเกินจอ ให้เลื่อนเฉพาะเมนู */
    }

    .sidebar-header { margin-bottom: 30px; }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 6px; /* ลดช่องว่างนิดหน่อยเพื่อให้พอดีจอ */
        flex: 1;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        color: var(--muted);
        text-decoration: none;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 15px;
    }
    .sidebar-link:hover {
        background: rgba(255,255,255,0.05);
        color: var(--text);
        transform: translateX(4px);
    }
    .sidebar-link.active {
        background: linear-gradient(90deg, var(--leaf), var(--green));
        color: #052012;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        font-weight: 700;
    }

    /* ปุ่มออกจากระบบ ดันลงล่างสุด */
    .logout-wrapper {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid var(--line);
    }
    .sidebar-link.logout {
        color: var(--danger);
        background: rgba(255, 77, 77, 0.05);
        border: 1px solid rgba(255, 77, 77, 0.15);
        justify-content: center;
    }
    .sidebar-link.logout:hover {
        background: rgba(255, 77, 77, 0.15);
    }

    /* พื้นที่เนื้อหาขวา */
    .content-pane {
        flex: 1;
        height: 100%;
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center; /* จัดเนื้อหาให้อยู่กึ่งกลางหน้าจอแนวตั้ง */
        overflow-y: auto; /* ถ้าหน้าจอเตี้ยมากๆ เนื้อหายังเลื่อนได้ */
    }
    
    .content-inner {
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
    }

    /* Cards */
    .welcome-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 25px 35px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--shadow);
    }
    .welcome-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0;
        width: 6px; background: var(--leaf);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* บังคับ 4 คอลัมน์แถวเดียวเพื่อให้ประหยัดที่ */
        gap: 20px;
    }

    .stat-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 20px;
        padding: 24px 20px;
        text-align: center;
        transition: transform 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: inherit;
    }
    .stat-card:hover { 
        transform: translateY(-5px); 
        background: var(--card2);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .icon-box {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 12px;
        font-size: 24px;
    }
    
    .bg-blue { background: rgba(92, 200, 255, 0.15); color: var(--sky); }
    .bg-green { background: rgba(34, 197, 94, 0.15); color: var(--green); }
    .bg-orange { background: rgba(255, 209, 102, 0.15); color: var(--sun); }
    .bg-soil { background: rgba(167, 123, 79, 0.15); color: var(--soil); }

    .stat-num { font-size: 32px; font-weight: 800; line-height: 1.1; }
    .stat-lbl { color: var(--muted); font-size: 13px; margin-top: 4px; }
    
    /* ซ่อน Footer ด้านล่าง (ถ้าไม่จำเป็น) หรือปรับให้เล็กลง */
    .inline-footer {
        text-align: center;
        font-size: 11px;
        color: var(--muted);
        opacity: 0.5;
        margin-top: 30px;
    }

    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .admin-full-wrapper { flex-direction: column; position: relative; height: auto; overflow: auto; }
        html, body { overflow: auto; height: auto; }
        .sidebar-pane { width: 100%; height: auto; overflow: visible; }
        .sidebar-menu { flex-direction: row; overflow-x: auto; padding-bottom: 5px; }
        .logout-wrapper { margin-top: 0; border-top: 0; margin-left: auto; }
        .content-pane { height: auto; justify-content: flex-start; }
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-full-wrapper">
    
    <aside class="sidebar-pane">
        <div class="sidebar-header">
            <div style="font-size:22px; font-weight:800; color:var(--green);">
                Admin System
            </div>
            <div style="font-size:13px; color:var(--muted);">
                จัดการระบบร้านค้า 4 สหายขายปุ๋ย
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="<?= h(url('/admin/index.php')) ?>" class="sidebar-link active">
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
            <div class="welcome-card">
                <div>
                    <h1 class="h2" style="margin:0 0 6px 0; font-size:24px; color:var(--text);">
                        สวัสดีครับ, คุณ <?= h($_SESSION['admin_username'] ?? 'Admin') ?> 👋
                    </h1>
                    <div style="color:var(--muted); font-size:14px;">
                        ระบบพร้อมใช้งานแล้ว วันนี้มีอะไรให้จัดการบ้าง?
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                
                <a href="<?= h(url('/admin/orders.php')) ?>" class="stat-card">
                    <div class="icon-box bg-blue">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </div>
                    <div class="stat-num"><?= $stat_orders ?></div>
                    <div class="stat-lbl">ออเดอร์ทั้งหมด</div>
                </a>

                <a href="<?= h(url('/admin/products.php')) ?>" class="stat-card">
                    <div class="icon-box bg-green">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    </div>
                    <div class="stat-num"><?= $stat_products ?></div>
                    <div class="stat-lbl">สินค้าในระบบ</div>
                </a>

                <a href="<?= h(url('/admin/users.php')) ?>" class="stat-card">
                    <div class="icon-box bg-orange">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="stat-num"><?= $stat_users ?></div>
                    <div class="stat-lbl">สมาชิกทั้งหมด</div>
                </a>

                <div class="stat-card">
                    <div class="icon-box bg-soil">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    </div>
                    <div class="stat-num"><?= $stat_paid ?></div>
                    <div class="stat-lbl">ชำระแล้ว</div>
                </div>

            </div>

            <div class="inline-footer">
                &copy; <?= date('Y') ?> 4 Sahai Fertilizer Shop. All rights reserved.
            </div>
        </div>
    </main>
</div>