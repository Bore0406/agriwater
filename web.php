<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>田地節能用水對比系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f1f8e9; font-family:'Segoe UI',sans-serif; margin:0; }
    .hero { position:relative; overflow:hidden; height:420px; background:#2e7d32; }
    .slide { display:none; position:absolute; inset:0; }
    .slide.active { display:flex; align-items:center; justify-content:center; flex-direction:column; }
    .slide-text { color:white; text-align:center; text-shadow:1px 2px 8px rgba(0,0,0,0.5); }
    .slide-text h1 { font-size:2.8rem; font-weight:bold; margin-bottom:8px; }
    .slide-text p  { font-size:1.2rem; }
    .slide-bg { position:absolute; inset:0; background-size:cover; background-position:center; filter:brightness(0.45); z-index:0; }
    .slide-text { position:relative; z-index:1; }
    .nav-dots { position:absolute; bottom:18px; left:50%; transform:translateX(-50%); display:flex; gap:8px; z-index:2; }
    .dot { width:12px; height:12px; border-radius:50%; background:rgba(255,255,255,0.5); cursor:pointer; border:none; }
    .dot.active { background:white; }
    .feature-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; padding:40px 60px; }
    .fcard { background:white; border-radius:14px; padding:28px 20px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
    .fcard .icon { font-size:2.4rem; margin-bottom:12px; }
    .fcard h5 { font-weight:700; color:#1b5e20; }
    .fcard p { font-size:0.9rem; color:#555; }
    .news-section { background:white; padding:40px 60px; }
    .news-section h2 { color:#2e7d32; border-left:5px solid #2e7d32; padding-left:12px; margin-bottom:24px; }
    .news-item { border-bottom:1px solid #e0e0e0; padding:14px 0; }
    .news-item .date { color:#aaa; font-size:0.85rem; }
    footer { background:#1b5e20; color:white; text-align:center; padding:20px; margin-top:40px; font-size:0.9rem; }
  </style>
</head>
<body>
<?php include('navbar.html'); ?>

<!-- 輪播 -->
<div class="hero">
  <div class="slide active">
    <div class="slide-bg" style="background-image:url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1200');"></div>
    <div class="slide-text">
      <h1>🌾 田地節能用水對比系統</h1>
      <p>讓每一滴水都用在刀口上，精準農業從這裡開始</p>
    </div>
  </div>
  <div class="slide">
    <div class="slide-bg" style="background-image:url('https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1200');"></div>
    <div class="slide-text">
      <h1>📊 即時用水對比分析</h1>
      <p>比對實際灌溉量與理論標準，節能率一目了然</p>
    </div>
  </div>
  <div class="slide">
    <div class="slide-bg" style="background-image:url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1200');"></div>
    <div class="slide-text">
      <h1>🌱 智慧耕作新時代</h1>
      <p>響應 SDGs 12 與 15，守護農業資源永續利用</p>
    </div>
  </div>
  <div class="nav-dots">
    <button class="dot active" onclick="goSlide(0)"></button>
    <button class="dot" onclick="goSlide(1)"></button>
    <button class="dot" onclick="goSlide(2)"></button>
  </div>
</div>

<!-- 功能特色卡片 -->
<div class="feature-cards">
  <div class="fcard">
    <div class="icon">👤</div>
    <h5>會員系統</h5>
    <p>農民與管理者雙重角色，安全帳號管理</p>
    <a href="login.php" class="btn btn-sm btn-outline-success mt-2">立即登入</a>
  </div>
  <div class="fcard">
    <div class="icon">🌾</div>
    <h5>農田管理</h5>
    <p>新增、修改您的農田資料與作物類型</p>
    <a href="farmland.php" class="btn btn-sm btn-outline-success mt-2">管理農田</a>
  </div>
  <div class="fcard">
    <div class="icon">🔍</div>
    <h5>查詢用水</h5>
    <p>月曆式介面查看每日用水量與節能狀況</p>
    <a href="search.php" class="btn btn-sm btn-outline-success mt-2">查詢記錄</a>
  </div>
  <div class="fcard">
    <div class="icon">💬</div>
    <h5>留言板</h5>
    <p>申請新增作物標準，管理員即時回饋</p>
    <a href="board.php" class="btn btn-sm btn-outline-success mt-2">前往留言</a>
  </div>
  <div class="fcard">
    <div class="icon">📊</div>
    <h5>數據分析</h5>
    <p>節能排行榜與趨勢預測，掌握用水全貌</p>
    <a href="analysis.php" class="btn btn-sm btn-outline-success mt-2">查看分析</a>
  </div>
</div>

<!-- 最新消息 -->
<div class="news-section">
  <h2>最新消息</h2>
  <div class="news-item">
    <div class="date">2026-06-01</div>
    <strong>系統上線公告：</strong>田地節能用水對比系統正式上線，歡迎農友踴躍使用！
  </div>
  <div class="news-item">
    <div class="date">2026-05-20</div>
    <strong>資料更新：</strong>已新增釋迦、蓮霧用水量標準至典藏系統。
  </div>
  <div class="news-item">
    <div class="date">2026-05-10</div>
    <strong>功能上線：</strong>數據分析系統新增「趨勢預測」功能，可預估下週需水量。
  </div>
</div>

<footer>
  &copy; 2026 田地節能用水對比系統 ｜ 第13組 — 吳濬澤、洪鵬翔、許珺翔、蔡善宇 ｜ 指導老師：李金鳳教授
</footer>

<script>
  let cur = 0;
  const slides = document.querySelectorAll('.slide');
  const dots   = document.querySelectorAll('.dot');
  function goSlide(n) {
    slides[cur].classList.remove('active');
    dots[cur].classList.remove('active');
    cur = n;
    slides[cur].classList.add('active');
    dots[cur].classList.add('active');
  }
  setInterval(() => goSlide((cur + 1) % slides.length), 4000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
