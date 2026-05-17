<?php

require_once 'auth_new.php';

requireAdmin();

$adminName = $_SESSION['admin_name'] ?? 'مدیریت';

?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>پنل مدیریت — توانش</title>

<link rel="icon"
      href="/images/logo-T.png"
      type="image/png">

<style>









*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

:root {

  --turquoise:#19b8c2;
  --turquoise-dark:#0c8790;
  --turquoise-light:#e6f8fa;
  --turquoise-lighter:#f0fbfd;

  --text:#0f3d42;
  --gray:#4e8a90;

  --shadow-sm:0 4px 15px rgba(0,0,0,.08);
  --shadow-md:0 12px 30px rgba(0,0,0,.12);

  --transition:all .35s cubic-bezier(.4,0,.2,1);
}

body {

  min-height:100vh;

  background:linear-gradient(to bottom,#f5fbfd,#fff);

  font-family:'Vazirmatn',sans-serif;

  color:var(--text);
}

/* topbar */

.topbar {

  background:var(--turquoise);

  color:#fff;

  position:sticky;

  top:0;

  z-index:1000;

  box-shadow:var(--shadow-md);
}

.topbar-inner {

  max-width:1200px;

  margin:auto;

  padding:14px 20px;

  display:flex;

  align-items:center;

  justify-content:space-between;
}

.brand {

  display:flex;

  align-items:center;

  gap:14px;
}

.brand-logo {

  width:56px;
  height:56px;

  border-radius:14px;

  overflow:hidden;

  background:rgba(255,255,255,.12);
}

.brand-logo img {

  width:100%;
  height:100%;

  object-fit:contain;
}

.brand-title {

  font-size:1.35rem;

  font-weight:800;
}

.brand-sub {

  font-size:.82rem;

  opacity:.9;
}

.user-box {

  background:rgba(255,255,255,.15);

  border:1px solid rgba(255,255,255,.25);

  border-radius:12px;

  padding:10px 16px;

  font-size:.85rem;

  font-weight:700;
}

/* content */

main {

  max-width:1200px;

  margin:auto;

  padding:40px 20px 70px;
}

.welcome {

  background:linear-gradient(
      135deg,
      #d0f6f9,
      var(--turquoise-light)
  );

  border:2px solid var(--turquoise);

  border-radius:22px;

  padding:30px;

  margin-bottom:35px;

  box-shadow:var(--shadow-md);
}

.welcome h1 {

  font-size:1.7rem;

  font-weight:800;

  margin-bottom:8px;
}

.welcome span {

  color:var(--turquoise-dark);
}

.welcome p {

  color:var(--gray);

  font-size:.92rem;
}

.section-title {

  font-size:1.1rem;

  font-weight:800;

  margin-bottom:18px;

  display:flex;

  align-items:center;

  gap:10px;
}

.section-title::before {

  content:'';

  width:5px;

  height:20px;

  border-radius:3px;

  background:var(--turquoise);
}

.grid {

  display:grid;

  grid-template-columns:
      repeat(auto-fit,minmax(240px,1fr));

  gap:20px;
}

.card {

  background:#fff;

  border:1.5px solid var(--turquoise-light);

  border-radius:20px;

  padding:26px 22px;

  box-shadow:var(--shadow-sm);

  text-decoration:none;

  color:inherit;

  transition:var(--transition);
}

.card:hover {

  transform:translateY(-4px);

  box-shadow:var(--shadow-md);

  border-color:var(--turquoise);
}

.card-icon {

  font-size:2.2rem;

  margin-bottom:14px;
}

.card h3 {

  font-size:1rem;

  margin-bottom:8px;

  font-weight:800;
}

.card p {

  color:var(--gray);

  font-size:.85rem;

  line-height:1.8;
}

.card.featured {

  background:linear-gradient(
      135deg,
      var(--turquoise),
      var(--turquoise-dark)
  );

  color:#fff;

  border:none;
}

.card.featured p {

  color:rgba(255,255,255,.85);
}

</style>

  <?php include 'header_styles.php'; ?>
</head>

<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">















  <div class="welcome">

    <h1>

      سلام <span><?= htmlspecialchars($adminName) ?></span> 👋

    </h1>

    <p>

      به سامانه مدیریت دبیرستان توانش خوش آمدید.

    </p>

  </div>

  <div class="section-title">

    مدیریت سامانه

  </div>

  <div class="grid">

    <a href="students.php"
       class="card featured">

      <div class="card-icon">👩‍🎓</div>

      <h3>مدیریت دانش‌آموزان</h3>

      <p>
        ثبت، ویرایش، جستجو و مدیریت اطلاعات دانش‌آموزان
      </p>

    </a>

    <a href="financial.php"
       class="card">

      <div class="card-icon">💳</div>

      <h3>شهریه و اقساط</h3>

      <p>
        مدیریت شهریه، اقساط و پرداخت‌ها
      </p>

    </a>

    <a href="#"
       class="card">

      <div class="card-icon">📊</div>

      <h3>گزارش مالی</h3>

      <p>
        مشاهده وضعیت مالی و بدهکاران
      </p>

    </a>

    <a href="#"
       class="card">

      <div class="card-icon">⚙️</div>

      <h3>تنظیمات سامانه</h3>

      <p>
        تنظیمات مدیریتی و اطلاعات مدرسه
      </p>

    </a>

  </div>






</main>
</div>
</body>
</html>