<?php
require_once 'auth.php';
requireLogin();

$db = getDB();
$national_id = $_SESSION['username']; // نام کاربری همان کد ملی است
$fullName    = $_SESSION['full_name'] ?? $_SESSION['username'];

// واکشی اقساط این دانش‌آموز
$stmt = $db->prepare("SELECT * FROM tuition WHERE national_id = ? ORDER BY installment_no ASC");
$stmt->execute([$national_id]);
$installments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// محاسبه جمع کل، پرداخت شده، باقیمانده
$totalAmount    = 0;
$totalPaid      = 0;
$totalDueByToday = 0;
$todayJalali    = get_jalali_today();

foreach ($installments as $row) {
    $totalAmount += $row['amount'];
    $totalPaid   += $row['paid_amount'];
    if (!empty($row['due_date']) && $row['due_date'] <= $todayJalali) {
        $totalDueByToday += $row['amount'];
    }
}
$totalRemaining = $totalAmount - $totalPaid;
$financialBalance = $totalPaid - $totalDueByToday;

// تبدیل عدد به فارسی
function toFa($num) {
    $map = ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹'];
    return strtr((string)$num, $map);
}
function formatMoney($n) {
    return toFa(number_format($n));
}

$statusLabel = ['paid'=>'پرداخت شده','partial'=>'ناقص','unpaid'=>'پرداخت نشده'];
$statusClass = ['paid'=>'paid','partial'=>'partial','unpaid'=>'unpaid'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>اقساط شهریه — توانش</title>
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
  --green:            #1a9960;
  --green-light:      #f0fdf4;
  --green-border:     #86efac;
  --orange:           #d97706;
  --red:              #c94040;
  --red-light:        #fff1f1;
  --red-border:       #fca5a5;
  --shadow-sm:        0 4px 15px rgba(0,0,0,.08);
  --shadow-md:        0 12px 30px rgba(0,0,0,.12);
  --shadow-lg:        0 20px 50px rgba(0,0,0,.18);
  --transition:       all 0.35s cubic-bezier(0.4,0,0.2,1);
}

body { min-height:100vh; background:linear-gradient(to bottom,#f5fbfd,#fff); font-family:'Vazirmatn',sans-serif; color:var(--text); line-height:1.7; }

/* نوار بالا */
.topbar { background:var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000; box-shadow:var(--shadow-md); }
.topbar-inner { max-width:1100px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
.brand { display:flex; align-items:center; gap:12px; }
.brand-logo { width:52px; height:52px; border-radius:12px; overflow:hidden; background:rgba(255,255,255,.2); box-shadow:0 4px 12px rgba(0,0,0,.18); }
.brand-logo img { width:100%; height:100%; object-fit:contain; }
.brand-title { font-size:1.2rem; font-weight:800; }
.brand-sub   { font-size:.8rem; opacity:.9; font-weight:300; }
.topbar-left { display:flex; align-items:center; gap:10px; }
.btn-back    { padding:8px 16px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); border-radius:10px; font-family:'Vazirmatn',sans-serif; font-size:.85rem; font-weight:700; color:#fff; text-decoration:none; transition:var(--transition); }
.btn-back:hover { background:rgba(255,255,255,.28); }

/* محتوا */
main { max-width:1100px; margin:0 auto; padding:36px 20px 60px; animation:fadeIn .4s ease both; }
@keyframes fadeIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* هدر صفحه */
.page-header { margin-bottom:32px; }
.page-header h1 { font-size:1.5rem; font-weight:800; margin-bottom:4px; }
.page-header h1 span { color:var(--turquoise-dark); }
.page-header p { font-size:.88rem; color:var(--gray); font-weight:300; }

/* کارت‌های خلاصه */
.summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:36px; }
.summary-card { background:#fff; border-radius:16px; padding:22px 20px; box-shadow:var(--shadow-sm); border:1.5px solid var(--turquoise-light); text-align:center; }
.summary-card .label { font-size:.8rem; color:var(--gray); font-weight:500; margin-bottom:8px; letter-spacing:.02em; }
.summary-card .value { font-size:1.4rem; font-weight:800; }
.summary-card.total    .value { color:var(--text); }
.summary-card.paid-sum .value { color:var(--green); }
.summary-card.remaining .value { color:var(--red); }
.summary-card.balance-overdue { border-color: var(--red-border); background: var(--red-light); }
.summary-card.balance-overdue .value { color: var(--red); }
.summary-card.balance-credit { border-color: var(--green-border); background: var(--green-light); }
.summary-card.balance-credit .value { color: var(--green); }

/* جدول */
.table-wrap { background:#fff; border-radius:20px; box-shadow:var(--shadow-sm); border:1.5px solid var(--turquoise-light); overflow:hidden; }
.table-title { padding:18px 24px 14px; font-size:1rem; font-weight:800; border-bottom:1px solid var(--turquoise-light); display:flex; align-items:center; gap:8px; }
.table-title::before { content:'💳'; }

table { width:100%; border-collapse:collapse; }
thead th { background:var(--turquoise-lighter); padding:12px 16px; font-size:.8rem; font-weight:700; color:var(--turquoise-dark); text-align:right; border-bottom:1.5px solid var(--turquoise-light); white-space:nowrap; }
tbody tr { border-bottom:1px solid #edf6f8; transition:background .2s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--turquoise-lighter); }
tbody td { padding:14px 16px; font-size:.9rem; vertical-align:middle; }
.no-data { text-align:center; padding:48px 20px; color:var(--gray); font-size:.95rem; }

/* نشان وضعیت */
.badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:.78rem; font-weight:700; white-space:nowrap; }
.badge.paid    { background:rgba(26,153,96,.1);  color:var(--green);  border:1px solid rgba(26,153,96,.25); }
.badge.partial { background:rgba(217,119,6,.1);  color:var(--orange); border:1px solid rgba(217,119,6,.25); }
.badge.unpaid  { background:rgba(201,64,64,.1);  color:var(--red);    border:1px solid rgba(201,64,64,.25); }

/* نوار پیشرفت */
.progress-wrap { display:flex; align-items:center; gap:10px; }
.progress-bar  { flex:1; height:8px; background:#e0f2f4; border-radius:4px; overflow:hidden; }
.progress-fill { height:100%; border-radius:4px; background:linear-gradient(to left,var(--turquoise),var(--turquoise-dark)); transition:width .6s ease; }
.progress-pct  { font-size:.78rem; font-weight:700; color:var(--turquoise-dark); white-space:nowrap; min-width:36px; text-align:left; }

/* واکنش‌گرا */
@media(max-width:700px){
  table thead { display:none; }
  tbody tr { display:block; padding:14px 16px; border-bottom:1.5px solid var(--turquoise-light); }
  tbody td { display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:.85rem; }
  tbody td::before { content:attr(data-label); font-weight:700; color:var(--gray); font-size:.78rem; }
  .progress-wrap { flex-direction:column; align-items:flex-end; gap:4px; }
}
</style>
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="brand-logo"><img src="/images/logo-Tw.png" alt="لوگو توانش"></div>
      <div>
        <div class="brand-title">دبیرستان دخترانه توانش</div>
        <div class="brand-sub">اقساط شهریه</div>
      </div>
    </div>
    <div class="topbar-left">
      <a href="dashboard.php" class="btn-back">→ بازگشت</a>
    </div>
  </div>
</header>

<main>
  <div class="page-header">
    <h1>اقساط شهریه — <span><?= htmlspecialchars($fullName) ?></span></h1>
    <p>کد ملی: <?= toFa(htmlspecialchars($national_id)) ?></p>
  </div>

  <?php if (empty($installments)): ?>
    <div class="table-wrap">
      <div class="no-data">
        📋 هنوز هیچ اطلاعات اقساطی برای شما ثبت نشده است.<br>
        <small>لطفاً با دفتر مدرسه تماس بگیرید.</small>
      </div>
    </div>
  <?php else: ?>

  <!-- خلاصه مالی -->
  <div class="summary-grid">
    <div class="summary-card total">
      <div class="label">کل شهریه</div>
      <div class="value"><?= formatMoney($totalAmount) ?> تومان</div>
    </div>
    <div class="summary-card paid-sum">
      <div class="label">پرداخت شده</div>
      <div class="value"><?= formatMoney($totalPaid) ?> تومان</div>
    </div>
    <div class="summary-card remaining">
      <div class="label">مانده کل</div>
      <div class="value"><?= formatMoney($totalRemaining) ?> تومان</div>
    </div>
    <?php if ($financialBalance < 0): ?>
      <div class="summary-card balance-overdue">
        <div class="label">معوقه تا امروز</div>
        <div class="value"><?= formatMoney(abs($financialBalance)) ?> تومان</div>
      </div>
    <?php elseif ($financialBalance > 0): ?>
      <div class="summary-card balance-credit">
        <div class="label">بستانکار (اضافه پرداختی)</div>
        <div class="value"><?= formatMoney($financialBalance) ?> تومان</div>
      </div>
    <?php endif; ?>
  </div>

  <!-- جدول اقساط -->
  <div class="table-wrap">
    <div class="table-title">جدول اقساط</div>
    <table>
      <thead>
        <tr>
          <th>شماره قسط</th>
          <th>شرح</th>
          <th>مبلغ (تومان)</th>
          <th>پرداخت شده (تومان)</th>
          <th>تاریخ سررسید</th>
          <th>تاریخ پرداخت</th>
          <th>پیشرفت</th>
          <th>وضعیت</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($installments as $row):
          $pct = $row['amount'] > 0 ? round($row['paid_amount'] / $row['amount'] * 100) : 0;
          $st  = $row['status'] ?? 'unpaid';
          $stLabel = $statusLabel[$st] ?? $st;
          $stClass = $statusClass[$st] ?? 'unpaid';
        ?>
        <tr>
          <td data-label="شماره قسط"><?= toFa($row['installment_no']) ?></td>
          <td data-label="شرح"><?= htmlspecialchars($row['description'] ?? '—') ?></td>
          <td data-label="مبلغ"><?= formatMoney($row['amount']) ?></td>
          <td data-label="پرداخت شده"><?= formatMoney($row['paid_amount']) ?></td>
          <td data-label="سررسید"><?= htmlspecialchars($row['due_date'] ?? '—') ?></td>
          <td data-label="تاریخ پرداخت"><?= htmlspecialchars($row['paid_date'] ?: '—') ?></td>
          <td data-label="پیشرفت">
            <div class="progress-wrap">
              <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
              <span class="progress-pct"><?= toFa($pct) ?>٪</span>
            </div>
          </td>
          <td data-label="وضعیت"><span class="badge <?= $stClass ?>"><?= $stLabel ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>
</main>
</body>
</html>
