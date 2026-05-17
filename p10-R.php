
<!DOCTYPE html>
<html lang="fa" dir="rtl">



<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>محتواهای آموزشی توانش 📚</title>
    <!-- Preload Vazirmatn Fonts -->







    <style>


   :root {
      --turquoise: #19b8c2;
      --turquoise-dark: #0c8790;
      --turquoise-light: #e6f8fa;
      --turquoise-lighter: #f0fbfd;
      --text: #0f3d42;
      --shadow-sm: 0 4px 15px rgba(0,0,0,0.08);
      --shadow-md: 0 12px 30px rgba(0,0,0,0.12);
      --shadow-lg: 0 20px 50px rgba(0,0,0,0.18);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: "Vazirmatn", Tahoma, sans-serif;
      background: linear-gradient(to bottom, var(--turquoise-lighter), #ffffff);
      color: var(--text);
      line-height: 1.6;
      padding: 0;
    }

    .top-actions {
      padding: 16px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .top-actions a {
      display: inline-block;
      margin: 0 10px;
      padding: 10px 24px;
      background: white;
      color: var(--turquoise);
      text-decoration: none;
      border-radius: 14px;
      font-weight: 700;
      font-size: 1.1rem;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      transition: var(--transition);
    }

    .top-actions a:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.25);
      background: var(--turquoise-light);
    }

    header {
      text-align: center;
      padding: 32px 20px 24px;
      background: var(--turquoise-lighter);
      margin: 10px auto;
      max-width: 900px;
      border-radius: 20px;
      box-shadow: var(--shadow-md);
      border: 2px solid var(--turquoise-light);
    }


    header h0 {
      font-size: 2.0rem;
      font-weight: 400;
      color: Yellow;
    }
    header h1 {
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--turquoise-dark);
    }

    header h2 {
      font-size: 1.4rem;
      font-weight: 600;
      margin-top: 8px;
      color: var(--text);
      opacity: 0.9;
    }

    .lesson-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 28px;
      padding: 0 20px 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .video-box {
      flex: 1 1 320px;
      max-width: 420px;
      background: white;
      padding: 24px;
      border-radius: 20px;
      box-shadow: var(--shadow-md);
      border: 2px solid var(--turquoise-light);
      transition: var(--transition);
    }

    .video-box:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: var(--turquoise);
    }

    .video-box h3 {
      font-size: 1rem;
      font-weight: 700;
      color: var(--turquoise-dark);
      margin-bottom: 16px;
      text-align: center;
    }

    video {
      width: 100%;
      border-radius: 16px;
      background: #000;
    }

    .download {
      margin-top: 16px;
      text-align: center;
    }

    .download a {
      display: inline-block;
      padding: 10px 20px;
      background: var(--turquoise);
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
    }

    .download a:hover {
      background: var(--turquoise-dark);
      transform: scale(1.05);
    }

    @media (max-width: 600px) {
      .top-actions a {
        display: block;
        margin: 8px auto;
        max-width: 280px;
      }
      header h1 { font-size: 1.8rem; }
      header h2 { font-size: 1.2rem; }
      .video-box { padding: 20px 16px; }
    }















    </style>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>محتواهای آموزشی دبیرستان دخترانه توانش</title>
    <!-- فونت وزیرمتن -->
    <style>
        :root {
            --primary: #19b8c2;
            --primary-dark: #0c8790;
            --primary-light: #e6f8fa;
            --card: #ffffff;
            --text: #ffffff;
            --muted: #4b6f73;
        }

        * {
            box-sizing: border-box;
            font-family: "Vazirmatn", Tahoma, sans-serif;
            font-variant-numeric: normal;
        }

        body {
            margin: 0;
            background: linear-gradient(to bottom, #f5fbfd, #ffffff);
            color: var(--text);
            line-height: 2;
            font-feature-settings: "ss01";
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
            top: 1.5rem;
            right: 1.5rem;
            width: 90px;
            height: auto;
            cursor: pointer;
            border-radius: 22px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .header-logo:hover {
            transform: translateY(-8px) scale(1.12);
            box-shadow: 0 12px 30px rgba(25, 184, 194, 0.4);
        }

        header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 800;
        }

        header p {
            margin-top: 0.75rem;
            opacity: 0.95;
            font-size: 1.1rem;
        }

        main {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 1rem;
            display: grid;
            gap: 2.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            main {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            main {
                grid-template-columns: repeat(3, 1fr);
            }
        }

 .video-box {
      flex: 1 1 320px;
      max-width: 420px;
      background: white;
      padding: 24px;
      border-radius: 20px;
      box-shadow: var(--shadow-md);
      border: 2px solid var(--turquoise-light);
      transition: var(--transition);
    }
        .lightbox-close:hover {
            opacity: 1;
        }

        /* دکمه بازگشت */
        .back-to-home {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border-radius: 18px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-to-home:hover {
            background: var(--primary-dark);
            transform: translateX(-50%) translateY(-4px);
        }

        .back-to-home::before {
            content: "🏠";
            font-size: 1.3rem;
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
        مرورگر شما از ویدیو پشتیبانی نمی‌کند.
      </video>
    </div>
    <div class="video-box">
      <h3>قسمت ۲</h3>
      <video controls>
        <source src="/learning-Videos/math10-2.mp4" type="video/mp4">
      </video>
    </div>
    <div class="video-box">
      <h3>قسمت ۳</h3>
      <video controls>
        <source src="/learning-Videos/math10-3.mp4" type="video/mp4">
      </video>
    </div>
    <div class="video-box">
      <h3>قسمت ۴</h3>
      <video controls>
        <source src="/learning-Videos/math10-4.mp4" type="video/mp4">
      </video>
    </div>
    <div class="video-box">
      <h3>قسمت ۵</h3>
      <video controls>
        <source src="/learning-Videos/math10-5.mp4" type="video/mp4">
      </video>
    </div>
  </div>









</main>
</div>
</body>
</html>