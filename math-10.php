<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>آموزش‌های غیرحضوری - دبیرستان دخترانه توانش</title>
  <!-- فونت وزیر -->

  <style>
    body {
      font-family: 'Vazirmatn', Tahoma, sans-serif;
      background: #e0f7f9;
      margin: 0;
      padding: 0;
      direction: rtl;
    }
    header {
      background: #00bcd4;
      color: white;
      padding: 20px;
      text-align: center;
    }
    header h1 {
      margin: 0;
      font-size: 28px;
    }
    header h2 {
      margin: 5px 0 0;
      font-size: 18px;
      font-weight: normal;
    }
    .lesson-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      margin: 30px;
    }
    .video-box {
      flex: 1 1 300px;
      max-width: 400px;
      background: #ffffff;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    }
    video {
      width: 100%;
      border-radius: 8px;
    }
    h3 {
      font-size: 18px;
      color: #007c91;
      margin: 10px 0;
    }
    .download {
      margin-top: 8px;
    }
    .download a {
      text-decoration: none;
      color: #00bcd4;
      font-weight: bold;
    }
    /* ریسپانسیو برای موبایل */
    @media (max-width: 600px) {
      header h1 { font-size: 22px; }
      header h2 { font-size: 16px; }
      h3 { font-size: 16px; }
      .lesson-container { margin: 15px; }
    }
  </style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">













  <div class="lesson-container">
    <div class="video-box">
      <h3>قسمت ۱</h3>
      <video controls>
        <source src="/learning-Videos/math10-1.mp4" type="video/mp4">
      </video>
      <div class="download"><a href="/learning-Videos/math10-1.mp4" download>⬇ دانلود کلیپ ۱</a></div>
    </div>
    <div class="video-box">
      <h3>قسمت ۲</h3>
      <video controls>
        <source src="/learning-Videos/math10-2.mp4" type="video/mp4">
      </video>
      <div class="download"><a href="/learning-Videos/math10-2.mp4" download>⬇ دانلود کلیپ ۲</a></div>
    </div>
    <div class="video-box">
      <h3>قسمت ۳</h3>
      <video controls>
        <source src="/learning-Videos/math10-3.mp4" type="video/mp4">
      </video>
      <div class="download"><a href="/learning-Videos/math10-3.mp4" download>⬇ دانلود کلیپ ۳</a></div>
    </div>
    <div class="video-box">
      <h3>قسمت ۴</h3>
      <video controls>
        <source src="/learning-Videos/math10-4.mp4" type="video/mp4">
      </video>
      <div class="download"><a href="/learning-Videos/math10-4.mp4" download>⬇ دانلود کلیپ ۴</a></div>
    </div>
    <div class="video-box">
      <h3>قسمت ۵</h3>
      <video controls>
        <source src="/learning-Videos/math10-5.mp4" type="video/mp4">
      </video>
      <div class="download"><a href="/learning-Videos/math10-5.mp4" download>⬇ دانلود کلیپ ۵</a></div>
    </div>
  </div>




</main>
</div>
</body>
</html>