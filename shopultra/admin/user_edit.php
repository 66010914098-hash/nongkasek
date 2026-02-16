<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if(!$user){
    die("ไม่พบข้อมูลลูกค้า");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);

    $update = $pdo->prepare("UPDATE users SET full_name=?, phone=? WHERE id=?");
    $update->execute([$full_name, $phone, $id]);

    header("Location: users.php");
    exit;
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>แก้ไขลูกค้า</h2>

    <form method="post">
        <div style="max-width:600px;margin:40px auto;">

    <div style="
        background:rgba(255,255,255,0.04);
        border:1px solid rgba(255,255,255,0.1);
        border-radius:20px;
        padding:30px;
        box-shadow:0 10px 40px rgba(0,0,0,0.3);
        backdrop-filter:blur(10px);
    ">

        <h2 style="margin-top:0;margin-bottom:25px;">แก้ไขลูกค้า</h2>

        <form method="post" style="display:flex;flex-direction:column;gap:20px;">

            <div>
                <label style="font-size:13px;color:rgba(255,255,255,0.6);">ชื่อ</label>
                <input type="text" name="full_name"
                    value="<?= h($user['full_name']) ?>" required
                    style="
                        width:100%;
                        padding:12px;
                        border-radius:12px;
                        background:rgba(0,0,0,0.4);
                        border:1px solid rgba(255,255,255,0.15);
                        color:#fff;
                        margin-top:6px;
                    ">
            </div>

            <div>
                <label style="font-size:13px;color:rgba(255,255,255,0.6);">เบอร์โทร</label>
                <input type="text" name="phone"
                    value="<?= h($user['phone']) ?>"
                    style="
                        width:100%;
                        padding:12px;
                        border-radius:12px;
                        background:rgba(0,0,0,0.4);
                        border:1px solid rgba(255,255,255,0.15);
                        color:#fff;
                        margin-top:6px;
                    ">
            </div>

            <div style="display:flex;gap:15px;margin-top:10px;">
                <button type="submit"
                    style="
                        flex:1;
                        padding:12px;
                        border-radius:12px;
                        border:none;
                        background:#22c55e;
                        color:#052012;
                        font-weight:700;
                        cursor:pointer;
                    ">
                    บันทึก
                </button>

                <a href="<?= h(url('/admin/users.php')) ?>"
                    style="
                        flex:1;
                        text-align:center;
                        padding:12px;
                        border-radius:12px;
                        border:1px solid rgba(255,255,255,0.2);
                        color:#fff;
                        text-decoration:none;
                    ">
                    ยกเลิก
                </a>
            </div>

        </form>
    </div>
</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>
