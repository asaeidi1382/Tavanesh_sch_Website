<?php
require_once 'auth.php';
$isLoggedIn = isLoggedIn();
$fullName = '';
$profile_image = null;

if ($isLoggedIn) {
    $fullName = $_SESSION['full_name'] ?? $_SESSION['username'];
    $db = getDB();
    $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile_image = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>دبیرستان دخترانه توانش — خانه دختران توانمند فردا</title>

  <?php include 'header_styles.php'; ?>
  <style>
    /* اسلایدر */
    .slider {
      direction: ltr !important;
      position: relative;
      overflow: hidden;
      border-radius: 24px;
      box-shadow: var(--shadow-lg);
      aspect-ratio: 16 / 9;
      background: var(--white);
      max-width: 720px;
      margin: 0 auto;
    }
    .slides {
      display: flex;
      width: 100%;
      height: 100%;
      transition: transform 0.7s cubic-bezier(0.22, 0.61, 0.36, 1);
      direction: ltr;
    }
    .slide {
      flex: 0 0 100%;
      max-width: 100%;
      height: 100%;
      direction: ltr;
    }
    .slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .arrow{
      position:absolute; top:50%; transform:translateY(-50%);
      width:64px; height:64px; background: linear-gradient(135deg, #ffffff, #f0fbfd);
      border-radius:50%; display:flex; align-items:center; justify-content:center;
      font-size:34px; font-weight:bold; color:var(--turquoise);
      cursor:pointer; z-index:10;
      box-shadow: 0 12px 35px rgba(0,0,0,0.25), inset 0 4px 10px rgba(255,255,255,0.8);
      backdrop-filter: blur(12px);
      border: 2px solid rgba(25,184,194,0.4);
      transition: var(--transition);
    }
    .arrow:hover{
      transform: translateY(-50%) scale(1.18);
      background: linear-gradient(135deg, #ffffff, #e6f8fa);
      box-shadow: 0 25px 55px rgba(0,0,0,0.35);
    }
    .arrow.prev{ left:24px; }
    .arrow.next{ right:24px; }
    .dots{
      position:absolute; bottom:18px; left:50%; transform:translateX(-50%);
      display:flex; gap:12px; background:rgba(255,255,255,0.9); padding:10px 18px; border-radius:40px;
      box-shadow: var(--shadow-md);
    }
    .dot{
      width:12px; height:12px; border-radius:50%; background:#ccc; cursor:pointer; transition: var(--transition);
    }
    .dot.active{ background:var(--turquoise-dark); transform:scale(1.4); }

    .quick-links{
      display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-top:28px;
    }
    .quick-link, .quick-linkb{
      background: var(--turquoise); color:#fff; text-align:center; padding:24px 16px;
      border-radius:20px; text-decoration:none; font-weight:700; font-size:1.15rem;
      box-shadow: var(--shadow-md); transition: var(--transition);
    }
    .quick-linkb { background: #f06292; }
    .quick-link:hover, .quick-linkb:hover{
      transform: translateY(-12px);
      box-shadow: 0 25px 50px rgba(25,184,194,0.4);
    }

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
      flex-direction: column;
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
      margin-bottom: 4px;
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
      .quick-links{ grid-template-columns:repeat(2,1fr); }
    }
  </style>

  <!-- Favicon -->
  <link rel="icon" href="images/logo-T.png" type="image/png" sizes="48x48">
  <link rel="icon" href="images/logo-T.png" type="image/png" sizes="96x96">
  <link rel="icon" href="images/logo-T.png" type="image/png" sizes="192x192">
  <link rel="apple-touch-icon" href="images/logo-T.png">
</head>
<body>

<?php include 'topbar.php'; ?>

<div class="layout">
  <?php include 'sidebar.php'; ?>
  <main class="content">
    <section class="slider" id="slider">
      <div class="slides" id="slides">
        <div class="slide"><img src="Images/slider/slide01.jpg" alt="اسلاید ۱"></div>
        <div class="slide"><img src="Images/slider/slide02.jpg" alt="اسلاید ۲"></div>
        <div class="slide"><img src="Images/slider/slide03.jpg" alt="اسلاید ۳"></div>
        <div class="slide"><img src="Images/slider/slide04.jpg" alt="اسلاید ۴"></div>
        <div class="slide"><img src="Images/slider/slide05.jpg" alt="اسلاید ۵"></div>
        <div class="slide"><img src="Images/slider/slide06.jpg" alt="اسلاید 6"></div>
      </div>
      <button class="arrow prev" id="prevBtn">&lt;</button>
      <button class="arrow next" id="nextBtn">&gt;</button>
      <div class="dots" id="dots"></div>
    </section>

    <section class="quick-links">
      <a class="quick-linkb" href="vlearning.php">مدرسه مجازی</a>
      <a class="quick-link" href="news.php">اخبار توانش</a>
      <a class="quick-link" href="learning.php">محتواهای آموزشی</a>
      <a class="quick-link" href="teachers.php">کادر آموزشی</a>
    </section>

    <footer>
      <div class="contact">
        <div class="contact-item"><strong>نشانی:</strong> زرند شهرک زیتون لاله ۳</div>
        <div class="contact-item"><strong>تلفن:</strong> ۰۳۴۳۳۴۰۱۵۲۰</div>
        <div class="contact-item"><strong>ایمیل:</strong> modir@tavanesh-sch.ir</div>
        <div class="contact-item">
          <strong>تماس با موسس:</strong>
          <span>۰۹۱۳۸۴۴۱۰۷۵ (سرکار خانم میرزایی)</span>
        </div>
        <div class="contact-item">
          <strong>تماس با مدیر:</strong>
          <span>۰۹۱۳۲۴۱۲۶۹۶ (سرکار خانم سعیدی)</span>
        </div>
      </div>
    </footer>

    <section class="stats-center">
      <div class="stat">بازدید امروز: <span id="visitsToday">در حال بارگذاری…</span></div>
      <div class="stat">کل بازدیدها: <span id="visitsTotal">در حال بارگذاری…</span></div>
    </section>
  </main>
</div>

<script>
  const slidesEl = document.getElementById('slides');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const dotsEl = document.getElementById('dots');
  let current = 0;
  const totalSlides = slidesEl.children.length;
  let autoTimer = null;
  const AUTO_MS = 6000;

  dotsEl.innerHTML = '';
  for(let i=0;i<totalSlides;i++){
    const d = document.createElement('span');
    d.className = 'dot' + (i===0?' active':'');
    d.setAttribute('data-index', i);
    d.addEventListener('click', () => goTo(i));
    dotsEl.appendChild(d);
  }

  function update(){
    const offset = current * 100;
    slidesEl.style.transform = `translateX(-${offset}%)`;
    dotsEl.querySelectorAll('.dot').forEach((dot,idx)=>dot.classList.toggle('active', idx===current));
  }
  function goTo(idx){ current = (idx + totalSlides) % totalSlides; update(); restartAuto(); }
  function next(){ goTo(current + 1); }
  function prev(){ goTo(current - 1); }
  if (nextBtn) nextBtn.addEventListener('click', next);
  if (prevBtn) prevBtn.addEventListener('click', prev);

  function startAuto(){ stopAuto(); autoTimer = setInterval(next, AUTO_MS); }
  function stopAuto(){ if(autoTimer) clearInterval(autoTimer); }
  function restartAuto(){ startAuto(); }
  const sliderEl = document.getElementById('slider');
  if (sliderEl) {
    sliderEl.addEventListener('mouseenter', stopAuto);
    sliderEl.addEventListener('mouseleave', startAuto);
  }

  update(); startAuto();

  function toFaDigits(str){
    const map = {'0':'۰','1':'۱','2':'۲','3':'۳','4':'۴','5':'۵','6':'۶','7':'۷','8':'۸','9':'۹'};
    return String(str).replace(/[0-9]/g, d => map[d] || d);
  }

  function loadVisits() {
    fetch('counter.php', { cache: 'no-store' })
      .then(res => res.json())
      .then(data => {
        if (!data) return;
        const total = data.total ?? 0;
        const today = data.today ?? 0;

        const totalEl = document.getElementById('visitsTotal');
        const todayEl = document.getElementById('visitsToday');
        if (totalEl) totalEl.textContent = toFaDigits(total.toLocaleString('fa-IR'));
        if (todayEl) todayEl.textContent = toFaDigits(today);
      })
      .catch(() => {
        const totalEl = document.getElementById('visitsTotal');
        const todayEl = document.getElementById('visitsToday');
        if (totalEl) totalEl.textContent = '—';
        if (todayEl) todayEl.textContent = '—';
      });
  }

  loadVisits();
  setInterval(loadVisits, 5 * 60 * 1000);

  document.addEventListener('keydown', e => {
    if(e.key === 'ArrowLeft') next();
    if(e.key === 'ArrowRight') prev();
  });
</script>
</body>
</html>
