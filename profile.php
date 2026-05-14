<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$username = $_SESSION['username'];

$stmt = $db->prepare("SELECT u.*, p.* FROM users u LEFT JOIN student_profiles p ON u.username = p.national_id WHERE u.username = ?");
$stmt->execute([$username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$fullName = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پروفایل من — دبیرستان توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Light.woff2') format('woff2'); font-weight:300; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Medium.woff2') format('woff2'); font-weight:500; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight:700; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-ExtraBold.woff2') format('woff2'); font-weight:800; font-display:swap; }

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

:root {
  --turquoise:        #19b8c2;
  --turquoise-dark:   #0c8790;
  --turquoise-light:  #e6f8fa;
  --turquoise-lighter:#f0fbfd;
  --text:             #0f3d42;
  --gray:             #4e8a90;
  --shadow-sm:        0 4px 15px rgba(0,0,0,.08);
  --shadow-md:        0 12px 30px rgba(0,0,0,.12);
}

body { min-height:100vh; background:linear-gradient(to bottom, #f5fbfd, #ffffff); font-family:'Vazirmatn', sans-serif; color:var(--text); line-height:1.7; }

.topbar { background:var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000; box-shadow:var(--shadow-md); }
.topbar-inner { max-width:1100px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
.brand { display:flex; align-items:center; gap:12px; }
.brand-logo { width:52px; height:52px; border-radius:12px; overflow:hidden; background:rgba(255,255,255,.2); }
.brand-logo img { width:100%; height:100%; object-fit:contain; }
.brand-title { font-size:1.2rem; font-weight:800; }
.btn-back { padding:8px 16px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); border-radius:10px; font-family:'Vazirmatn',sans-serif; font-size:.85rem; font-weight:700; color:#fff; text-decoration:none; }

main { max-width:900px; margin:32px auto; padding:0 20px 60px; }
.card { background:#fff; border:1.5px solid var(--turquoise-light); border-radius:20px; padding:32px; box-shadow:var(--shadow-sm); }
.card h2 { font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--turquoise-dark); border-bottom:2px solid var(--turquoise-light); padding-bottom:12px; }

.profile-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; }
.info-item { margin-bottom:15px; }
.info-label { display:block; font-size:.8rem; color:var(--gray); font-weight:700; margin-bottom:4px; }
.info-value { display:block; font-size:1rem; font-weight:500; background:var(--turquoise-lighter); padding:10px 14px; border-radius:10px; border:1px solid #e0f2f4; }

@media(max-width:600px) {
  .profile-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="brand-logo"><img src="/images/logo-Tw.png" alt="لوگو"></div>
      <div class="brand-title">پروفایل دانش‌آموز</div>
    </div>
    <a href="dashboard.php" class="btn-back">→ بازگشت</a>
  </div>
</header>

<main>
  <div class="card">
    <h2>👤 اطلاعات شناسایی و تحصیلی</h2>
    <div class="profile-grid">
      <div class="info-item"><span class="info-label">نام و نام خانوادگی</span><span class="info-value"><?= htmlspecialchars($profile['full_name']) ?></span></div>
      <div class="info-item"><span class="info-label">نام</span><span class="info-value"><?= htmlspecialchars($profile['first_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">نام خانوادگی</span><span class="info-value"><?= htmlspecialchars($profile['last_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">کد ملی (نام کاربری)</span><span class="info-value"><?= htmlspecialchars($profile['username']) ?></span></div>
      <div class="info-item"><span class="info-label">پایه تحصیلی</span><span class="info-value"><?= htmlspecialchars($profile['grade'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">رشته تحصیلی</span><span class="info-value"><?= htmlspecialchars($profile['major'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">شماره صندلی</span><span class="info-value"><?= htmlspecialchars($profile['seat_no'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">وضعیت دست</span><span class="info-value"><?= ($profile['left_handed']??0) ? 'چپ دست' : 'راست دست' ?></span></div>
    </div>

    <h2 style="margin-top:40px;">👨‍👩‍👧‍👦 اطلاعات خانواده و تماس</h2>
    <div class="profile-grid">
      <div class="info-item"><span class="info-label">نام پدر</span><span class="info-value"><?= htmlspecialchars($profile['father_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">نام و نام خانوادگی مادر</span><span class="info-value"><?= htmlspecialchars($profile['mother_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">تلفن پدر</span><span class="info-value"><?= htmlspecialchars($profile['father_phone'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">تلفن مادر</span><span class="info-value"><?= htmlspecialchars($profile['mother_phone'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">تلفن همراه دانش‌آموز</span><span class="info-value"><?= htmlspecialchars($profile['student_phone'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">تلفن منزل</span><span class="info-value"><?= htmlspecialchars($profile['home_phone'] ?: '—') ?></span></div>
      <div class="info-item" style="grid-column: span 2;"><span class="info-label">آدرس منزل</span><span class="info-value"><?= htmlspecialchars($profile['address'] ?: '—') ?></span></div>
    </div>
  </div>
</main>
</body>
</html>
