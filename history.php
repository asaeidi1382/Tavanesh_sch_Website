<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>دبیرستان دخترانه توانش — تاریخچه</title>



  <style>
    :root{
      --turquoise: #19b8c2;
      --turquoise-dark: #0c8790;
      --turquoise-light: #e6f8fa;
      --turquoise-lighter: #f0fbfd;
      --text: #0f3d42;
      --shadow-sm: 0 4px 15px rgba(0,0,0,0.08);
      --shadow-md: 0 12px 30px rgba(0,0,0,0.12);
      --shadow-lg: 0 20px 50px rgba(0,0,0,0.18);
      --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      --white: #ffffff;
    }

    *{ box-sizing: border-box; margin:0; padding:0; }
    body{
      background: linear-gradient(to bottom, #f5fbfd, #ffffff);
      color: var(--text);
      font-family: "Vazirmatn", sans-serif;
      line-height: 1.7;
    }

    /* هدر */
    .topbar{
      background: var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000;
      box-shadow: var(--shadow-md);
    }
    .topbar-inner{
      max-width: 1400px; margin:0 auto; padding:14px 20px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .brand{ display:flex; align-items:center; gap:16px; }
    .logo{
      width:72px; height:72px; border-radius:18px; overflow:hidden;
      background: rgba(255,255,255,0.2); box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      transition: var(--transition);
    }
    .logo:hover{ transform: translateY(-4px); }
    .logo img{ width:100%; height:100%; object-fit:contain; }
    .site-title{ font-size:2rem; font-weight:800; }
    .page-subtitle{ font-size:1.1rem; opacity:0.95; }

    .hamburger{
      display:none; cursor:pointer; width:50px; height:50px; border-radius:16px;
      background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4);
      display:flex; align-items:center; justify-content:center;
      box-shadow: var(--shadow-sm); transition: var(--transition);
    }
    .hamburger:hover{ background: rgba(255,255,255,0.3); transform: translateY(-3px); }
    .hamburger .bar{ width:24px; height:3px; background:#fff; border-radius:3px; position:relative; }
    .hamburger .bar::before,.hamburger .bar::after{
      content:""; position:absolute; width:24px; height:3px; background:#fff; border-radius:3px;
    }
    .hamburger .bar::before{ top:-8px; }
    .hamburger .bar::after{ top:8px; }

    .layout{
      max-width: 1400px; margin:32px auto; padding:0 16px;
      display:grid; grid-template-columns:1fr; gap:28px;
    }

    /* سایدبار */
    aside.sidebar{
      background: var(--turquoise); color:#fff; border-radius:20px; padding:20px;
      box-shadow: var(--shadow-lg); position:relative; overflow:hidden;
    }
    aside.sidebar::before{
      content:""; position:absolute; inset:0;
      background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent 70%);
      pointer-events:none;
    }
    .menu-title{ font-size:1.4rem; font-weight:800; text-align:center; margin-bottom:16px; }
    .menu > li { margin-bottom: 8px; }
    .menu a{
      display:flex; justify-content:space-between; align-items:center;
      padding:14px 18px; margin:4px 0; border-radius:16px;
      background: rgba(255,255,255,0.15); font-weight:600; text-decoration:none; color:#fff;
      transition: var(--transition); box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      cursor: pointer;
    }
    .menu a:hover{
      background: rgba(255,255,255,0.25);
      transform: translateY(-4px);
      box-shadow: 0 12px 25px rgba(0,0,0,0.25);
    }
    .submenu{ display: none; background: rgba(255,255,255,0.1); border-radius: 12px; margin-top: 8px; overflow: hidden; }
    .submenu.open{ display: block; }
    .submenu a{ padding-right: 40px; font-weight: 500; background: transparent; box-shadow: none; }
    .submenu a:hover{ background: rgba(255,255,255,0.2); }
    .caret{
      width:0; height:0; border-right:6px solid transparent; border-left:6px solid transparent;
      border-top:8px solid #fff; transition: transform 0.3s ease;
    }
    .caret.open{ transform: rotate(180deg); }

    main.content{
      background: var(--turquoise-lighter); border-radius:20px; padding:40px;
      box-shadow: var(--shadow-lg);
      min-height: 70vh;
    }

    @media(max-width:991px){
      .hamburger{ display:flex; }
      aside.sidebar{
        position:fixed; top:74px; left:0; bottom:0; width:80vw; max-width:340px;
        transform:translateX(-100%); transition:var(--transition); z-index:1100; border-radius:0;
      }
      aside.sidebar.open{ transform:translateX(0); }
    }
    @media(min-width:992px){
      .layout{ grid-template-columns: 310px 1fr; gap:36px; }
    }
  </style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">


















  <aside class="sidebar" id="sidebar">
    <div class="menu-title">منوی اصلی</div>
    <ul class="menu">
      <li>
        <a href="#" data-toggle="submenu-1"><span>صفحه اصلی</span><span class="caret" id="caret-1"></span></a>
        <ul class="submenu" id="submenu-1">
          <li><a href="weekly.php">برنامه هفتگی</a></li>
          <li><a href="extra.php">کلاس‌های فوق‌برنامه</a></li>
          <li><a href="monthly.php">تقویم آزمون‌ها</a></li>
          <li><a href="tops.php">معرفی نفرات برتر</a></li>
        </ul>
      </li>
      <li>
        <a href="#" data-toggle="submenu-2"><span>معرفی مدرسه</span><span class="caret" id="caret-2"></span></a>
        <ul class="submenu" id="submenu-2">
          <li><a href="history.php">تاریخچه</a></li>
          <li><a href="staff.php">کادر اجرایی</a></li>
          <li><a href="teachers.php">کادر آموزشی</a></li>
          <li><a href="honors.php">تالار افتخارات</a></li>
        </ul>
      </li>
      <li><a href="gallery.php">گالری تصاویر</a></li>
      <li><a href="learning.php">محتواهای آموزشی</a></li>
      <li><a href="services.php">خدمات به مدارس</a></li>
      <li><a href="news.php">اخبار توانش </a></li>
      <li><a href="contactus.php">تماس با ما</a></li>
    </ul>
  </aside>


    <h1 style="font-size:2.2rem; margin-bottom:24px; color:var(--turquoise-dark);">تاریخچه دبیرستان دخترانه توانش</h1>

    <!-- اینجا متن تاریخچه مدرسه را بنویسید -->
    <div style="font-size:1.15rem; line-height:2; text-align:justify;">

دریافت مجوز تاسیس دبیرستان دخترانه غیردولتی توانش به پاییز 1403 بر می گردد.
و پس از طی مراحل قانونی و دریافت مجوزهای مربوطه و آماده سازی مکان در نهایت
دبیرستان دخترانه توانش در مهر 1404 فعالیت رسمی خود را آغاز نموده است.
<br><br><br>
کادر اجرایی دبیرستان جهت بهره مندی و استفاده کامل از تجربیات چند ساله خود در تدریس
 و امور اجرایی و با هدف نوآوری در آموزش و ایجاد تحولی موثر در شهرستان ، اقدام به تاسیس دبیرستان دوره دوم نمودند

    </div>


<script>
  // همبرگر منو
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');
  hamburgerBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if(window.innerWidth <= 991 && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)){
      sidebar.classList.remove('open');
    }
  });

  // زیرمنوها
  document.querySelectorAll('[data-toggle]').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault(); e.stopPropagation();
      const submenuId = this.getAttribute('data-toggle');
      const submenu = document.getElementById(submenuId);
      const caret = document.getElementById('caret-' + submenuId.split('-')[1]);
      submenu.classList.toggle('open');
      caret.classList.toggle('open');
    });
  });
</script>





</main>
</div>
</body>
</html>