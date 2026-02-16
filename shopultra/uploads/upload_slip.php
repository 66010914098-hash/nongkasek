<?php
// 1. เชื่อมต่อฐานข้อมูล (Database Connection)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shopultra"; // ใส่ชื่อ DB ของคุณ

$conn = new mysqli($servername, $username, $password, $dbname);

// เช็คการเชื่อมต่อ
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// 2. รับค่าที่ส่งมาจากหน้าบ้าน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id']; // รับ ID ของออเดอร์
    
    // ตรวจสอบว่ามีการอัปโหลดไฟล์มาไหม
    if (isset($_FILES['slip_image']) && $_FILES['slip_image']['error'] == 0) {
        
        $allowed = array('jpg', 'jpeg', 'png'); // นามสกุลที่อนุญาต
        $filename = $_FILES['slip_image']['name'];
        $filetype = $_FILES['slip_image']['type'];
        $filesize = $_FILES['slip_image']['size'];
    
        // ตรวจสอบนามสกุลไฟล์
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $allowed)) {
            echo json_encode(["status" => "error", "message" => "อนุญาตเฉพาะไฟล์รูปภาพเท่านั้น"]);
            exit();
        }

        // 3. ตั้งชื่อไฟล์ใหม่ (ใช้ Order ID + วันเวลา เพื่อไม่ให้ซ้ำ)
        $new_filename = "slip_" . $order_id . "_" . time() . "." . $ext;
        $upload_path = "uploads/slips/" . $new_filename;

        // 4. ย้ายไฟล์จาก Temp ไปยังโฟลเดอร์จริง
        if (move_uploaded_file($_FILES['slip_image']['tmp_name'], $upload_path)) {
            
            // 5. อัปเดตข้อมูลลง Database
            // อัปเดตทั้ง path รูป และเปลี่ยนสถานะเป็น 'รอตรวจสอบ'
            $sql = "UPDATE orders SET slip_path = '$new_filename', payment_status = 'pending_verification' WHERE id = '$order_id'";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(["status" => "success", "message" => "อัปโหลดสลิปเรียบร้อย"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
            }
            
        } else {
            echo json_encode(["status" => "error", "message" => "ไม่สามารถบันทึกไฟล์รูปภาพได้"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "กรุณาเลือกไฟล์รูปภาพ"]);
    }
}
$conn->close();
?>