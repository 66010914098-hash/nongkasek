CREATE DATABASE IF NOT EXISTS fertilizer_shop
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fertilizer_shop;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  description TEXT,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL DEFAULT 'promptpay',
  payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  shipping_status VARCHAR(30) NOT NULL DEFAULT 'processing',
  slip_path VARCHAR(255),
  shipping_address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(200) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  qty INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO categories(name, slug) VALUES
('ปุ๋ยเคมี', 'chemical-fertilizer'),
('ปุ๋ยอินทรีย์', 'organic-fertilizer'),
('สารกำจัดศัตรูพืช', 'pesticide'),
('เมล็ดพันธุ์', 'seeds');

INSERT INTO products(category_id, name, slug, price, stock, description) VALUES
(1,'ปุ๋ย 16-16-16 (50kg)','fert-16-16-16', 980, 50, 'สูตรยอดนิยม ใช้ได้กับพืชทั่วไป เร่งโต แตกกอ แข็งแรง'),
(1,'ปุ๋ย 46-0-0 (50kg)','fert-46-0-0', 890, 40, 'ไนโตรเจนสูง ช่วยเร่งใบ เร่งต้น เหมาะช่วงบำรุงต้น'),
(2,'ปุ๋ยคอกอัดเม็ด (25kg)','manure-pellet', 220, 80, 'ปรับปรุงดิน เพิ่มอินทรีย์วัตถุ ช่วยรากเดินดี'),
(2,'ปุ๋ยหมักชีวภาพ (10kg)','bio-compost', 180, 100, 'เพิ่มจุลินทรีย์ในดิน กลิ่นไม่แรง ใช้ง่าย'),
(3,'สารกำจัดแมลง สูตรเข้มข้น','insecticide-strong', 150, 120, 'ใช้ตามอัตราส่วนที่แนะนำ ป้องกันเพลี้ยและหนอน'),
(4,'เมล็ดพันธุ์ผักสวนครัวรวม','mixed-veg-seeds', 39, 200, 'เหมาะสำหรับปลูกในบ้าน/แปลงเล็ก โตไว เก็บกินได้');
