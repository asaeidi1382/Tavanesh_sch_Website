<?php
require_once 'auth.php';
$db = getDB();
$news_items = $db->query("SELECT * FROM news ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// اگر دیتابیس خالی است، می‌توانیم چند خبر نمونه یا قدیمی را از فایل اصلی برداریم
// اما برای سادگی، فعلاً فقط اخبار دیتابیس را نشان می‌دهیم.
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>اخبار دبیرستان دخترانه توانش</title>
  <style>
    @font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Light.woff2') format('woff2'); font-weight:300; font-display:swap; }
    @font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
    @font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Medium.woff2') format('woff2'); font-weight:500; font-display:swap; }
    @font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
    @font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-ExtraBold.woff2') format('woff2'); font-weight:800; font-display:swap; }

    :root {
      --primary: #19b8c2;
      --primary-dark: #0c8790;
      --primary-light: #e6f8fa;
      --card: #ffffff;
      --text: #0f3d42;
      --muted: #4b6f73;
    }

    * { box-sizing: border-box; font-family: "Vazirmatn", Tahoma, sans-serif; }

    body {
      margin: 0;
      background: linear-gradient(to bottom, #f5fbfd, #ffffff);
      color: var(--text);
      line-height: 2;
    }

    header {
      position: relative;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 2.5rem 1rem;
      text-align: center;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .header-logo {
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      width: 90px;
      height: auto;
      border-radius: 22px;
      padding: 5px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(4px);
      transition: all 0.4s;
    }
    .header-logo:hover { transform: translateY(8px) scale(1.1); }

    header h1 { margin: 0; font-size: 1.7rem; font-weight: 800; }
    header p { margin-top: 0.75rem; opacity: 0.95; font-size: 1.1rem; }

    main {
      max-width: 1400px;
      margin: 3rem auto;
      padding: 0 1rem;
      display: grid;
      gap: 2.5rem;
      grid-template-columns: 1fr;
    }
    @media (min-width: 768px) { main { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 1024px) { main { grid-template-columns: repeat(3, 1fr); } }

    .news-card {
      background: var(--card);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
      display: flex;
      flex-direction: column;
      transition: all 0.4s ease;
    }
    .news-card:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(25, 184, 194, 0.35); }

    .news-gallery { position: relative; height: 240px; background: var(--primary-light); overflow: hidden; }
    .news-images { display: flex; width: 100%; height: 100%; scroll-snap-type: x mandatory; scroll-behavior: smooth; overflow-x: auto; scrollbar-width: none; }
    .news-images::-webkit-scrollbar { display: none; }
    .news-images img { width: 100%; height: 240px; object-fit: cover; flex-shrink: 0; scroll-snap-align: start; cursor: pointer; }

    .gallery-nav {
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(255, 255, 255, 0.85);
      padding: 6px 12px;
      border-radius: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .gallery-prev, .gallery-next { background: none; border: none; font-size: 1.2rem; color: var(--primary-dark); cursor: pointer; }
    .gallery-dots { display: flex; gap: 6px; }
    .dot { width: 7px; height: 7px; background: var(--muted); border-radius: 50%; transition: 0.3s; }
    .dot.active { background: var(--primary); }

    .news-content { padding: 1.8rem 2rem 2.4rem; flex: 1; }
    .news-content h2 { font-size: 1.25rem; margin: 0 0 0.8rem; color: var(--primary-dark); font-weight: 800; text-align: justify; }
    .news-content .date { font-size: 1.1rem; color: var(--muted); margin-bottom: 1.2rem; font-weight: 600; }
    .news-content p { font-size: 0.9rem; margin: 0; text-align: justify; line-height: 2.1; white-space: pre-line; }

    footer { margin-top: 4rem; padding: 3rem 1rem; background: var(--primary-light); text-align: center; border-top: 2px solid var(--primary); }

    .lightbox {
      display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.95); align-items: center; justify-content: center;
    }
    .lightbox.active { display: flex; }
    .lightbox-img-container { position: relative; max-width: 95%; max-height: 95%; }
    .lightbox img { max-width: 100%; max-height: 90vh; border-radius: 12px; }
    .lightbox-prev, .lightbox-next {
      position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0, 0, 0, 0.5);
      color: white; border: none; width: 45px; height: 45px; border-radius: 50%; font-size: 1.5rem; cursor: pointer;
    }
    .lightbox-prev { left: 10px; } .lightbox-next { right: 10px; }
    .lightbox-close { position: absolute; top: 20px; right: 30px; font-size: 3rem; color: #fff; cursor: pointer; }

    .back-to-home {
      position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
      padding: 10px 24px; background: var(--primary); color: #fff; text-decoration: none;
      border-radius: 30px; font-weight: 700; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 999;
    }
  </style>
</head>
<body>

<header>
  <a href="index.html"><img class="header-logo" src="images/logo-named.png" alt="لوگو"></a>
  <h1>اخبار دبیرستان دخترانه توانش</h1>
  <p>آخرین رویدادها و اطلاعیه‌های مدرسه</p>
</header>

<main>
  <?php foreach ($news_items as $item):
    $images = json_decode($item['images'], true) ?: [];
  ?>
    <article class="news-card">
      <div class="news-gallery">
        <div class="news-images">
          <?php if (!empty($item['video_embed'])): ?>
            <div style="width:100%; height:240px; flex-shrink:0;">
              <?= $item['video_embed'] ?>
            </div>
          <?php endif; ?>
          <?php foreach ($images as $img): ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
          <?php endforeach; ?>
          <?php if (empty($images) && empty($item['video_embed'])): ?>
             <img src="images/logo-named.png" style="object-fit: contain; padding: 40px;" alt="لوگو">
          <?php endif; ?>
        </div>
        <?php if (count($images) + (!empty($item['video_embed']) ? 1 : 0) > 1): ?>
        <div class="gallery-nav">
          <button class="gallery-prev">→</button>
          <div class="gallery-dots"></div>
          <button class="gallery-next">←</button>
        </div>
        <?php endif; ?>
      </div>
      <div class="news-content">
        <h2><?= to_persian_num(htmlspecialchars($item['title'])) ?></h2>
        <div class="date"><?= to_persian_num(htmlspecialchars($item['date'])) ?></div>
        <p><?= to_persian_num(nl2br(htmlspecialchars($item['content']))) ?></p>
      </div>
    </article>
  <?php endforeach; ?>
</main>

<footer>
  <p>© تمامی حقوق متعلق به دبیرستان دخترانه توانش است</p>
</footer>

<a href="index.html" class="back-to-home">🏠 بازگشت به صفحه اصلی</a>

<div class="lightbox" id="lightbox">
  <div class="lightbox-img-container">
    <button class="lightbox-prev">←</button>
    <img id="lightbox-img" src="" alt="بزرگ‌نمایی">
    <button class="lightbox-next">→</button>
  </div>
  <div class="lightbox-close">×</div>
</div>

<script>
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  let currentGallery = [];
  let currentIndex = 0;

  document.querySelectorAll('.news-images img').forEach(img => {
    img.addEventListener('click', () => {
      currentGallery = Array.from(img.closest('.news-images').querySelectorAll('img'));
      currentIndex = currentGallery.indexOf(img);
      showLightbox();
    });
  });

  function showLightbox() {
    lightboxImg.src = currentGallery[currentIndex].src;
    lightbox.classList.add('active');
  }

  document.querySelector('.lightbox-next').addEventListener('click', () => {
    if (currentIndex < currentGallery.length - 1) { currentIndex++; showLightbox(); }
  });
  document.querySelector('.lightbox-prev').addEventListener('click', () => {
    if (currentIndex > 0) { currentIndex--; showLightbox(); }
  });
  lightbox.addEventListener('click', e => {
    if (e.target === lightbox || e.target.classList.contains('lightbox-close')) lightbox.classList.remove('active');
  });

  document.querySelectorAll('.news-gallery').forEach(gallery => {
    const container = gallery.querySelector('.news-images');
    const dotsContainer = gallery.querySelector('.gallery-dots');
    if (!dotsContainer) return;

    const items = container.children;
    const total = items.length;

    for (let i = 0; i < total; i++) {
      const dot = document.createElement('span');
      dot.classList.add('dot');
      if (i === 0) dot.classList.add('active');
      dotsContainer.appendChild(dot);
    }

    const dots = dotsContainer.querySelectorAll('.dot');
    container.addEventListener('scroll', () => {
      const index = Math.round(Math.abs(container.scrollLeft) / container.clientWidth);
      dots.forEach((d, i) => d.classList.toggle('active', i === index));
    });

    gallery.querySelector('.gallery-next').addEventListener('click', () => {
      container.scrollBy({ left: -container.clientWidth, behavior: 'smooth' });
    });
    gallery.querySelector('.gallery-prev').addEventListener('click', () => {
      container.scrollBy({ left: container.clientWidth, behavior: 'smooth' });
    });
  });
</script>
</body>
</html>
