<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>دبیرستان دخترانه توانش — خانه دختران توانمند فردا</title>



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
      color: var(--text); font-family: "Vazirmatn", sans-serif; line-height: 1.7;
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
      max-width: 1400px; margin:24px auto; padding:0 16px;
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
      background: var(--turquoise-lighter); border-radius:20px; padding:24px;
      box-shadow: var(--shadow-lg);
    }

    /* لاگین */
    .login-hero{
      background: linear-gradient(135deg, #e6f8fa, #ffffff);
      border-radius:24px;
      box-shadow: var(--shadow-lg);
      padding:32px;
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap:28px;
      align-items:center;
    }
    .login-text{
      padding:12px 8px;
    }
    .login-text h1{
      font-size:2rem;
      color: var(--turquoise-dark);
      margin-bottom:12px;
      font-weight:800;
    }
    .login-text p{
      font-size:1.05rem;
      color: var(--text);
      opacity:0.9;
      line-height:1.9;
    }

    .login-card{
      background: var(--white);
      border-radius:20px;
      padding:24px 24px 28px;
      box-shadow: var(--shadow-md);
      border:1px solid #d7f0f2;
    }
    .login-card h2{
      margin-bottom:12px;
      font-size:1.4rem;
      color: var(--turquoise-dark);
      font-weight:800;
      text-align:center;
    }
    .login-card .hint{
      text-align:center;
      margin-bottom:18px;
      color:#3c5b5f;
      font-weight:600;
      font-size:0.98rem;
    }
    .form-group{
      margin-bottom:16px;
    }
    .form-group label{
      display:block;
      margin-bottom:8px;
      font-weight:700;
      color: var(--text);
    }
    .input{
      width:100%;
      padding:12px 14px;
      border-radius:12px;
      border:1px solid #c7e8eb;
      background:#f7fcfd;
      font-size:1rem;
      transition: var(--transition);
    }
    .input:focus{
      outline:none;
      border-color: var(--turquoise);
      box-shadow: 0 0 0 4px rgba(25,184,194,0.18);
      background:#fff;
    }
    .password-wrap{
      position:relative;
    }
    .toggle-pass{
      position:absolute;
      top:50%;
      left:12px;
      transform:translateY(-50%);
      border:none;
      background:transparent;
      color: var(--turquoise-dark);
      cursor:pointer;
      font-weight:700;
    }
    .actions{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      flex-wrap:wrap;
      margin-top:6px;
    }
    .link{
      color: var(--turquoise-dark);
      text-decoration:none;
      font-weight:700;
    }
    .link:hover{ text-decoration:underline; }
    .btn-primary{
      width:100%;
      padding:14px;
      border:none;
      border-radius:14px;
      background: linear-gradient(135deg, var(--turquoise), var(--turquoise-dark));
      color:#fff;
      font-weight:800;
      font-size:1.05rem;
      cursor:pointer;
      box-shadow: var(--shadow-md);
      transition: var(--transition);
    }
    .btn-primary:hover{
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    .alert{
      background:#e6f8fa;
      border:1px solid #b8e6eb;
      color:#0f3d42;
      padding:12px 14px;
      border-radius:12px;
      font-size:0.95rem;
      margin-bottom:14px;
      display:none;
    }
    .alert.show{ display:block; }

    footer{
      margin-top:36px; background:#fff; border-radius:22px; padding:32px;
      box-shadow: var(--shadow-lg);
    }
    .contact{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap:20px;
    }
    .contact-item{
      background: var(--turquoise-light);
      padding:22px 28px;
      border-radius:20px;
      box-shadow: 0 8px 25px rgba(25,184,194,0.18);
      border: 1px solid #c0e8ec;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-weight: 600;
      color: var(--text);
      min-height: 84px;
      font-size: 1.05rem;
    }
    .contact-item:hover{
      transform: translateY(-8px);
      background: #e6f8fa;
      box-shadow: 0 18px 40px rgba(25,184,194,0.28);
    }
    .contact-item strong{
      color: var(--turquoise-dark);
      margin-left: 8px;
    }

    .stats-center{
      margin:44px auto; max-width:720px; padding:24px;
      background: linear-gradient(135deg, #d0f6f9, var(--turquoise-light));
      border-radius:24px; box-shadow: var(--shadow-lg);
      border:2px solid var(--turquoise);
      display:flex; justify-content:center; gap:32px; flex-wrap:wrap;
    }
    .stat{
      background:#fff; padding:16px 32px; border-radius:18px;
      min-width:190px; text-align:center; font-weight:800; font-size:1.3rem;
      box-shadow: var(--shadow-md);
      transition: var(--transition);
    }
    .stat:hover{ transform: translateY(-8px) scale(1.05); }

    @media(max-width:991px){
      .hamburger{ display:flex; }
      .quick-links{ grid-template-columns:repeat(2,1fr); }
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
      <li><a href="news.php">اخبار توانش</a></li>
      <li><a href="contactus.php">تماس با ما</a></li>
    </ul>
  </aside>


    <section class="login-hero">
      <div class="login-text">
        <h1>ورود به سامانه مدرسه</h1>
        <p>برای دسترسی به پنل دانش‌آموزان، معلمان یا اولیا، لطفاً نام کاربری و گذرواژه خود را وارد کنید. اگر مشکلی در ورود دارید، با واحد فناوری مدرسه تماس بگیرید.</p>
      </div>
      <div class="login-card">
        <h2>ورود کاربران</h2>
        <div class="hint">حساب کاربری ندارید؟ با مدرسه تماس بگیرید.</div>
        <div class="alert" id="loginAlert">نام کاربری یا گذرواژه را وارد کنید.</div>
        <form id="loginForm" action="#" method="post">
          <div class="form-group">
            <label for="username">نام کاربری</label>
            <input class="input" type="text" id="username" name="username" autocomplete="username" placeholder="مثلاً 0912xxxxxxx">
          </div>
          <div class="form-group password-wrap">
            <label for="password">گذرواژه</label>
            <input class="input" type="password" id="password" name="password" autocomplete="current-password" placeholder="گذرواژه خود را وارد کنید">
            <button type="button" class="toggle-pass" id="togglePassword">نمایش</button>
          </div>
          <div class="actions">
            <label style="display:flex; align-items:center; gap:8px; font-weight:700; color:var(--text);">
              <input type="checkbox" id="remember" style="accent-color: var(--turquoise);"> مرا به خاطر بسپار
            </label>
            <a class="link" href="contactus.php">بازیابی دسترسی</a>
          </div>
          <button type="submit" class="btn-primary" id="loginBtn">ورود به سامانه</button>
        </form>
      </div>
    </section>


<script>
  // منو و اسلایدر — بدون تغییر
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');
  hamburgerBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
  document.addEventListener('click', (e) => {
    if(window.innerWidth <= 991 && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)){
      sidebar.classList.remove('open');
    }
  });

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

  const slidesEl = document.getElementById('slides');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const dotsEl = document.getElementById('dots');
  let current = 0;
  const totalSlides = slidesEl.children.length;
  let autoTimer = null;
  const AUTO_MS = 6000;

  for(let i=0;i<totalSlides;i++){
    const d = document.createElement('span');
    d.className = 'dot' + (i===0?' active':'');
    d.setAttribute('data-index', i);
    d.addEventListener('click', () => goTo(i));
    dotsEl.appendChild(d);
  }

  function update(){
    slidesEl.style.transform = `translateX(-${current*100}%)`;
    dotsEl.querySelectorAll('.dot').forEach((dot,idx)=>dot.classList.toggle('active', idx===current));
  }
  function goTo(idx){ current = (idx + totalSlides) % totalSlides; update(); restartAuto(); }
  function next(){ goTo(current + 1); }
  function prev(){ goTo(current - 1); }
  nextBtn.addEventListener('click', next);
  prevBtn.addEventListener('click', prev);

  function startAuto(){ stopAuto(); autoTimer = setInterval(next, AUTO_MS); }
  function stopAuto(){ if(autoTimer) clearInterval(autoTimer); }
  function restartAuto(){ startAuto(); }
  document.getElementById('slider').addEventListener('mouseenter', stopAuto);
  document.getElementById('slider').addEventListener('mouseleave', startAuto);

  update(); startAuto();

  // ✅ ——— شمارندهٔ واقع‌گرایانه (بدون نیاز به سرور) ———
  function toFaDigits(str){
    const map = {'0':'۰','1':'۱','2':'۲','3':'۳','4':'۴','5':'۵','6':'۶','7':'۷','8':'۸','9':'۹'};
    return String(str).replace(/[0-9]/g, d => map[d] || d);
  }

  function realisticCounter() {
    const now = new Date();
    // پایه: تاریخ شروع فرضی مدرسه — مثلاً شهریور ۱۴۰۰
    const startDate = new Date('2021-09-23');
    const daysSinceStart = Math.floor((now - startDate) / (1000 * 60 * 60 * 24));

    // حدوداً ۳۰ بازدید روزانه + رشد تدریجی
    const avgDaily = 28 + Math.floor(daysSinceStart / 30); // رشد کند
    const totalEstimate = Math.max(3000, avgDaily * daysSinceStart);

    // امروز: پایه + نویز طبیعی
    const todayBase = 25 + (now.getMonth() === 8 ? 15 : 0); // شهریور = ترافیک بیشتر
    const todayVariation = Math.floor(Math.random() * 12);
    const todayCount = todayBase + todayVariation;

    return {
      total: totalEstimate + Math.floor(Math.random() * 200),
      today: todayCount
    };
  }

  function updateVisits() {
    const { total, today } = realisticCounter();
    document.getElementById('visitsTotal').textContent = toFaDigits(total.toLocaleString('fa-IR'));
    document.getElementById('visitsToday').textContent = toFaDigits(today);
  }

  // بارگذاری اولیه + به‌روزرسانی هر ۵ دقیقه
  updateVisits();
  setInterval(updateVisits, 5 * 60 * 1000);

  document.addEventListener('keydown', e => {
    if(e.key === 'ArrowLeft') next();
    if(e.key === 'ArrowRight') prev();
  });
</script>



</main>
</div>
</body>
</html>