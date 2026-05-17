<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>صفحه اصلی گالری دبیرستان توانش</title>

  <!-- فونت محلی Vazirmatn (وزیرمتن) -->
  <style>




    body {
      font-family: "Vazirmatn", system-ui, sans-serif;
      background: #f0fcfd;
      margin: 0;
      color: #0f2f2e;
      min-height: 100vh;           /* برای اینکه دکمه پایین همیشه قابل دیدن باشد */
      position: relative;          /* برای position fixed دکمه */
    }

    header {
      background: linear-gradient(90deg, #0097a7, #00bcd4);
      color: #fff;
      padding: 20px;
      text-align: center;
    }

    main {
      max-width: 900px;
      margin: 40px auto 80px auto; /* فاصله پایین بیشتر برای جلوگیری از هم‌پوشانی با دکمه */
      padding: 0 20px;
    }

    h2 {
      text-align: center;
      margin-bottom: 24px;
      color: #0097a7;
    }

    .gallery-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      border: 1px solid rgba(0,188,212,0.25);
      box-shadow: 0 6px 16px rgba(0,188,212,0.12);
      transition: transform .2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0,188,212,0.18);
    }

    .card a {
      text-decoration: none;
      color: #0097a7;
      font-weight: 600;
      font-size: 16px;
    }

    /* دکمه بازگشت ثابت در پایین */
    .back-btn {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: linear-gradient(90deg, #0097a7, #00bcd4);
      color: white;
      padding: 12px 28px;
      border-radius: 50px;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      box-shadow: 0 6px 20px rgba(0, 151, 167, 0.4);
      transition: all 0.3s ease;
      z-index: 1000;
      white-space: nowrap;
    }

    .back-btn:hover {
      transform: translateX(-50%) translateY(-3px);
      box-shadow: 0 10px 30px rgba(0, 151, 167, 0.5);
      background: linear-gradient(90deg, #00bcd4, #0097a7);
    }

    /* برای موبایل کوچکتر */
    @media (max-width: 480px) {
      .back-btn {
        padding: 10px 24px;
        font-size: 0.95rem;
        bottom: 16px;
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














    <h2>لطفاً یکی از گالری‌ها را انتخاب کنید</h2>
    <div class="gallery-list">
      <div class="card">
        <a href="gallery-Spaces.php">🏫 گالری فضاهای مدرسه</a>
      </div>
      <div class="card">
        <a href="gallery-posters.php">🖼️ گالری پوسترها</a>
      </div>
      <div class="card">
        <a href="gallery-ceremonies.php">🎉 گالری مراسم و مناسبت‌ها</a>
      </div>
      <div class="card">
        <a href="gallery-videos.php">🎬 گالری فیلم و کلیپ</a>
      </div>
      <div class="card">
        <a href="gallery-Learns.php">📚 گالری مطالب آموزشی</a>
      </div>
      <div class="card">
        <a href="gallery-photos.php">📷 گالری عکس‌های عمومی</a>
      </div>
      <div class="card">
        <a href="gallery-Ordoo1.php">🚌 گالری اردوی یک روزه</a>
      </div>
      <div class="card">
        <a href="Gallery-MOALEM1405.php">👏 گالری جشن روز معلم</a>
      </div>

    </div>


  <!-- دکمه بازگشت ثابت -->
  <a href="index.php" class="back-btn">بازگشت به صفحه اصلی</a>




</main>
</div>
</body>
</html>