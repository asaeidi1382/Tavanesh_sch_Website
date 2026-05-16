<?php
require_once 'auth.php';

// اگر ادمین است که دسترسی دارد، اگر دانش‌آموز است فقط به پروفایل خودش
if (isset($_SESSION['is_admin'])) {
    $db = getDB();
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $national_id = $stmt->fetchColumn();
    } else {
        $national_id = $_GET['username'] ?? '';
    }
} else {
    requireLogin();
    $national_id = $_SESSION['username'];
}

$academic_year = $_GET['year'] ?? ($_SESSION['active_year'] ?? '1404-1405');
$db = getDB();

$stmt = $db->prepare("SELECT sp.*, u.profile_image
                      FROM student_profiles sp
                      JOIN users u ON sp.national_id = u.username
                      WHERE sp.national_id = ? AND sp.academic_year = ?");
$stmt->execute([$national_id, $academic_year]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('دانش‌آموز پیدا نشد.');
}

$display_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: $student['national_id'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پروفایل دانش‌آموز — <?= htmlspecialchars($display_name) ?></title>
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
.card h2 { font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--turquoise-dark); border-bottom:2px solid var(--turquoise-light); padding-bottom:12px; display:flex; align-items:center; gap:10px; }

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
    <a href="<?= isset($_SESSION['is_admin']) ? 'admin.php?tab=students' : 'dashboard.php' ?>" class="btn-back">→ بازگشت</a>
  </div>
</header>

<main>
  <div class="card">
    <div style="margin-bottom:30px; text-align:center;">
        <div style="width:120px; height:120px; border-radius:50%; overflow:hidden; margin:0 auto 20px; border:4px solid var(--turquoise-light); box-shadow:var(--shadow-md); background:#fff; display:flex; align-items:center; justify-content:center; font-size:60px;">
            <?php if (!empty($student['profile_image'])): ?>
                <img src="<?= htmlspecialchars($student['profile_image']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
        <h1 style="font-size:2rem; font-weight:800; color:var(--turquoise-dark); margin-bottom:5px;"><?= to_persian_num(htmlspecialchars($display_name)) ?></h1>
        <p style="color:var(--gray);">سال تحصیلی: <?= to_persian_num(htmlspecialchars($academic_year)) ?></p>
    </div>

    <h2>👤 اطلاعات شناسایی و تحصیلی</h2>
    <div class="profile-grid">
      <div class="info-item"><span class="info-label">نام</span><span class="info-value"><?= htmlspecialchars($student['first_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">نام خانوادگی</span><span class="info-value"><?= htmlspecialchars($student['last_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">کد ملی (نام کاربری)</span><span class="info-value"><?= to_persian_num(htmlspecialchars($student['national_id'])) ?></span></div>
      <div class="info-item"><span class="info-label">پایه تحصیلی</span><span class="info-value"><?= htmlspecialchars($student['grade'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">رشته تحصیلی</span><span class="info-value"><?= htmlspecialchars($student['major'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">وضعیت دست</span><span class="info-value"><?= ($student['left_handed']??0) ? 'چپ دست' : 'راست دست' ?></span></div>
    </div>

    <h2 style="margin-top:40px;">👨‍👩‍👧‍👦 اطلاعات خانواده و تماس</h2>
    <div class="profile-grid">
      <div class="info-item"><span class="info-label">نام پدر</span><span class="info-value"><?= htmlspecialchars($student['father_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">نام مادر</span><span class="info-value"><?= htmlspecialchars($student['mother_name'] ?: '—') ?></span></div>
      <div class="info-item"><span class="info-label">تلفن پدر</span><span class="info-value"><?= to_persian_num(htmlspecialchars($student['father_phone'] ?: '—')) ?></span></div>
      <div class="info-item"><span class="info-label">تلفن مادر</span><span class="info-value"><?= to_persian_num(htmlspecialchars($student['mother_phone'] ?: '—')) ?></span></div>
      <div class="info-item"><span class="info-label">تلفن همراه دانش‌آموز</span><span class="info-value"><?= to_persian_num(htmlspecialchars($student['student_phone'] ?: '—')) ?></span></div>
      <div class="info-item"><span class="info-label">تلفن ثابت منزل</span><span class="info-value"><?= to_persian_num(htmlspecialchars($student['home_phone'] ?: '—')) ?></span></div>
      <div class="info-item" style="grid-column: span 2;"><span class="info-label">آدرس منزل</span><span class="info-value"><?= nl2br(htmlspecialchars($student['address'] ?: '—')) ?></span></div>
    </div>
  </div>
</main>
</body>
</html>
