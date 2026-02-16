<?php
// index.php (ทำเป็นหน้า Home แบบเทพ)
// ถ้าจะดึงสินค้าจริง: include connect แล้ว SELECT แล้ว loop แทนตัวอย่างด้านล่างได้
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nongkasek | ร้านค้า</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- AOS (Animate on Scroll) -->
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <!-- Swiper (Slider) -->
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#0b1220;
      --bg2:#111b33;
      --card:#0f1a33cc;
      --line:#ffffff1a;
      --text:#eaf0ff;
      --muted:#b7c3e0;
      --brand:#7c5cff;
      --brand2:#22c55e;
    }
    body{
      font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif;
      color: var(--text);
      background: radial-gradient(1200px 700px at 20% 10%, #2a1b67 0%, transparent 60%),
                  radial-gradient(900px 600px at 80% 20%, #114a3a 0%, transparent 55%),
                  linear-gradient(180deg, var(--bg1), var(--bg2));
      overflow-x:hidden;
    }
    .glass{
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 18px;
      backdrop-filter: blur(10px);
      box-shadow: 0 18px 60px rgba(0,0,0,.35);
    }
    .nav-blur{
      background: rgba(10,16,30,.62);
      border-bottom:1px solid var(--line);
      backdrop-filter: blur(10px);
    }
    .hero{
      padding: 110px 0 60px;
      position:relative;
    }
    .hero .badge{
      background: rgba(124,92,255,.18);
      border:1px solid rgba(124,92,255,.35);
      color: var(--text);
    }
    .hero-title{
      font-weight:800;
      letter-spacing: -0.02em;
      line-height:1.05;
      font-size: clamp(2.2rem, 4.2vw, 3.8rem);
    }
    .hero-sub{
      color: var(--muted);
      font-size: 1.05rem;
    }
    .btn-brand{
      background: linear-gradient(135deg, var(--brand), #3b82f6);
      border:0;
      color:white;
      font-weight:700;
      box-shadow: 0 14px 40px rgba(124,92,255,.30);
    }
    .btn-brand:hover{ filter: brightness(1.05); }
    .btn-outline-glow{
      border:1px solid rgba(255,255,255,.22);
      color: var(--text);
      background: rgba(255,255,255,.04);
    }

    .kpi .bi{ font-size: 1.3rem; }
    .kpi small{ color: var(--muted); }

    .section-title{
      font-weight: 800;
      letter-spacing:-0.01em;
    }
    .section-sub{ color: var(--muted); }

    .product-card img{
      width:100%;
      height: 190px;
      object-fit: cover;
      border-radius: 14px;
      border:1px solid var(--line);
    }
    .product-card .price{
      font-weight:800;
    }
    .chip{
      display:inline-flex;
      gap:.4rem;
      align-items:center;
      padding:.28rem .6rem;
      border-radius: 999px;
      background: rgba(34,197,94,.14);
      border:1px solid rgba(34,197,94,.28);
      color: #c8ffe0;
      font-size: .85rem;
    }

    /* Swiper */
    .swiper{
      padding-bottom: 34px;
    }
    .swiper-pagination-bullet{
      opacity:.35;
    }
    .swiper-pagination-bullet-active{
      opacity:1;
    }

    /* Back to top */
    #toTop{
      position: fixed;
      right: 18px;
      bottom: 18px;
      width: 46px;
      height: 46px;
      border-radius: 14px;
      border: 1px solid var(--line);
      background: rgba(255,255,255,.06);
      color: var(--text);
      display: none;
      place-items: center;
      z-index: 9999;
      backdrop-filter: blur(10px);
    }
    #toTop:hover{ background: rgba(255,255,255,.10); }

    /* subtle parallax dots */
    .dots{
      position:absolute;
      inset:-200px -200px auto auto;
      width: 520px;
      height: 520px;
      background-image: radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px);
      background-size: 18px 18px;
      transform: rotate(12deg);
      opacity:.35;
      pointer-events:none;
    }
  </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark nav-blur fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
      <i class="bi bi-bag-heart-fill me-2"></i>Nongkasek
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="#products">สินค้า</a></li>
        <li class="nav-item"><a class="nav-link" href="#categories">หมวดหมู่</a></li>
        <li class="nav-item"><a class="nav-link" href="#reviews">รีวิว</a></li>
        <li class="nav-item ms-lg-2">
          <a class="btn btn-sm btn-brand px-3" href="#contact">
            <i class="bi bi-chat-dots me-1"></i> ติดต่อเรา
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  <div class="dots"></div>
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6" data-aos="fade-up">
        <span class="badge rounded-pill px-3 py-2 mb-3">
          <i class="bi bi-lightning-charge-fill me-1"></i> หน้าเว็บแบบเทพ + เลื่อนแล้วมีเอฟเฟกต์
        </span>

        <h1 class="hero-title mb-3">
          ช้อปสินค้าเกษตร<br>
          <span style="background:linear-gradient(135deg,#7c5cff,#22c55e);-webkit-background-clip:text;background-clip:text;color:transparent;">
            สวย • เร็ว • น่าเชื่อถือ
          </span>
        </h1>

        <p class="hero-sub mb-4">
          เลื่อนผ่านแล้วการ์ดเด้ง, มีสไลด์สินค้า, ปุ่มกลับขึ้นบน, และ Section จัดวางแบบพรีเมียม
        </p>

        <div class="d-flex gap-2 flex-wrap">
          <a href="#products" class="btn btn-brand px-4">
            <i class="bi bi-cart3 me-1"></i> ดูสินค้าเด่น
          </a>
          <a href="#categories" class="btn btn-outline-glow px-4">
            <i class="bi bi-grid me-1"></i> ดูหมวดหมู่
          </a>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mt-4 kpi">
          <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="50">
            <div class="glass p-3">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-truck"></i>
                <div>
                  <div class="fw-bold">ส่งไว</div>
                  <small>ทั่วไทย</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="glass p-3">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i>
                <div>
                  <div class="fw-bold">ปลอดภัย</div>
                  <small>เช็คก่อนส่ง</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="150">
            <div class="glass p-3">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-stars"></i>
                <div>
                  <div class="fw-bold">คุณภาพ</div>
                  <small>คัดสรร</small>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right hero card -->
      <div class="col-lg-6" data-aos="fade-left">
        <div class="glass p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold"><i class="bi bi-fire me-2"></i>โปรโมชันวันนี้</div>
            <span class="chip"><i class="bi bi-percent"></i> Hot Deal</span>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <div class="glass p-2" style="background:rgba(255,255,255,.04)">
                <img src="https://images.unsplash.com/photo-1598514982208-39e0f4d8b67b?q=80&w=900&auto=format&fit=crop"
                     class="w-100 rounded-3" style="height:140px;object-fit:cover;border:1px solid var(--line);" alt="">
                <div class="mt-2">
                  <div class="fw-bold">ปุ๋ยอินทรีย์</div>
                  <div class="text-secondary-emphasis">เริ่มต้น 99฿</div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="glass p-2" style="background:rgba(255,255,255,.04)">
                <img src="https://images.unsplash.com/photo-1609250291996-fdebe6020a6b?q=80&w=900&auto=format&fit=crop"
                     class="w-100 rounded-3" style="height:140px;object-fit:cover;border:1px solid var(--line);" alt="">
                <div class="mt-2">
                  <div class="fw-bold">เมล็ดพันธุ์</div>
                  <div class="text-secondary-emphasis">ลดเพิ่ม</div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="glass p-3" style="background:rgba(255,255,255,.04)">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <div class="fw-bold">ใส่โค้ด: <span class="text-info">NONG10</span></div>
                    <div class="text-secondary-emphasis">ลด 10% สำหรับคำสั่งซื้อแรก</div>
                  </div>
                  <button class="btn btn-sm btn-outline-glow" onclick="copyCode()">
                    <i class="bi bi-clipboard me-1"></i>คัดลอกโค้ด
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</header>

<!-- PRODUCTS (Slider) -->
<section id="products" class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
      <div data-aos="fade-up">
        <h2 class="section-title mb-1">สินค้าแนะนำ</h2>
        <div class="section-sub">เลื่อนแล้วเด้ง + สไลด์แบบพรีเมียม</div>
      </div>
      <a class="btn btn-sm btn-outline-glow" href="#" data-aos="fade-up" data-aos-delay="60">
        ดูทั้งหมด <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="glass p-3" data-aos="fade-up" data-aos-delay="80">
      <div class="swiper">
        <div class="swiper-wrapper">

          <!-- ✅ ตัวอย่างสินค้า 6 ใบ (เปลี่ยนเป็น loop จาก DB ได้) -->
          <?php
            $sample = [
              ["ปุ๋ยคอกอัดเม็ด", 129, "https://images.unsplash.com/photo-1501004318641-b39e6451bec6?q=80&w=900&auto=format&fit=crop"],
              ["ปุ๋ยอินทรีย์", 199, "https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?q=80&w=900&auto=format&fit=crop"],
              ["เมล็ดพันธุ์ผัก", 59, "https://images.unsplash.com/photo-1615485925660-9725b7ea2aa1?q=80&w=900&auto=format&fit=crop"],
              ["สารปรับปรุงดิน", 89, "https://images.unsplash.com/photo-1589927986089-35812388d1f4?q=80&w=900&auto=format&fit=crop"],
              ["อุปกรณ์รดน้ำ", 299, "https://images.unsplash.com/photo-1524593577913-85bcb3cd0b37?q=80&w=900&auto=format&fit=crop"],
              ["ถุงเพาะชำ", 39, "https://images.unsplash.com/photo-1472141521881-95d0e87e2e39?q=80&w=900&auto=format&fit=crop"],
            ];
            foreach($sample as $i => $it):
          ?>
          <div class="swiper-slide">
            <div class="product-card glass p-3 h-100" data-aos="zoom-in" data-aos-delay="<?= 50 + ($i*40) ?>">
              <img src="<?= htmlspecialchars($it[2]) ?>" alt="">
              <div class="mt-3 d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="fw-bold"><?= htmlspecialchars($it[0]) ?></div>
                  <div class="text-secondary-emphasis small">พร้อมส่ง • เก็บปลายทาง</div>
                </div>
                <span class="chip"><i class="bi bi-check2-circle"></i>แนะนำ</span>
              </div>
              <div class="mt-2 d-flex justify-content-between align-items-center">
                <div class="price fs-5">฿<?= number_format($it[1]) ?></div>
                <button class="btn btn-sm btn-brand">
                  <i class="bi bi-cart-plus me-1"></i>ใส่ตะกร้า
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>

        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section id="categories" class="py-5">
  <div class="container">
    <div class="mb-3" data-aos="fade-up">
      <h2 class="section-title mb-1">หมวดหมู่ยอดนิยม</h2>
      <div class="section-sub">การ์ดเลื่อนผ่านแล้วเด้งแบบสวย ๆ</div>
    </div>

    <div class="row g-3">
      <?php
        $cats = [
          ["ปุ๋ย", "bi-droplet-half", "เติมอาหารให้ดิน"],
          ["เมล็ดพันธุ์", "bi-flower1", "ปลูกง่าย โตไว"],
          ["อุปกรณ์", "bi-tools", "ครบจบงานเกษตร"],
          ["ดิน/วัสดุปลูก", "bi-layers", "ปรับสภาพดิน"],
        ];
        foreach($cats as $k => $c):
      ?>
      <div class="col-12 col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= 60 + $k*60 ?>">
        <div class="glass p-3 h-100">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="glass p-2" style="background:rgba(255,255,255,.04)">
              <i class="bi <?= $c[1] ?> fs-4"></i>
            </div>
            <div class="fw-bold"><?= htmlspecialchars($c[0]) ?></div>
          </div>
          <div class="section-sub"><?= htmlspecialchars($c[2]) ?></div>
          <a href="#products" class="btn btn-sm btn-outline-glow mt-3 w-100">ดูสินค้าในหมวด</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- REVIEWS -->
<section id="reviews" class="py-5">
  <div class="container">
    <div class="mb-3" data-aos="fade-up">
      <h2 class="section-title mb-1">เสียงจากลูกค้า</h2>
      <div class="section-sub">เลื่อนผ่านแล้วมีเอฟเฟกต์ + การ์ดสวย</div>
    </div>

    <div class="row g-3">
      <?php
        $reviews = [
          ["ส่งไวมาก แพ็กดีสุด ๆ", "คุณเอ", 5],
          ["สินค้าโอเค ราคาดี", "คุณบี", 4],
          ["ตอบแชทไว แนะนำเลย", "คุณซี", 5],
        ];
        foreach($reviews as $r => $rv):
      ?>
      <div class="col-12 col-lg-4" data-aos="fade-up" data-aos-delay="<?= 80 + $r*70 ?>">
        <div class="glass p-3 h-100">
          <div class="mb-2">
            <?php for($s=0;$s<$rv[2];$s++): ?>
              <i class="bi bi-star-fill text-warning"></i>
            <?php endfor; ?>
          </div>
          <div class="fw-bold"><?= htmlspecialchars($rv[0]) ?></div>
          <div class="section-sub mt-1">— <?= htmlspecialchars($rv[1]) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact" class="py-5">
  <div class="container">
    <div class="glass p-4" data-aos="zoom-in">
      <div class="row align-items-center g-3">
        <div class="col-lg-8">
          <h3 class="fw-bold mb-1">อยากให้ผม “ต่อให้เป็นเว็บขายจริง” ไหม?</h3>
          <div class="section-sub">
            ผมทำให้ได้เลย: ดึงสินค้า/รูปจาก DB, ตะกร้า, สั่งซื้อ, อัปโหลดรูปไม่พัง, หน้า Admin เทพ ๆ
          </div>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a class="btn btn-brand px-4" href="#">
            <i class="bi bi-rocket-takeoff me-1"></i> เริ่มทำเว็บขายจริง
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="py-4">
  <div class="container text-center section-sub">
    © <?= date('Y') ?> Nongkasek • Built with Bootstrap + AOS + Swiper
  </div>
</footer>

<!-- Back to top -->
<button id="toTop" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <i class="bi bi-arrow-up"></i>
</button>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  // Animate on Scroll
  AOS.init({ duration: 850, once: true, offset: 90 });

  // Slider
  new Swiper('.swiper', {
    slidesPerView: 1.15,
    spaceBetween: 14,
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: {
      576: { slidesPerView: 2.1 },
      992: { slidesPerView: 3.1 },
      1200:{ slidesPerView: 3.4 }
    }
  });

  // Back to top show/hide
  const toTop = document.getElementById('toTop');
  window.addEventListener('scroll', () => {
    toTop.style.display = (window.scrollY > 500) ? 'grid' : 'none';
  });

  function copyCode(){
    navigator.clipboard.writeText("NONG10");
    alert("คัดลอกโค้ด NONG10 แล้ว!");
  }

  // Smooth scroll for anchor
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener('click', (e)=>{
      const id = a.getAttribute('href');
      if(!id || id === '#') return;
      const el = document.querySelector(id);
      if(!el) return;
      e.preventDefault();
      el.scrollIntoView({behavior:'smooth'});
    });
  });
</script>

</body>
</html>
