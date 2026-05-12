<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'auth.php';

// ════════════════════════════════════════════════════
//  رمز ورود پنل مدیریت — حتماً تغییر دهید!
// ════════════════════════════════════════════════════
define('ADMIN_PASSWORD', 'Admin@Tavanesh1');

session_start_if_not_started: // label not used, session already started in auth.php

// ─── ورود/خروج ادمین ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['admin_pass'] === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
    } else {
        $loginError = 'رمز عبور اشتباه است.';
    }
}
if (isset($_GET['logout_admin'])) {
    unset($_SESSION['is_admin']);
    header('Location: admin.php');
    exit;
}

$isAdmin = !empty($_SESSION['is_admin']);

// ─── پیام‌ها ───
$msgs = [];

// ─── ایمپورت دانش‌آموزان از CSV ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_students'])) {
    if (!empty($_FILES['students_csv']['tmp_name'])) {
        $file = fopen($_FILES['students_csv']['tmp_name'], 'r');
        // تشخیص BOM یونیکد (اکسل گاهی اضافه می‌کند)
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($file);

        $created = $updated = $skipped = 0;
        $rowNum = 0;
        while (($row = fgetcsv($file)) !== false) {
            $rowNum++;
            // رد کردن سطر عنوان
            if ($rowNum === 1 && (trim($row[0]) === 'full_name' || trim($row[0]) === 'نام_و_نام_خانوادگی')) continue;
            if (count($row) < 2) { $skipped++; continue; }

            $full_name   = trim($row[0]);
            $national_id = trim($row[1]);
            if (!$full_name || !$national_id) { $skipped++; continue; }

            $result = upsertStudent($national_id, $full_name);
            if ($result === 'created') $created++;
            else $updated++;
        }
        fclose($file);
        $msgs[] = ['type'=>'success', 'text'=>"✅ دانش‌آموزان: ایجاد شده: $created | به‌روز: $updated | رد شده: $skipped"];
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل CSV انتخاب نشده.'];
    }
}

// ─── ایمپورت اقساط از CSV ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_tuition'])) {
    if (!empty($_FILES['tuition_csv']['tmp_name'])) {
        $db   = getDB();
        $file = fopen($_FILES['tuition_csv']['tmp_name'], 'r');
        $bom  = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($file);

        $inserted = $updated = $skipped = 0;
        $rowNum = 0;
        while (($row = fgetcsv($file)) !== false) {
            $rowNum++;
            if ($rowNum === 1 && (trim($row[0]) === 'national_id' || trim($row[0]) === 'کد_ملی')) continue;
            if (count($row) < 4) { $skipped++; continue; }

            $national_id    = trim($row[0]);
            $installment_no = (int)trim($row[1]);
            $description    = trim($row[2] ?? '');
            $amount         = (int)str_replace([',', '،'], '', trim($row[3]));
            $due_date       = trim($row[4] ?? '');
            $paid_amount    = (int)str_replace([',', '،'], '', trim($row[5] ?? 0));
            $paid_date      = trim($row[6] ?? '');
            $status         = trim($row[7] ?? 'unpaid');
            if (!in_array($status, ['paid','partial','unpaid'])) $status = 'unpaid';

            if (!$national_id || !$installment_no) { $skipped++; continue; }

            // بررسی وجود رکورد
            $check = $db->prepare("SELECT id FROM tuition WHERE national_id=? AND installment_no=?");
            $check->execute([$national_id, $installment_no]);
            $existing = $check->fetch();

            if ($existing) {
                $db->prepare("UPDATE tuition SET description=?,amount=?,due_date=?,paid_amount=?,paid_date=?,status=? WHERE national_id=? AND installment_no=?")
                   ->execute([$description,$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$national_id,$installment_no]);
                $updated++;
            } else {
                $db->prepare("INSERT INTO tuition (national_id,installment_no,description,amount,due_date,paid_amount,paid_date,status) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$national_id,$installment_no,$description,$amount,$due_date,$paid_amount,$paid_date ?: null,$status]);
                $inserted++;
            }
        }
        fclose($file);
        $msgs[] = ['type'=>'success', 'text'=>"✅ اقساط: وارد شده: $inserted | به‌روز: $updated | رد شده: $skipped"];
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل CSV انتخاب نشده.'];
    }
}
// ─── افزودن دستی دانش‌آموز ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_manual'])) {

    $full_name   = trim($_POST['full_name'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');

    if (!$full_name || !$national_id) {

        $msgs[] = [
            'type' => 'error',
            'text' => '❌ نام و کد ملی الزامی هستند.'
        ];

    } else {

        $result = upsertStudent($national_id, $full_name);

        if ($result === 'created') {

            $msgs[] = [
                'type' => 'success',
                'text' => '✅ دانش‌آموز جدید ایجاد شد.'
            ];

        } else {

            $msgs[] = [
                'type' => 'success',
                'text' => '✅ اطلاعات دانش‌آموز به‌روزرسانی شد.'
            ];
        }
    }
}
// ─── افزودن دستی قسط ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tuition_manual'])) {

    $db = getDB();

    $national_id    = trim($_POST['national_id'] ?? '');
    $installment_no = (int)($_POST['installment_no'] ?? 0);
    $description    = trim($_POST['description'] ?? '');
    $amount         = (int)str_replace(',', '', $_POST['amount'] ?? 0);
    $due_date       = trim($_POST['due_date'] ?? '');
    $paid_amount    = (int)str_replace(',', '', $_POST['paid_amount'] ?? 0);
    $paid_date      = trim($_POST['paid_date'] ?? '');
    $status         = trim($_POST['status'] ?? 'unpaid');

    if (!$national_id || !$installment_no || !$amount) {

        $msgs[] = [
            'type' => 'error',
            'text' => '❌ اطلاعات قسط ناقص است.'
        ];

    } else {

        $stmt = $db->prepare("
            INSERT INTO tuition
            (
                national_id,
                installment_no,
                description,
                amount,
                due_date,
                paid_amount,
                paid_date,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $national_id,
            $installment_no,
            $description,
            $amount,
            $due_date,
            $paid_amount,
            $paid_date ?: null,
            $status
        ]);

        $msgs[] = [
            'type' => 'success',
            'text' => '✅ قسط شهریه ثبت شد.'
        ];
    }
}
// ─── حذف دانش‌آموز ───
if ($isAdmin && isset($_GET['delete_user'])) {
    $db = getDB();
    $db->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_GET['delete_user']]);
    header('Location: admin.php?tab=students');
    exit;
}

// ─── ویرایش پروفایل دانش‌آموز ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student_profile'])) {
    $db = getDB();
    $old_username = $_POST['old_username'];
    $new_username = trim($_POST['new_username']);
    $full_name    = trim($_POST['full_name']);

    try {
        $db->beginTransaction();
        $stmt = $db->prepare("UPDATE users SET username=?, full_name=? WHERE username=?");
        $stmt->execute([$new_username, $full_name, $old_username]);
        if ($new_username !== $old_username) {
            $db->prepare("UPDATE tuition SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
        }
        $db->commit();
        $msgs[] = ['type'=>'success', 'text'=>'✅ پروفایل دانش‌آموز بروزرسانی شد.'];
        $_GET['username'] = $new_username; // برای ماندن در همان صفحه مدیریت
    } catch (Exception $e) {
        $db->rollBack();
        $msgs[] = ['type'=>'error', 'text'=>'❌ خطا: ' . $e->getMessage()];
    }
}

// ─── ویرایش قسط ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tuition_row'])) {
    $db = getDB();
    $id = (int)$_POST['tuition_id'];
    $installment_no = (int)$_POST['installment_no'];
    $description = trim($_POST['description']);
    $amount = (int)str_replace(',', '', $_POST['amount']);
    $due_date = trim($_POST['due_date']);
    $paid_amount = (int)str_replace(',', '', $_POST['paid_amount']);
    $paid_date = trim($_POST['paid_date']);
    $status = $_POST['status'];

    $stmt = $db->prepare("UPDATE tuition SET installment_no=?, description=?, amount=?, due_date=?, paid_amount=?, paid_date=?, status=? WHERE id=?");
    $stmt->execute([$installment_no, $description, $amount, $due_date, $paid_amount, $paid_date ?: null, $status, $id]);
    $msgs[] = ['type'=>'success', 'text'=>'✅ قسط ویرایش شد.'];
}

// ─── حذف قسط ───
if ($isAdmin && isset($_GET['delete_tuition_id'])) {
    $db = getDB();
    $db->prepare("DELETE FROM tuition WHERE id=?")->execute([(int)$_GET['delete_tuition_id']]);
    $msgs[] = ['type'=>'success', 'text'=>'✅ قسط حذف شد.'];
}

// ─── ثبت پرداختی خودکار (توزیع بین اقساط) ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_payment_auto'])) {
    $db = getDB();
    $national_id = $_POST['national_id'];
    $pay_amount = (int)str_replace(',', '', $_POST['pay_amount']);
    $pay_date = trim($_POST['pay_date']);

    if ($pay_amount > 0) {
        $stmt = $db->prepare("SELECT * FROM tuition WHERE national_id=? AND status != 'paid' ORDER BY installment_no ASC");
        $stmt->execute([$national_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $remaining = $pay_amount;
        foreach ($rows as $row) {
            if ($remaining <= 0) break;
            $needed = $row['amount'] - $row['paid_amount'];
            if ($needed <= 0) continue;
            if ($remaining >= $needed) {
                $new_paid = $row['amount'];
                $new_status = 'paid';
                $remaining -= $needed;
            } else {
                $new_paid = $row['paid_amount'] + $remaining;
                $new_status = 'partial';
                $remaining = 0;
            }
            $db->prepare("UPDATE tuition SET paid_amount=?, status=?, paid_date=? WHERE id=?")
               ->execute([$new_paid, $new_status, $pay_date ?: null, $row['id']]);
        }
        $msgs[] = ['type'=>'success', 'text'=>'✅ مبلغ پرداختی بین اقساط توزیع شد.'];
    }
}

// ─── لیست دانش‌آموزان ───
$students = [];
if ($isAdmin) {
    $db = getDB();
    $students = $db->query("SELECT id, username, full_name, created_at FROM users ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
}

$tab = $_GET['tab'] ?? 'import';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>پنل مدیریت — توانش</title>
<link rel="icon" href="/images/logo-T.png" type="image/png">
<style>
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Light.woff2') format('woff2'); font-weight:300; font-display:swap; }
@font-face { font-family:'Vazirmatn'; src:url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight:400; font-display:swap; }
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
  --red:              #c94040;
  --green:            #1a9960;
  --shadow-sm:        0 4px 15px rgba(0,0,0,.08);
  --shadow-md:        0 12px 30px rgba(0,0,0,.12);
  --shadow-lg:        0 20px 50px rgba(0,0,0,.18);
}

body { min-height:100vh; background:linear-gradient(to bottom,#f5fbfd,#fff); font-family:'Vazirmatn',sans-serif; color:var(--text); }

/* نوار بالا */
.topbar { background:var(--turquoise); color:#fff; box-shadow:var(--shadow-md); }
.topbar-inner { max-width:1100px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
.brand { display:flex; align-items:center; gap:12px; }
.brand-logo { width:50px; height:50px; border-radius:12px; overflow:hidden; background:rgba(255,255,255,.2); }
.brand-logo img { width:100%; height:100%; object-fit:contain; }
.brand-title { font-size:1.1rem; font-weight:800; }
.brand-sub   { font-size:.8rem; opacity:.9; font-weight:300; }
.btn-sm { padding:8px 15px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); border-radius:10px; font-family:'Vazirmatn',sans-serif; font-size:.82rem; font-weight:700; color:#fff; text-decoration:none; cursor:pointer; transition:background .2s; }
.btn-sm:hover { background:rgba(255,255,255,.28); }

/* صفحه لاگین */
.login-wrap { max-width:420px; margin:80px auto; padding:0 16px; }
.login-card { background:#fff; border-radius:20px; padding:36px 32px; box-shadow:var(--shadow-lg); }
.login-card h2 { font-size:1.3rem; font-weight:800; margin-bottom:20px; }
.field { margin-bottom:18px; }
label { display:block; font-size:.78rem; font-weight:700; color:var(--gray); margin-bottom:7px; }
input[type=password], input[type=text], input[type=file] {
  width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:11px 14px;
  font-family:'Vazirmatn',sans-serif; font-size:.93rem; color:var(--text);
  background:var(--turquoise-lighter); outline:none; text-align:right;
  transition:border-color .2s, box-shadow .2s;
}
input:focus { border-color:var(--turquoise); background:#fff; box-shadow:0 0 0 3px rgba(25,184,194,.15); }
.btn-primary { width:100%; padding:12px; background:linear-gradient(135deg,var(--turquoise),var(--turquoise-dark)); border:none; border-radius:12px; font-family:'Vazirmatn',sans-serif; font-size:.95rem; font-weight:700; color:#fff; cursor:pointer; box-shadow:0 6px 20px rgba(25,184,194,.4); transition:transform .15s, box-shadow .15s; }
.btn-primary:hover { transform:translateY(-2px); }

/* محتوای اصلی */
main { max-width:1100px; margin:0 auto; padding:32px 20px 60px; }

/* تب‌ها */
.tabs { display:flex; gap:8px; margin-bottom:28px; flex-wrap:wrap; }
.tab { padding:10px 22px; border-radius:12px; font-family:'Vazirmatn',sans-serif; font-size:.88rem; font-weight:700; text-decoration:none; color:var(--gray); background:#fff; border:1.5px solid var(--turquoise-light); transition:all .2s; }
.tab:hover,.tab.active { background:var(--turquoise); color:#fff; border-color:var(--turquoise); }

/* کارت */
.card { background:#fff; border:1.5px solid var(--turquoise-light); border-radius:18px; padding:28px; box-shadow:var(--shadow-sm); margin-bottom:24px; }
.card h3 { font-size:1rem; font-weight:800; margin-bottom:18px; display:flex; align-items:center; gap:8px; }

/* پیام */
.alert { padding:12px 16px; border-radius:12px; font-size:.87rem; margin-bottom:18px; }
.alert.success { background:rgba(26,153,96,.08); border:1px solid rgba(26,153,96,.25); color:var(--green); }
.alert.error   { background:rgba(201,64,64,.08); border:1px solid rgba(201,64,64,.25); color:var(--red); }

/* جدول */
.table-wrap { overflow-x:auto; border-radius:14px; border:1.5px solid var(--turquoise-light); }
table { width:100%; border-collapse:collapse; }
thead th { background:var(--turquoise-lighter); padding:11px 14px; font-size:.8rem; font-weight:700; color:var(--turquoise-dark); text-align:right; border-bottom:1.5px solid var(--turquoise-light); }
tbody tr { border-bottom:1px solid #edf6f8; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--turquoise-lighter); }
tbody td { padding:11px 14px; font-size:.88rem; }
.btn-del { padding:5px 12px; background:rgba(201,64,64,.08); border:1px solid rgba(201,64,64,.25); border-radius:8px; font-family:'Vazirmatn',sans-serif; font-size:.78rem; font-weight:700; color:var(--red); cursor:pointer; text-decoration:none; }
.btn-del:hover { background:rgba(201,64,64,.15); }

/* راهنما */
.guide { background:var(--turquoise-lighter); border:1px solid var(--turquoise-light); border-radius:14px; padding:18px 20px; font-size:.85rem; color:var(--text); line-height:1.8; margin-top:12px; }
.guide strong { color:var(--turquoise-dark); }
.guide code { background:#d0eff3; border-radius:6px; padding:2px 7px; font-family:monospace; font-size:.82rem; }
</style>
</head>
<body>

<header class="topbar">
  <div class="topbar-inner">
    <div class="brand">
      <div class="brand-logo"><img src="/images/logo-Tw.png" alt="لوگو"></div>
      <div>
        <div class="brand-title">پنل مدیریت توانش</div>
        <div class="brand-sub">مدیریت دانش‌آموزان و اقساط</div>
      </div>
    </div>
    <?php if ($isAdmin): ?>
    <a href="admin.php?logout_admin=1" class="btn-sm">خروج از پنل</a>
    <?php endif; ?>
  </div>
</header>

<?php if (!$isAdmin): ?>
<!-- ─── فرم ورود ─── -->
<div class="login-wrap">
  <div class="login-card">
    <h2>🔒 ورود به پنل مدیریت</h2>
    <?php if (!empty($loginError)): ?>
      <div class="alert error"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field">
        <label>رمز مدیریت</label>
        <input type="password" name="admin_pass" placeholder="رمز عبور پنل را وارد کنید" autofocus>
      </div>
      <button type="submit" name="admin_login" class="btn-primary">ورود ←</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ─── پنل اصلی ─── -->
<main>

  <?php foreach ($msgs as $m): ?>
    <div class="alert <?= $m['type'] ?>"><?= htmlspecialchars($m['text']) ?></div>
  <?php endforeach; ?>

  <div class="tabs">
    <a href="?tab=import"    class="tab <?= $tab==='import'    ?'active':'' ?>">📤 وارد کردن داده</a>
    <a href="?tab=students"  class="tab <?= $tab==='students'  ?'active':'' ?>">👩‍🎓 لیست دانش‌آموزان (<?= count($students) ?>)</a>
  </div>

  <?php if ($tab === 'import'): ?>
<!-- افزودن دستی دانش‌آموز -->
<div class="card">
  <h3>➕ افزودن دستی دانش‌آموز</h3>

  <form method="POST">

    <div class="field">
      <label>نام و نام خانوادگی</label>
      <input type="text"
             name="full_name"
             placeholder="مثلاً فاطمه محمدی">
    </div>

    <div class="field">
      <label>کد ملی</label>
      <input type="text"
             name="national_id"
             placeholder="مثلاً 1234567890">
    </div>

    <button type="submit"
            name="add_student_manual"
            class="btn-primary">
      ثبت دانش‌آموز
    </button>

  </form>

  <div class="guide">
    ⚡ نام کاربری و رمز عبور اولیه = کد ملی دانش‌آموز
  </div>
</div>
  <!-- ایمپورت دانش‌آموزان -->
  <div class="card">
    <h3>👩‍🎓 وارد کردن دانش‌آموزان از فایل CSV</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل CSV دانش‌آموزان</label>
        <input type="file" name="students_csv" accept=".csv">
      </div>
      <button type="submit" name="import_students" class="btn-primary">آپلود و وارد کردن</button>
    </form>
    <div class="guide">
      <strong>فرمت فایل CSV دانش‌آموزان:</strong><br>
      ستون اول: نام و نام خانوادگی — ستون دوم: کد ملی<br>
      <code>فاطمه محمدی,1234567890</code><br>
      <code>زهرا احمدی,0987654321</code><br><br>
      ⚡ نام کاربری و رمز عبور اولیه هر دانش‌آموز = کد ملی او<br>
      🔄 اگر کد ملی قبلاً وجود داشته باشد، فقط نام به‌روز می‌شود.<br><br>
      <strong>برای ذخیره اکسل به CSV:</strong> در اکسل ← <em>File → Save As → CSV UTF-8 (Comma delimited)</em>
    </div>
  </div>
<!-- افزودن دستی قسط -->
<div class="card">

  <h3>💳 افزودن دستی قسط شهریه</h3>

  <form method="POST">

    <div class="field">
      <label>کد ملی دانش‌آموز (یا جستجوی نام)</label>
      <input type="text" name="national_id" list="students_list" autocomplete="off">
      <datalist id="students_list">
        <?php foreach ($students as $s): ?>
          <option value="<?= htmlspecialchars($s['username']) ?>"><?= htmlspecialchars($s['full_name']) ?> (<?= htmlspecialchars($s['username']) ?>)</option>
        <?php endforeach; ?>
      </datalist>
    </div>

    <div class="field">
      <label>شماره قسط</label>
      <input type="text" name="installment_no">
    </div>

    <div class="field">
      <label>شرح</label>
      <input type="text" name="description">
    </div>

    <div class="field">
      <label>مبلغ</label>
      <input type="text" name="amount">
    </div>

    <div class="field">
      <label>تاریخ سررسید</label>
      <input type="text" name="due_date" placeholder="1405/01/15">
    </div>

    <div class="field">
      <label>مبلغ پرداخت شده</label>
      <input type="text" name="paid_amount" value="0">
    </div>

    <div class="field">
      <label>تاریخ پرداخت</label>
      <input type="text" name="paid_date">
    </div>

    <div class="field">
      <label>وضعیت</label>

      <select name="status"
              style="width:100%;padding:12px;border-radius:12px;border:1.5px solid #c0e5ea;font-family:'Vazirmatn';">

        <option value="unpaid">پرداخت نشده</option>
        <option value="partial">ناقص</option>
        <option value="paid">پرداخت شده</option>

      </select>
    </div>

    <button type="submit"
            name="add_tuition_manual"
            class="btn-primary">

      ثبت قسط

    </button>

  </form>
</div>
  <!-- ایمپورت اقساط -->
  <div class="card">
    <h3>💳 وارد کردن اقساط شهریه از فایل CSV</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل CSV اقساط</label>
        <input type="file" name="tuition_csv" accept=".csv">
      </div>
      <button type="submit" name="import_tuition" class="btn-primary">آپلود و وارد کردن</button>
    </form>
    <div class="guide">
      <strong>فرمت فایل CSV اقساط (۸ ستون):</strong><br>
      <code>کد_ملی , شماره_قسط , شرح , مبلغ , تاریخ_سررسید , پرداخت_شده , تاریخ_پرداخت , وضعیت</code><br><br>
      <strong>مثال:</strong><br>
      <code>1234567890,1,قسط اول,5000000,1403/07/01,5000000,1403/06/28,paid</code><br>
      <code>1234567890,2,قسط دوم,5000000,1403/09/01,2500000,,partial</code><br>
      <code>1234567890,3,قسط سوم,5000000,1403/11/01,0,,unpaid</code><br><br>
      <strong>مقادیر وضعیت:</strong>
      <code>paid</code> پرداخت شده &nbsp;|&nbsp;
      <code>partial</code> ناقص &nbsp;|&nbsp;
      <code>unpaid</code> پرداخت نشده<br>
      🔄 اگر کد ملی + شماره قسط قبلاً موجود بود، به‌روز می‌شود.
    </div>
  </div>

  <?php elseif ($tab === 'students'): ?>

  <!-- لیست دانش‌آموزان -->
  <div class="card">
    <h3>👩‍🎓 لیست دانش‌آموزان (<?= count($students) ?> نفر)</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>نام و نام خانوادگی</th>
            <th>کد ملی (نام کاربری)</th>
            <th>تاریخ ثبت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--gray)">هنوز دانش‌آموزی ثبت نشده</td></tr>
          <?php endif; ?>
          <?php foreach ($students as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($s['full_name'] ?: '—') ?></td>
            <td><?= htmlspecialchars($s['username']) ?></td>
            <td><?= htmlspecialchars(substr($s['created_at'], 0, 10)) ?></td>
            <td>
              <a href="?tab=manage_student&username=<?= urlencode($s['username']) ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">مدیریت</a>
              <a href="?tab=students&delete_user=<?= $s['id'] ?>"
                 class="btn-del"
                 onclick="return confirm('حذف این دانش‌آموز؟')">حذف</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php elseif ($tab === 'manage_student' && isset($_GET['username'])):
    $db = getDB();
    $target_user = $_GET['username'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$target_user]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student_info):
      echo "<div class='alert error'>❌ دانش‌آموز یافت نشد.</div>";
    else:
      $t_stmt = $db->prepare("SELECT * FROM tuition WHERE national_id = ? ORDER BY installment_no ASC");
      $t_stmt->execute([$target_user]);
      $student_tuition = $t_stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>⚙️ مدیریت پروفایل: <?= htmlspecialchars($student_info['full_name']) ?></h3>
    <form method="POST">
      <input type="hidden" name="old_username" value="<?= htmlspecialchars($student_info['username']) ?>">
      <div class="field">
        <label>نام و نام خانوادگی</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($student_info['full_name']) ?>">
      </div>
      <div class="field">
        <label>کد ملی (نام کاربری)</label>
        <input type="text" name="new_username" value="<?= htmlspecialchars($student_info['username']) ?>">
      </div>
      <button type="submit" name="update_student_profile" class="btn-primary">بروزرسانی پروفایل</button>
    </form>
  </div>

  <div class="card">
    <h3>💰 ثبت پرداختی خودکار</h3>
    <p style="font-size: 0.8rem; margin-bottom: 10px; color: var(--gray);">مبلغ وارد شده به ترتیب از اولین قسط پرداخت نشده کسر می‌شود.</p>
    <form method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
      <input type="hidden" name="national_id" value="<?= htmlspecialchars($student_info['username']) ?>">
      <div class="field" style="flex: 1; margin-bottom: 0;">
        <label>مبلغ پرداختی (تومان)</label>
        <input type="text" name="pay_amount" placeholder="مثلا 5,000,000">
      </div>
      <div class="field" style="flex: 1; margin-bottom: 0;">
        <label>تاریخ پرداخت</label>
        <input type="text" name="pay_date" value="<?= get_jalali_today() ?>">
      </div>
      <button type="submit" name="register_payment_auto" class="btn-primary" style="width: auto; padding: 11px 24px;">ثبت و توزیع</button>
    </form>
  </div>

  <div class="card">
    <h3>📋 لیست اقساط</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>شرح</th>
            <th>مبلغ</th>
            <th>پرداختی</th>
            <th>سررسید</th>
            <th>تاریخ پرداخت</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($student_tuition as $row): ?>
          <form method="POST">
            <input type="hidden" name="tuition_id" value="<?= $row['id'] ?>">
            <tr>
              <td><input type="text" name="installment_no" value="<?= $row['installment_no'] ?>" style="width:40px; padding:5px;"></td>
              <td><input type="text" name="description" value="<?= htmlspecialchars($row['description']) ?>" style="width:100px; padding:5px;"></td>
              <td><input type="text" name="amount" value="<?= number_format($row['amount']) ?>" style="width:100px; padding:5px;"></td>
              <td><input type="text" name="paid_amount" value="<?= number_format($row['paid_amount']) ?>" style="width:100px; padding:5px;"></td>
              <td><input type="text" name="due_date" value="<?= htmlspecialchars($row['due_date']) ?>" style="width:90px; padding:5px;"></td>
              <td><input type="text" name="paid_date" value="<?= htmlspecialchars($row['paid_date']) ?>" style="width:90px; padding:5px;"></td>
              <td>
                <select name="status" style="padding:5px; border-radius:8px; font-family:Vazirmatn; font-size:0.8rem;">
                  <option value="unpaid" <?= $row['status']=='unpaid'?'selected':'' ?>>پرداخت نشده</option>
                  <option value="partial" <?= $row['status']=='partial'?'selected':'' ?>>ناقص</option>
                  <option value="paid" <?= $row['status']=='paid'?'selected':'' ?>>پرداخت شده</option>
                </select>
              </td>
              <td style="white-space: nowrap;">
                <button type="submit" name="edit_tuition_row" class="btn-sm" style="background:var(--green)">ذخیره</button>
                <a href="?tab=manage_student&username=<?= urlencode($target_user) ?>&delete_tuition_id=<?= $row['id'] ?>"
                   class="btn-del" onclick="return confirm('حذف این قسط؟')">حذف</a>
              </td>
            </tr>
          </form>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3>➕ افزودن قسط جدید برای این دانش‌آموز</h3>
    <form method="POST">
      <input type="hidden" name="national_id" value="<?= htmlspecialchars($student_info['username']) ?>">
      <div class="field"><label>شماره قسط</label><input type="text" name="installment_no"></div>
      <div class="field"><label>شرح</label><input type="text" name="description"></div>
      <div class="field"><label>مبلغ</label><input type="text" name="amount"></div>
      <div class="field"><label>تاریخ سررسید</label><input type="text" name="due_date"></div>
      <button type="submit" name="add_tuition_manual" class="btn-primary">افزودن قسط</button>
    </form>
  </div>
  <?php endif; ?>
  <?php endif; ?>

</main>
<?php endif; ?>

</body>
</html>
