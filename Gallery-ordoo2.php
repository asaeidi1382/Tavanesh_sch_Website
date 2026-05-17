<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>گالری اردوی یک روزه - دبیرستان توانش</title>



  <style>
    :root {
      --turquoise-600: #00bcd4;
      --turquoise-700: #0097a7;
      --turquoise-100: #e0f7fa;
      --turquoise-50:  #f0fcfd;
      --white: #ffffff;
      --ink-900: #0f2f2e;
    }

    body {
      font-family: "Vazirmatn", system-ui, sans-serif;
      background: var(--turquoise-50);
      margin: 0;
      color: var(--ink-900);
    }

    header {
      background: linear-gradient(90deg, var(--turquoise-700), var(--turquoise-600));
      color: var(--white);
      padding: 20px 16px;
      text-align: center;
    }

    .header-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      position: relative;
      max-width: 1400px;
      margin: 0 auto;
    }

    h1 {
      margin: 8px 0 4px 0;
      font-size: 1.85rem;
      font-weight: 600;
      line-height: 1.3;
    }

    .home-btn {
      background: var(--white);
      color: var(--turquoise-700);
      border: 1px solid rgba(0,188,212,0.4);
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      font-size: 0.95rem;
      box-shadow: 0 2px 8px rgba(0,188,212,0.18);
      transition: all .22s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      white-space: nowrap;
    }

    .home-btn:hover {
      background: var(--turquoise-100);
      transform: scale(1.04);
      box-shadow: 0 4px 14px rgba(0,188,212,0.28);
      color: var(--turquoise-800);
    }

    .toolbar {
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
      padding: 14px 16px;
    }

    .btn {
      background: var(--white);
      color: var(--turquoise-700);
      border: 1px solid rgba(0,188,212,0.35);
      padding: 8px 12px;
      border-radius: 10px;
      cursor: pointer;
      transition: all .2s ease;
      font-size: 14px;
    }

    .btn:hover {
      background: var(--turquoise-100);
    }

    .gallery {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      padding: 16px;
      max-width: 1400px;
      margin: 0 auto 40px;
    }

    .card {
      background: var(--white);
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid rgba(0,188,212,0.25);
      box-shadow: 0 8px 20px rgba(0,188,212,0.12);
      transition: transform .18s ease;
    }

    .card:hover {
      transform: translateY(-2px);
    }

    .thumb {
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      display: block;
    }

    /* لایت‌باکس */
    .lightbox {
      position: fixed;
      inset: 0;
      background: rgba(10, 35, 34, 0.8);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 50;
      padding: 16px;
    }

    .lightbox.open {
      display: flex;
    }

    .lightbox-content {
      position: relative;
      max-width: 92vw;
      max-height: 86vh;
      background: var(--white);
      border-radius: 14px;
      overflow: hidden;
    }

    .lightbox img {
      width: 100%;
      height: auto;
      max-height: 86vh;
      object-fit: contain;
      display: block;
    }

    .icon-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0,188,212,0.78);
      color: #fff;
      border: none;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      cursor: pointer;
      transition: all .2s ease;
      font-size: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
    }

    .icon-btn:hover,
    .icon-btn:focus {
      background: rgba(0,151,167,0.92);
      outline: 2px solid #fff;
      outline-offset: 2px;
    }

    #prevBtn { left: 16px; }
    #nextBtn { right: 16px; }
    #closeBtn {
      top: 12px;
      right: 12px;
      transform: none;
      width: 36px;
      height: 36px;
      font-size: 18px;
      background: rgba(0,188,212,0.9);
    }

    /* ────────────────────────────────
       حالت دسکتاپ (عرض بیشتر از 640px)
    ───────────────────────────────── */
    @media (min-width: 640px) {
      header {
        padding: 18px 24px;
      }

      .header-content {
        position: relative;
        padding: 0 220px 0 24px;     /* فضای کافی در سمت راست برای دکمه */
        display: flex;
        justify-content: center;
        align-items: center;
      }

      h1 {
        margin: 0;
        font-size: 2rem;
        text-align: center;          /* عنوان کاملاً وسط */
        flex: 1;
        padding: 0 40px;             /* فاصله از دکمه */
      }

      .home-btn {
        position: absolute;
        top: 50%;
        right: 24px;
        transform: translateY(-50%);
        margin: 0;
        padding: 10px 18px;
        font-size: 0.95rem;
        z-index: 5;
      }

      .home-btn:hover {
        transform: translateY(-50%) scale(1.04);
      }
    }

    /* ────────────────────────────────
       موبایل خیلی کوچک (اختیاری)
    ───────────────────────────────── */
    @media (max-width: 400px) {
      h1 {
        font-size: 1.6rem;
      }

      .home-btn {
        padding: 9px 16px;
        font-size: 0.9rem;
      }
    }
  </style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">














  <!-- کنترل تعداد ستون‌ها -->
  <div class="toolbar">
    <button class="btn" id="grid2">دو ستونه</button>
    <button class="btn" id="grid3">سه ستونه</button>
    <button class="btn" id="grid4">چهار ستونه</button>
    <button class="btn" id="grid5">پنج ستونه</button>
    <button class="btn" id="grid6">شش ستونه</button>
  </div>

  <section class="gallery" id="gallery"></section>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox">
    <div class="lightbox-content">
      <img id="lightboxImg" alt="تصویر بزرگ شده" />
    </div>
    <button class="icon-btn" id="prevBtn" aria-label="قبلی">◀</button>
    <button class="icon-btn" id="nextBtn" aria-label="بعدی">▶</button>
    <button class="icon-btn" id="closeBtn" aria-label="بستن">✖</button>
  </div>

  <script>
    // مسیر و نام‌گذاری عکسهای عمومی
    const basePath = "images/Ordoo-mahan1404/";
    const startIndex = 1;
    const endIndex = 99;

    function pad2(n){ return n.toString().padStart(2, '0'); }
    function fileName(n){ return `Tavanesh-Ordoo-${pad2(n)}.jpg`; }

    const galleryEl = document.getElementById('gallery');
    const items = [];
    for(let i = startIndex; i <= endIndex; i++){
      items.push({ index: i, src: basePath + fileName(i) });
    }

    items.forEach((item, idx) => {
      const card = document.createElement('article');
      card.className = 'card';
      const img = document.createElement('img');
      img.className = 'thumb';
      img.src = item.src;
      img.alt = `تصویر ${item.index}`;
      img.addEventListener('click', () => openLightbox(idx));
      img.onerror = () => { card.style.opacity = '0.45'; card.style.filter = 'grayscale(70%)'; };
      card.appendChild(img);
      galleryEl.appendChild(card);
    });

    const lightboxEl = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const closeBtn = document.getElementById('closeBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    let currentIndex = 0;

    function openLightbox(idx){
      currentIndex = idx;
      updateLightbox();
      lightboxEl.classList.add('open');
      nextBtn.focus();
    }
    function closeLightbox(){ lightboxEl.classList.remove('open'); }
    function updateLightbox(){
      const item = items[currentIndex];
      lightboxImg.src = item.src;
    }
    function prev(){
      currentIndex = (currentIndex - 1 + items.length) % items.length;
      updateLightbox();
      prevBtn.focus();
    }
    function next(){
      currentIndex = (currentIndex + 1) % items.length;
      updateLightbox();
      nextBtn.focus();
    }

    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', prev);
    nextBtn.addEventListener('click', next);

    lightboxEl.addEventListener('click', (e) => {
      if(e.target === lightboxEl) closeLightbox();
    });

    window.addEventListener('keydown', (e) => {
      if(!lightboxEl.classList.contains('open')) return;
      if(e.key === 'Escape') closeLightbox();
      if(e.key === 'ArrowLeft') prev();
      if(e.key === 'ArrowRight') next();
    });

    document.getElementById('grid2').addEventListener('click', () => {
      galleryEl.style.gridTemplateColumns = 'repeat(2, 1fr)';
    });
    document.getElementById('grid3').addEventListener('click', () => {
      galleryEl.style.gridTemplateColumns = 'repeat(3, 1fr)';
    });
    document.getElementById('grid4').addEventListener('click', () => {
      galleryEl.style.gridTemplateColumns = 'repeat(4, 1fr)';
    });
    document.getElementById('grid5').addEventListener('click', () => {
      galleryEl.style.gridTemplateColumns = 'repeat(5, 1fr)';
    });
    document.getElementById('grid6').addEventListener('click', () => {
      galleryEl.style.gridTemplateColumns = 'repeat(6, 1fr)';
    });
  </script>




</main>
</div>
</body>
</html>