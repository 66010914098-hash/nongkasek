Fertilizer Shop ULTRA (PHP + MySQL + CSS + JS)

✅ แก้ปัญหา 404 /products.php
- ถ้าวางโปรเจกต์ในโฟลเดอร์ย่อย (เช่น /fertilizer-shop-ultra)
  ต้องเข้าแบบนี้:
  http://localhost/fertilizer-shop-ultra/
  แล้วลิงก์ภายในจะทำงานเอง (BASE_URL Auto)

1) Import DB:
   - phpMyAdmin -> สร้าง DB ชื่อ fertilizer_shop
   - Import: sql/install.sql

2) ตั้งค่า DB:
   - includes/config.php (DB_HOST, DB_USER, DB_PASS)

3) วางไฟล์ใน htdocs:
   - วางโฟลเดอร์ fertilizer-shop-ultra ลงใน htdocs

4) สร้าง Admin:
   - /admin/setup_admin.php
   - ค่าเริ่มต้น admin / admin123
   - แนะนำลบไฟล์ setup_admin.php หลังสร้างแล้ว
