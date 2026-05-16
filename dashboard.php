<?php
require_once 'auth.php';
requireLogin();
$fullName = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>داشبورد — دبیرستان دخترانه توانش</title>
<style>
.placeholder-msg { padding: 40px; text-align: center; background: #fff; border-radius: 20px; border: 1.5px dashed var(--turquoise); margin-top: 20px; }
.placeholder-msg h2 { color: var(--turquoise-dark); margin-bottom: 10px; }

/* استایل دکمه‌های عملیات (فیش حقوقی و کارنامه) */
.btn-action { padding:6px 12px; border-radius:8px; font-size:.8rem; font-weight:700; text-decoration:none; display:inline-block; margin-left:5px; transition: 0.2s; }
.btn-view { background:var(--turquoise-light); color:var(--turquoise-dark); }
.btn-view:hover { background:var(--turquoise-dark); color:#fff; }
.btn-download { background:var(--turquoise); color:#fff; }
.btn-download:hover { background:var(--turquoise-dark); }
.btn-disabled { background:#800000 !important; color:#fff !important; cursor:not-allowed; opacity: 0.8; }
</style>
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
  --shadow-lg:        0 20px 50px rgba(0,0,0,.18);
  --transition:       all 0.4s cubic-bezier(0.4,0,0.2,1);
}

body {
  min-height:100vh;
  background:linear-gradient(to bottom, #f5fbfd, #ffffff);
  font-family:'Vazirmatn', sans-serif;
  color:var(--text);
  line-height:1.7;
}

/* ───── نوار بالا ───── */
.topbar { background:var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000; box-shadow:var(--shadow-md); }
.topbar-inner { max-width:1200px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
.brand { display:flex; align-items:center; gap:14px; }
.brand-logo { width:58px; height:58px; border-radius:14px; overflow:hidden; background:rgba(255,255,255,.2); box-shadow:0 6px 20px rgba(0,0,0,.2); transition:var(--transition); }
.brand-logo:hover { transform:translateY(-3px); }
.brand-logo img { width:100%; height:100%; object-fit:contain; }
.brand-title { font-size:1.5rem; font-weight:800; }
.brand-sub   { font-size:.9rem; opacity:.9; font-weight:300; }
.topbar-left { display:flex; align-items:center; gap:12px; }
.user-badge  { background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.3); border-radius:12px; padding:8px 16px; font-size:.85rem; font-weight:500; }
.btn-logout  { padding:9px 18px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); border-radius:12px; font-family:'Vazirmatn',sans-serif; font-size:.85rem; font-weight:700; color:#fff; cursor:pointer; text-decoration:none; transition:var(--transition); }
.btn-logout:hover { background:rgba(255,255,255,.28); transform:translateY(-2px); }

/* ───── محتوا ───── */
main { max-width:1000px; margin:0 auto; padding:40px 20px 60px; animation:fadeIn .4s ease both; }
@keyframes fadeIn { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

/* خوش‌آمدگویی */
.welcome { background:linear-gradient(135deg,#d0f6f9,var(--turquoise-light)); border:2px solid var(--turquoise); border-radius:20px; padding:28px 32px; margin-bottom:36px; box-shadow:var(--shadow-md); }
.welcome h1 { font-size:1.6rem; font-weight:800; margin-bottom:6px; }
.welcome h1 span { color:var(--turquoise-dark); }
.welcome p  { font-size:.9rem; color:var(--gray); font-weight:300; }

/* عنوان بخش */
.section-title { font-size:1.1rem; font-weight:800; margin:0 0 18px; display:flex; align-items:center; gap:10px; }
.section-title::before { content:''; width:5px; height:20px; background:var(--turquoise); border-radius:3px; flex-shrink:0; }

/* گرید کارت‌ها */
.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:18px; }
.card { background:#fff; border:1.5px solid var(--turquoise-light); border-radius:18px; padding:24px 20px; box-shadow:var(--shadow-sm); transition:var(--transition); text-decoration:none; color:inherit; display:block; }
.card:hover { transform:translateY(-4px) scale(1.02); box-shadow:var(--shadow-md); border-color:var(--turquoise); }
.card-icon { font-size:2rem; margin-bottom:12px; }
.card h3   { font-size:1rem; font-weight:700; margin-bottom:5px; }
.card p    { font-size:.82rem; color:var(--gray); font-weight:300; line-height:1.6; }

/* کارت اقساط — برجسته */
.card.featured { background:linear-gradient(135deg,var(--turquoise),var(--turquoise-dark)); color:#fff; border-color:transparent; }
.card.featured h3, .card.featured p { color:rgba(255,255,255,.95); }
.card.featured p  { color:rgba(255,255,255,.8); }
.card.featured:hover { transform:translateY(-4px) scale(1.03); box-shadow:0 16px 40px rgba(25,184,194,.45); }

.back-link { display:inline-flex; align-items:center; gap:6px; margin-top:36px; color:var(--turquoise-dark); text-decoration:none; font-size:.9rem; font-weight:700; transition:color .2s; }
.back-link:hover { color:var(--turquoise); }
</style>
</head>
<body>
<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="brand-logo"><img src="/images/logo-Tw.png" alt="لوگو توانش"></div>
      <div>
        <div class="brand-title">دبیرستان دخترانه توانش</div>
        <div class="brand-sub">پرتال <?= $_SESSION['role'] === 'staff' ? 'کارکنان' : 'دانش‌آموزی' ?></div>
      </div>
    </div>
    <div class="topbar-left">
      <div class="user-badge">👤 <?= to_persian_num(htmlspecialchars($fullName)) ?></div>
      <a href="logout.php" class="btn-logout">خروج ←</a>
    </div>
  </div>
</header>

<main>
  <?php if (isset($_GET['page']) && $_GET['page'] === 'paystub'):
      require_once 'auth.php';
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM paystubs WHERE national_id = ? ORDER BY upload_date DESC");
      $stmt->execute([$_SESSION['username']]);
      $stubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
    <div class="welcome">
        <h1>💵 مشاهده فیش حقوقی</h1>
        <p>لیست فیش‌های حقوقی صادر شده برای شما</p>
    </div>
    <div class="card">
        <?php if ($stubs): ?>
            <div class="table-wrap">
                <style>
                    .table-wrap { overflow-x:auto; border-radius:14px; border:1.5px solid var(--turquoise-light); }
                    table { width:100%; border-collapse:collapse; }
                    thead th { background:var(--turquoise-lighter); padding:12px 14px; font-size:.85rem; font-weight:700; color:var(--turquoise-dark); text-align:right; border-bottom:1.5px solid var(--turquoise-light); }
                    tbody tr { border-bottom:1px solid #edf6f8; transition: background .2s; }
                    tbody tr:hover { background:var(--turquoise-lighter); }
                    tbody td { padding:14px; font-size:.92rem; }
                </style>
                <table>
                    <thead>
                        <tr>
                            <th>عنوان فیش</th>
                            <th>تاریخ آپلود</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stubs as $stub): ?>
                        <tr>
                            <td><?= htmlspecialchars($stub['title']) ?></td>
                            <td><?= to_persian_num(convert_to_jalali($stub['upload_date'])) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($stub['file_path']) ?>" target="_blank" class="btn-action btn-view">👁️ مشاهده</a>
                                <a href="<?= htmlspecialchars($stub['file_path']) ?>" download class="btn-action btn-download">📥 دانلود</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="placeholder-msg">
                <h2>فیش حقوقی یافت نشد</h2>
                <p>هنوز هیچ فیش حقوقی برای شما در سیستم ثبت نشده است.</p>
            </div>
        <?php endif; ?>
        <div style="margin-top:30px; text-align:center;">
            <a href="dashboard.php" class="btn-logout" style="background:var(--turquoise-dark); border:none;">← بازگشت به داشبورد</a>
        </div>
    </div>

  <?php elseif (isset($_GET['page']) && $_GET['page'] === 'report_card'):
      require_once 'auth.php';
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM report_cards WHERE national_id = ? ORDER BY upload_date DESC");
      $stmt->execute([$_SESSION['username']]);
      $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
    <div class="welcome">
        <h1>📜 مشاهده کارنامه</h1>
        <p>لیست کارنامه‌های صادر شده برای شما</p>
    </div>
    <div class="card">
        <?php if ($reports): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>عنوان کارنامه</th>
                            <th>تاریخ آپلود</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $rc): ?>
                        <tr>
                            <td><?= htmlspecialchars($rc['title']) ?></td>
                            <td><?= to_persian_num(convert_to_jalali($rc['upload_date'])) ?></td>
                            <td>
                                <style>
                                    .report-actions { display:flex; flex-direction:column; gap:4px; }
                                    .report-buttons { display:flex; gap:5px; }
                                    .contact-msg { color:#800000; font-weight:800; font-size:1.2rem; margin-top:6px; }
                                </style>
                                <div class="report-actions">
                                    <div class="report-buttons">
                                        <?php if ($rc['is_visible']): ?>
                                            <a href="<?= htmlspecialchars($rc['file_path']) ?>" target="_blank" class="btn-action btn-view">👁️ مشاهده</a>
                                            <a href="<?= htmlspecialchars($rc['file_path']) ?>" download class="btn-action btn-download">📥 دانلود</a>
                                        <?php else: ?>
                                            <span class="btn-action btn-disabled">👁️ مشاهده</span>
                                            <span class="btn-action btn-disabled">📥 دانلود</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$rc['is_visible']): ?>
                                        <div class="contact-msg">لطفاً با مدرسه تماس بگیرید</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="placeholder-msg">
                <h2>کارنامه‌ای یافت نشد</h2>
                <p>هنوز هیچ کارنامه‌ای برای شما در سیستم ثبت نشده است.</p>
            </div>
        <?php endif; ?>
        <div style="margin-top:30px; text-align:center;">
            <a href="dashboard.php" class="btn-logout" style="background:var(--turquoise-dark); border:none;">← بازگشت به داشبورد</a>
        </div>
    </div>

  <?php elseif (isset($_GET['page'])): ?>
    <div class="placeholder-msg">
        <h2>این بخش بعدا تکمیل خواهد شد</h2>
        <p>در حال حاضر این صفحه در دسترس نیست.</p>
        <a href="dashboard.php" class="back-link">← بازگشت به داشبورد</a>
    </div>
  <?php elseif (isset($_GET['personal_info'])):
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE national_id = ? ORDER BY academic_year DESC LIMIT 1");
      $stmt->execute([$_SESSION['username']]);
      $prof = $stmt->fetch(PDO::FETCH_ASSOC);
  ?>
    <style>
      .profile-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px; }
      .info-item { margin-bottom:15px; }
      .info-label { display:block; font-size:.8rem; color:var(--gray); font-weight:700; margin-bottom:4px; }
      .info-value { display:block; font-size:1rem; font-weight:500; background:var(--turquoise-lighter); padding:10px 14px; border-radius:10px; border:1px solid #e0f2f4; overflow-wrap: break-word; }
      .card h2 { font-size:1.4rem; font-weight:800; margin-bottom:24px; color:var(--turquoise-dark); border-bottom:2px solid var(--turquoise-light); padding-bottom:12px; display:flex; align-items:center; gap:10px; }
      @media (max-width: 600px) {
        .profile-grid { grid-template-columns: 1fr; gap: 10px; }
        .info-item { margin-bottom: 10px; }
        .info-value { font-size: 0.9rem; padding: 8px 12px; }
        .info-item[style*="grid-column: span 2;"] { grid-column: span 1 !important; }
      }
    </style>
    <div class="welcome">
        <h1>اطلاعات پرسنلی</h1>
        <p>مشخصات ثبت شده شما در سیستم</p>
    </div>
    <div class="card">
        <?php if ($prof): ?>
            <h2>👤 مشخصات فردی و شغلی</h2>
            <div class="profile-grid">
                <div class="info-item"><span class="info-label">نام</span><span class="info-value"><?= htmlspecialchars($prof['first_name'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">نام خانوادگی</span><span class="info-value"><?= htmlspecialchars($prof['last_name'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">کد ملی</span><span class="info-value"><?= to_persian_num($prof['national_id']) ?></span></div>
                <div class="info-item"><span class="info-label">سمت</span><span class="info-value"><?= htmlspecialchars($prof['position'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">تحصیلات</span><span class="info-value"><?= htmlspecialchars($prof['education'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">نام پدر</span><span class="info-value"><?= htmlspecialchars($prof['father_name'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">تاریخ تولد</span><span class="info-value"><?= to_persian_num($prof['birth_date'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">محل صدور</span><span class="info-value"><?= htmlspecialchars($prof['birth_place'] ?: '—') ?></span></div>
            </div>

            <h2 style="margin-top:30px;">📞 اطلاعات تماس و قرارداد</h2>
            <div class="profile-grid">
                <div class="info-item"><span class="info-label">تلفن همراه</span><span class="info-value"><?= to_persian_num($prof['mobile_phone'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">تلفن ثابت</span><span class="info-value"><?= to_persian_num($prof['home_phone'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">تاریخ قرارداد</span><span class="info-value"><?= to_persian_num($prof['contract_date'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">شماره نامه</span><span class="info-value"><?= to_persian_num($prof['letter_no'] ?: '—') ?></span></div>
                <div class="info-item"><span class="info-label">بانک</span><span class="info-value"><?= htmlspecialchars($prof['bank'] ?: '—') ?></span></div>
                <div class="info-item" style="grid-column: span 2;"><span class="info-label">شماره شبا</span><span class="info-value" style="direction:ltr; text-align:right;"><?= to_persian_num($prof['sheba'] ?: '—') ?></span></div>
                <div class="info-item" style="grid-column: span 2;"><span class="info-label">آدرس</span><span class="info-value"><?= nl2br(htmlspecialchars($prof['address'] ?: '—')) ?></span></div>
            </div>

            <h2 style="margin-top:30px;">📅 برنامه حضور</h2>
            <div class="info-item">
                <span class="info-value"><?= nl2br(htmlspecialchars($prof['schedule'] ?: 'هنوز برنامه‌ای ثبت نشده است.')) ?></span>
            </div>
        <?php else: ?>
            <div class="placeholder-msg">
                <h2>اطلاعاتی یافت نشد</h2>
                <p>هنوز پروفایلی برای شما در این سال تحصیلی ثبت نشده است.</p>
            </div>
        <?php endif; ?>
        <div style="margin-top:30px; text-align:center;">
            <a href="dashboard.php" class="btn-logout" style="background:var(--turquoise-dark); border:none;">← بازگشت به داشبورد</a>
        </div>
    </div>

  <?php else: ?>
    <div class="welcome">
      <h1>سلام، <span><?= to_persian_num(htmlspecialchars($fullName)) ?></span> خوش آمدید 👋</h1>
      <p>به پرتال <?= $_SESSION['role'] === 'staff' ? 'کارکنان' : 'دانش‌آموزی' ?> دبیرستان توانش خوش آمدید. از بخش‌های زیر استفاده کنید.</p>
    </div>

    <div class="section-title">امکانات پرتال</div>
    <div class="grid">

      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="manage_exams.php" class="card">
          <div class="card-icon">📝</div>
          <h3>مدیریت نمرات</h3>
          <p>ثبت و ویرایش نمرات دانش‌آموزان</p>
        </a>

      <?php elseif ($_SESSION['role'] === 'staff'):
          $db = getDB();
          $stmt = $db->prepare("SELECT position FROM staff_profiles WHERE national_id = ? ORDER BY academic_year DESC LIMIT 1");
          $stmt->execute([$_SESSION['username']]);
          $staff_info = $stmt->fetch();
          $isTeacher = ($staff_info && strpos($staff_info['position'], 'دبیر') !== false);
      ?>
        <a href="?personal_info=1" class="card featured">
          <div class="card-icon">👤</div>
          <h3>اطلاعات پرسنلی</h3>
          <p>مشاهده و ویرایش مشخصات فردی</p>
        </a>

        <?php if ($isTeacher): ?>
        <a href="manage_exams.php" class="card">
          <div class="card-icon">📝</div>
          <h3>مدیریت نمرات</h3>
          <p>ثبت و ویرایش نمرات دانش‌آموزان</p>
        </a>
        <?php endif; ?>

        <a href="?page=paystub" class="card">
          <div class="card-icon">💵</div>
          <h3>مشاهده فیش حقوقی</h3>
          <p>اطلاعات پرداختی‌ها و حقوق</p>
        </a>

      <?php else: ?>
        <!-- اقساط شهریه — برجسته -->
        <a href="tuition.php" class="card featured">
          <div class="card-icon">💳</div>
          <h3>اقساط شهریه</h3>
          <p>مشاهده وضعیت پرداخت اقساط شهریه</p>
        </a>

        <a href="student_profile.php" class="card">
          <div class="card-icon">👤</div>
          <h3>پروفایل من</h3>
          <p>مشاهده اطلاعات ثبت شده در سیستم</p>
        </a>

        <a href="?page=report_card" class="card">
          <div class="card-icon">📜</div>
          <h3>کارنامه</h3>
          <p>مشاهده و دریافت کارنامه‌های تحصیلی</p>
        </a>

        <!--
        <div class="card">
          <div class="card-icon">📚</div>
          <h3>دروس و محتوا</h3>
          <p>مشاهده محتواهای آموزشی و جزوه‌های درسی</p>
        </div>
        <div class="card">
          <div class="card-icon">📅</div>
          <h3>برنامه هفتگی</h3>
          <p>مشاهده جدول کلاس‌ها و برنامه هفتگی</p>
        </div>
        <div class="card">
          <div class="card-icon">📝</div>
          <h3>تکالیف</h3>
          <p>ارسال تکالیف و پیگیری وضعیت تحویل</p>
        </div>
        -->

        <a href="student_scores.php" class="card">
          <div class="card-icon">🏆</div>
          <h3>نمرات</h3>
          <p>مشاهده نمرات آزمون‌ها و کارنامه تحصیلی</p>
        </a>

        <!--
        <div class="card">
          <div class="card-icon">📆</div>
          <h3>تقویم آزمون‌ها</h3>
          <p>مشاهده تاریخ و زمان آزمون‌های پیش‌رو</p>
        </div>
        <div class="card">
          <div class="card-icon">🎓</div>
          <h3>کلاس‌های فوق‌برنامه</h3>
          <p>ثبت‌نام و مشاهده کلاس‌های تکمیلی</p>
        </div>
        <div class="card">
          <div class="card-icon">💬</div>
          <h3>پیام‌ها</h3>
          <p>ارتباط با معلمان و ارسال پیام</p>
        </div>
        -->
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <a href="/" class="back-link">→ بازگشت به صفحه اصلی سایت</a>
</main>
</body>
</html>
