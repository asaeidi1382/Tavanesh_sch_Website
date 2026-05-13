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

// ─── مدیریت سال تحصیلی ───
$academic_years = ['1404-1405', '1405-1406'];
if (!isset($_SESSION['active_year'])) {
    $_SESSION['active_year'] = '1404-1405';
}
if (isset($_POST['set_active_year'])) {
    $_SESSION['active_year'] = $_POST['active_year'];
}
$active_year = $_SESSION['active_year'];

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

            $result = upsertStudent($national_id, $full_name, $active_year);
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
            if (count($row) < 3) { $skipped++; continue; }

            $national_id    = trim($row[0]);
            $installment_no = (int)trim($row[1]);
            $amount         = (int)str_replace([',', '،'], '', trim($row[2]));
            $due_date       = trim($row[3] ?? '');
            $paid_amount    = (int)str_replace([',', '،'], '', trim($row[4] ?? 0));
            $paid_date      = trim($row[5] ?? '');
            $status         = trim($row[6] ?? 'unpaid');
            $description    = '';
            if (!in_array($status, ['paid','partial','unpaid'])) $status = 'unpaid';

            if (!$national_id || !$installment_no) { $skipped++; continue; }

            // بررسی وجود رکورد
            $check = $db->prepare("SELECT id FROM tuition WHERE national_id=? AND installment_no=? AND academic_year=?");
            $check->execute([$national_id, $installment_no, $active_year]);
            $existing = $check->fetch();

            if ($existing) {
                $db->prepare("UPDATE tuition SET description=?,amount=?,due_date=?,paid_amount=?,paid_date=?,status=? WHERE national_id=? AND installment_no=? AND academic_year=?")
                   ->execute([$description,$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$national_id,$installment_no,$active_year]);
                $updated++;
            } else {
                $db->prepare("INSERT INTO tuition (national_id,installment_no,description,amount,due_date,paid_amount,paid_date,status,academic_year) VALUES (?,?,?,?,?,?,?,?,?)")
                   ->execute([$national_id,$installment_no,$description,$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$active_year]);
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

        $result = upsertStudent($national_id, $full_name, $active_year);

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

    // تاریخ سررسید
    $due_y = $_POST['due_y'] ?? '';
    $due_m = $_POST['due_m'] ?? '';
    $due_d = $_POST['due_d'] ?? '';
    $due_date = ($due_y && $due_m && $due_d) ? sprintf("%04d/%02d/%02d", $due_y, $due_m, $due_d) : '';

    $paid_amount    = (int)str_replace(',', '', $_POST['paid_amount'] ?? 0);

    // تاریخ پرداخت
    $paid_y = $_POST['paid_y'] ?? '';
    $paid_m = $_POST['paid_m'] ?? '';
    $paid_d = $_POST['paid_d'] ?? '';
    $paid_date = ($paid_y && $paid_m && $paid_d) ? sprintf("%04d/%02d/%02d", $paid_y, $paid_m, $paid_d) : '';

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
                status,
                academic_year
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $national_id,
            $installment_no,
            $description,
            $amount,
            $due_date,
            $paid_amount,
            $paid_date ?: null,
            $status,
            $active_year
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
    $new_password = trim($_POST['new_password'] ?? '');

    // Metadata
    $grade         = $_POST['grade'] ?? '';
    $major         = $_POST['major'] ?? '';
    $father_name   = $_POST['father_name'] ?? '';
    $mother_name   = $_POST['mother_name'] ?? '';
    $mother_phone  = $_POST['mother_phone'] ?? '';
    $father_phone  = $_POST['father_phone'] ?? '';
    $home_phone    = $_POST['home_phone'] ?? '';
    $student_phone = $_POST['student_phone'] ?? '';
    $address       = $_POST['address'] ?? '';
    $left_handed   = isset($_POST['left_handed']) ? 1 : 0;

    try {
        $db->beginTransaction();

        // Update users table
        if ($new_password) {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET username=?, full_name=?, password=? WHERE username=?");
            $stmt->execute([$new_username, $full_name, $hash, $old_username]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username=?, full_name=? WHERE username=?");
            $stmt->execute([$new_username, $full_name, $old_username]);
        }

        if ($new_username !== $old_username) {
            $db->prepare("UPDATE tuition SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
            $db->prepare("UPDATE student_profiles SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
        }

        // Update student_profiles table for active year
        $stmt = $db->prepare("UPDATE student_profiles SET
            grade=?, major=?, father_name=?, mother_name=?, mother_phone=?, father_phone=?,
            home_phone=?, student_phone=?, address=?, left_handed=?
            WHERE national_id=? AND academic_year=?");
        $stmt->execute([
            $grade, $major, $father_name, $mother_name, $mother_phone, $father_phone,
            $home_phone, $student_phone, $address, $left_handed,
            $new_username, $active_year
        ]);

        $db->commit();
        $msgs[] = ['type'=>'success', 'text'=>'✅ پروفایل و اطلاعات تکمیلی دانش‌آموز بروزرسانی شد.'];
        $_GET['username'] = $new_username;
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
    $description = ''; // Removed from UI
    $amount = (int)str_replace(',', '', $_POST['amount']);

    // تاریخ سررسید
    $due_y = $_POST['due_y'] ?? '';
    $due_m = $_POST['due_m'] ?? '';
    $due_d = $_POST['due_d'] ?? '';
    $due_date = ($due_y && $due_m && $due_d) ? sprintf("%04d/%02d/%02d", $due_y, $due_m, $due_d) : '';

    $paid_amount = (int)str_replace(',', '', $_POST['paid_amount']);

    // تاریخ پرداخت
    $paid_y = $_POST['paid_y'] ?? '';
    $paid_m = $_POST['paid_m'] ?? '';
    $paid_d = $_POST['paid_d'] ?? '';
    $paid_date = ($paid_y && $paid_m && $paid_d) ? sprintf("%04d/%02d/%02d", $paid_y, $paid_m, $paid_d) : '';

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

// ─── مدیریت اخبار ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $db = getDB();
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $content = trim($_POST['content']);
    $video_embed = trim($_POST['video_embed']);

    if (!is_dir('uploads/news')) {
        mkdir('uploads/news', 0777, true);
    }

    $uploaded_images = [];
    if (!empty($_FILES['news_images']['name'][0])) {
        foreach ($_FILES['news_images']['tmp_name'] as $key => $tmp_name) {
            $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES['news_images']['name'][$key]);
            $target = "uploads/news/" . $filename;
            if (move_uploaded_file($tmp_name, $target)) {
                $uploaded_images[] = $target;
            }
        }
    }

    $images_json = json_encode($uploaded_images);

    $stmt = $db->prepare("INSERT INTO news (title, date, content, images, video_embed) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $date, $content, $images_json, $video_embed]);
    $msgs[] = ['type'=>'success', 'text'=>'✅ خبر با موفقیت ثبت شد.'];
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_news'])) {
    $db = getDB();
    $id = (int)$_POST['news_id'];
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $content = trim($_POST['content']);
    $video_embed = trim($_POST['video_embed']);

    // واکشی تصاویر قبلی
    $stmt = $db->prepare("SELECT images FROM news WHERE id=?");
    $stmt->execute([$id]);
    $old_news = $stmt->fetch();
    $images = json_decode($old_news['images'], true) ?: [];

    if (!is_dir('uploads/news')) {
        mkdir('uploads/news', 0777, true);
    }

    // افزودن تصاویر جدید
    if (!empty($_FILES['news_images']['name'][0])) {
        foreach ($_FILES['news_images']['tmp_name'] as $key => $tmp_name) {
            if (!$tmp_name) continue;
            $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES['news_images']['name'][$key]);
            $target = "uploads/news/" . $filename;
            if (move_uploaded_file($tmp_name, $target)) {
                $images[] = $target;
            }
        }
    }

    // حذف تصاویر انتخاب شده
    if (isset($_POST['remove_images']) && is_array($_POST['remove_images'])) {
        foreach ($_POST['remove_images'] as $img_to_remove) {
            if (($key = array_search($img_to_remove, $images)) !== false) {
                unset($images[$key]);
                if (file_exists($img_to_remove)) unlink($img_to_remove);
            }
        }
        $images = array_values($images);
    }

    $images_json = json_encode($images);
    $stmt = $db->prepare("UPDATE news SET title=?, date=?, content=?, images=?, video_embed=? WHERE id=?");
    $stmt->execute([$title, $date, $content, $images_json, $video_embed, $id]);
    $msgs[] = ['type'=>'success', 'text'=>'✅ خبر بروزرسانی شد.'];
}

if ($isAdmin && isset($_GET['delete_news'])) {
    $db = getDB();
    $id = (int)$_GET['delete_news'];

    // ابتدا تصاویر را حذف می‌کنیم (اختیاری اما بهتر است)
    $stmt = $db->prepare("SELECT images FROM news WHERE id=?");
    $stmt->execute([$id]);
    $news_item = $stmt->fetch();
    if ($news_item) {
        $images = json_decode($news_item['images'], true);
        if (is_array($images)) {
            foreach ($images as $img) {
                if (file_exists($img)) unlink($img);
            }
        }
    }

    $db->prepare("DELETE FROM news WHERE id=?")->execute([$id]);
    header('Location: admin.php?tab=news');
    exit;
}

// ─── ثبت پرداختی خودکار (توزیع بین اقساط) ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_payment_auto'])) {
    $db = getDB();
    $national_id = $_POST['national_id'];
    $pay_amount = (int)str_replace(',', '', $_POST['pay_amount']);

    $py = $_POST['pay_y'] ?? '';
    $pm = $_POST['pay_m'] ?? '';
    $pd = $_POST['pay_d'] ?? '';
    $pay_date = ($py && $pm && $pd) ? sprintf("%04d/%02d/%02d", $py, $pm, $pd) : '';

    if ($pay_amount > 0) {
        $stmt = $db->prepare("SELECT * FROM tuition WHERE national_id=? AND academic_year=? AND status != 'paid' ORDER BY installment_no ASC");
        $stmt->execute([$national_id, $active_year]);
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
    // دانش‌آموزانی که در سال تحصیلی فعال رکورد دارند یا اگر سال پیش‌فرض است، همه
    if ($active_year === '1404-1405') {
        $students = $db->query("SELECT id, username, full_name, created_at FROM users ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->prepare("SELECT u.id, u.username, u.full_name, u.created_at FROM users u JOIN student_profiles sp ON u.username = sp.national_id WHERE sp.academic_year = ? ORDER BY u.full_name ASC");
        $stmt->execute([$active_year]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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
    <div style="display:flex; align-items:center; gap:15px;">
        <form method="POST" style="display:flex; align-items:center; gap:5px; background:rgba(255,255,255,0.1); padding:5px 10px; border-radius:10px;">
            <label style="color:#fff; margin-bottom:0; font-size:0.75rem;">سال تحصیلی:</label>
            <select name="active_year" onchange="this.form.submit()" style="background:transparent; border:none; color:#fff; font-family:Vazirmatn; font-size:0.85rem; outline:none; cursor:pointer;">
                <?php foreach ($academic_years as $y): ?>
                    <option value="<?= $y ?>" <?= $y===$active_year?'selected':'' ?> style="color:#000;"><?= $y ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="set_active_year" value="1">
        </form>
        <a href="admin.php?logout_admin=1" class="btn-sm">خروج از پنل</a>
    </div>
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
    <a href="?tab=debtors"   class="tab <?= $tab==='debtors'   ?'active':'' ?>">📉 لیست بدهکاران</a>
    <a href="?tab=news"      class="tab <?= $tab==='news'      ?'active':'' ?>">📰 مدیریت اخبار</a>
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
      <label>مبلغ</label>
      <input type="text" name="amount">
    </div>

    <div class="field">
      <label>تاریخ سررسید</label>
      <div style="display:flex; gap:5px; direction:rtl;">
        <select name="due_d" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">روز</option>
            <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="due_m" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">ماه</option>
            <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="due_y" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">سال</option>
            <option value="1403">1403</option>
            <option value="1404">1404</option>
            <option value="1405" selected>1405</option>
            <option value="1406">1406</option>
        </select>
      </div>
    </div>

    <div class="field">
      <label>مبلغ پرداخت شده</label>
      <input type="text" name="paid_amount" value="0">
    </div>

    <div class="field">
      <label>تاریخ پرداخت</label>
      <div style="display:flex; gap:5px; direction:rtl;">
        <select name="paid_d" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">روز</option>
            <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="paid_m" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">ماه</option>
            <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i</option>"; ?>
        </select>
        <select name="paid_y" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
            <option value="">سال</option>
            <option value="1403">1403</option>
            <option value="1404">1404</option>
            <option value="1405" selected>1405</option>
            <option value="1406">1406</option>
        </select>
      </div>
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
      <strong>فرمت فایل CSV اقساط (۷ ستون):</strong><br>
      <code>کد_ملی , شماره_قسط , مبلغ , تاریخ_سررسید , پرداخت_شده , تاریخ_پرداخت , وضعیت</code><br><br>
      <strong>مثال:</strong><br>
      <code>1234567890,1,5000000,1403/07/01,5000000,1403/06/28,paid</code><br>
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

    <div class="field" style="margin-bottom: 20px;">
        <input type="text" id="studentSearch" placeholder="🔍 جستجوی نام یا کد ملی..." onkeyup="filterStudents()" style="padding: 12px 15px; border-radius: 12px; border: 1.5px solid var(--turquoise-light); width: 100%; font-family: Vazirmatn;">
    </div>

    <div class="table-wrap">
      <table id="studentTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)" style="cursor:pointer;"># ↕</th>
            <th onclick="sortTable(1)" style="cursor:pointer;">نام و نام خانوادگی ↕</th>
            <th onclick="sortTable(2)" style="cursor:pointer;">کد ملی (نام کاربری) ↕</th>
            <th onclick="sortTable(3)" style="cursor:pointer;">تاریخ ثبت ↕</th>
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
      $stmt = $db->prepare("SELECT * FROM student_profiles WHERE national_id = ? AND academic_year = ?");
      $stmt->execute([$target_user, $active_year]);
      $prof = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

      $t_stmt = $db->prepare("SELECT * FROM tuition WHERE national_id = ? AND academic_year = ? ORDER BY installment_no ASC");
      $t_stmt->execute([$target_user, $active_year]);
      $student_tuition = $t_stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>⚙️ مدیریت پروفایل: <?= htmlspecialchars($student_info['full_name']) ?> (سال <?= $active_year ?>)</h3>
    <form method="POST">
      <input type="hidden" name="old_username" value="<?= htmlspecialchars($student_info['username']) ?>">

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
          <div class="field">
            <label>نام و نام خانوادگی</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($student_info['full_name']) ?>">
          </div>
          <div class="field">
            <label>کد ملی (نام کاربری)</label>
            <input type="text" name="new_username" value="<?= htmlspecialchars($student_info['username']) ?>">
          </div>
          <div class="field">
            <label>رمز عبور جدید (خالی بماند = بدون تغییر)</label>
            <input type="password" name="new_password" placeholder="********">
          </div>
          <div class="field">
            <label>پایه تحصیلی</label>
            <select name="grade" style="width:100%; padding:11px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
                <option value="">انتخاب کنید</option>
                <?php foreach(['دهم','یازدهم','دوازدهم'] as $g) echo "<option value='$g' ".((($prof['grade']??'')==$g)?'selected':'').">$g</option>"; ?>
            </select>
          </div>
          <div class="field">
            <label>رشته تحصیلی</label>
            <select name="major" style="width:100%; padding:11px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
                <option value="">انتخاب کنید</option>
                <?php foreach(['ریاضی','تجربی','انسانی'] as $m) echo "<option value='$m' ".((($prof['major']??'')==$m)?'selected':'').">$m</option>"; ?>
            </select>
          </div>
          <div class="field">
            <label>نام پدر</label>
            <input type="text" name="father_name" value="<?= htmlspecialchars($prof['father_name']??'') ?>">
          </div>
          <div class="field">
            <label>تلفن پدر</label>
            <input type="text" name="father_phone" value="<?= htmlspecialchars($prof['father_phone']??'') ?>">
          </div>
          <div class="field">
            <label>نام مادر</label>
            <input type="text" name="mother_name" value="<?= htmlspecialchars($prof['mother_name']??'') ?>">
          </div>
          <div class="field">
            <label>تلفن مادر</label>
            <input type="text" name="mother_phone" value="<?= htmlspecialchars($prof['mother_phone']??'') ?>">
          </div>
          <div class="field">
            <label>تلفن ثابت منزل</label>
            <input type="text" name="home_phone" value="<?= htmlspecialchars($prof['home_phone']??'') ?>">
          </div>
          <div class="field">
            <label>تلفن همراه دانش‌آموز</label>
            <input type="text" name="student_phone" value="<?= htmlspecialchars($prof['student_phone']??'') ?>">
          </div>
          <div class="field" style="display:flex; align-items:center; gap:10px; margin-top:25px;">
            <input type="checkbox" name="left_handed" id="lh" <?= ($prof['left_handed']??0)?'checked':'' ?>>
            <label for="lh" style="margin-bottom:0;">دانش‌آموز چپ‌دست است</label>
          </div>
      </div>

      <div class="field">
        <label>آدرس منزل</label>
        <textarea name="address" rows="3" style="width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:11px; font-family:Vazirmatn;"><?= htmlspecialchars($prof['address']??'') ?></textarea>
      </div>

      <button type="submit" name="update_student_profile" class="btn-primary">بروزرسانی پروفایل و اطلاعات تکمیلی</button>
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
        <?php
            $today = get_jalali_today();
            list($ty, $tm, $td) = explode('/', $today);
        ?>
        <div style="display:flex; gap:5px; direction:rtl;">
            <select name="pay_d" id="pay_d" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$td==$i?'selected':'').">$i</option>"; ?>
            </select>
            <select name="pay_m" id="pay_m" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$tm==$i?'selected':'').">$i</option>"; ?>
            </select>
            <select name="pay_y" id="pay_y" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php foreach(['1403','1404','1405','1406'] as $y) echo "<option value='$y' ".($ty==$y?'selected':'').">$y</option>"; ?>
            </select>
        </div>
      </div>
      <button type="submit" name="register_payment_auto" class="btn-primary" style="width: auto; padding: 11px 24px;">ثبت و توزیع</button>
    </form>
    <button onclick="setToday()" class="btn-sm" style="margin-top:10px; background:var(--gray);">تاریخ امروز</button>
  </div>

  <script>
  function setToday() {
      const today = '<?= get_jalali_today() ?>';
      const parts = today.split('/');
      document.getElementById('pay_y').value = parts[0];
      document.getElementById('pay_m').value = parts[1];
      document.getElementById('pay_d').value = parts[2];
  }
  </script>

  <div class="card">
    <h3>📋 لیست اقساط</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>مبلغ</th>
            <th>پرداختی</th>
            <th>سررسید</th>
            <th>تاریخ پرداخت</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($student_tuition as $row):
            $formId = "form_edit_" . $row['id'];
          ?>
          <tr>
            <td>
              <form id="<?= $formId ?>" method="POST">
                <input type="hidden" name="tuition_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="edit_tuition_row" value="1">
              </form>
              <input form="<?= $formId ?>" type="text" name="installment_no" value="<?= $row['installment_no'] ?>" style="width:40px; padding:5px;">
            </td>
            <td><input form="<?= $formId ?>" type="text" name="amount" value="<?= number_format($row['amount']) ?>" style="width:100px; padding:5px;"></td>
            <td><input form="<?= $formId ?>" type="text" name="paid_amount" value="<?= number_format($row['paid_amount']) ?>" style="width:100px; padding:5px;"></td>
            <td>
                <?php
                    $d_y = $d_m = $d_d = "";
                    if (!empty($row['due_date'])) {
                        list($d_y, $d_m, $d_d) = explode('/', $row['due_date']);
                    }
                ?>
                <div style="display:flex; gap:2px; direction:rtl;">
                    <select form="<?= $formId ?>" name="due_d" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">روز</option>
                        <?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_d==$i?'selected':'').">$i</option>"; ?>
                    </select>
                    <select form="<?= $formId ?>" name="due_m" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">ماه</option>
                        <?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_m==$i?'selected':'').">$i</option>"; ?>
                    </select>
                    <select form="<?= $formId ?>" name="due_y" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">سال</option>
                        <?php foreach(['1403','1404','1405','1406'] as $y) echo "<option value='$y' ".($d_y==$y?'selected':'').">$y</option>"; ?>
                    </select>
                </div>
            </td>
            <td>
                <?php
                    $p_y = $p_m = $p_d = "";
                    if (!empty($row['paid_date'])) {
                        list($p_y, $p_m, $p_d) = explode('/', $row['paid_date']);
                    }
                ?>
                <div style="display:flex; gap:2px; direction:rtl;">
                    <select form="<?= $formId ?>" name="paid_d" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">روز</option>
                        <?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$p_d==$i?'selected':'').">$i</option>"; ?>
                    </select>
                    <select form="<?= $formId ?>" name="paid_m" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">ماه</option>
                        <?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$p_m==$i?'selected':'').">$i</option>"; ?>
                    </select>
                    <select form="<?= $formId ?>" name="paid_y" style="font-size:0.9rem; padding:2px; font-family:Vazirmatn;">
                        <option value="">سال</option>
                        <?php foreach(['1403','1404','1405','1406'] as $y) echo "<option value='$y' ".($p_y==$y?'selected':'').">$y</option>"; ?>
                    </select>
                </div>
            </td>
            <td>
              <select form="<?= $formId ?>" name="status" style="padding:5px; border-radius:8px; font-family:Vazirmatn; font-size:0.8rem;">
                <option value="unpaid" <?= $row['status']=='unpaid'?'selected':'' ?>>پرداخت نشده</option>
                <option value="partial" <?= $row['status']=='partial'?'selected':'' ?>>ناقص</option>
                <option value="paid" <?= $row['status']=='paid'?'selected':'' ?>>پرداخت شده</option>
              </select>
            </td>
            <td style="white-space: nowrap;">
              <button form="<?= $formId ?>" type="submit" class="btn-sm" style="background:var(--green)">ذخیره</button>
              <a href="?tab=manage_student&username=<?= urlencode($target_user) ?>&delete_tuition_id=<?= $row['id'] ?>"
                 class="btn-del" onclick="return confirm('حذف این قسط؟')">حذف</a>
            </td>
          </tr>
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
      <div class="field"><label>مبلغ</label><input type="text" name="amount"></div>
      <div class="field">
        <label>تاریخ سررسید</label>
        <div style="display:flex; gap:5px; direction:rtl;">
            <select name="due_d" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <option value="">روز</option>
                <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>$i</option>"; ?>
            </select>
            <select name="due_m" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <option value="">ماه</option>
                <?php for($i=1; $i<=12; $i++) echo "<option value='$i'>$i</option>"; ?>
            </select>
            <select name="due_y" style="flex:1; padding:10px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <option value="">سال</option>
                <option value="1403">1403</option>
                <option value="1404">1404</option>
                <option value="1405" selected>1405</option>
                <option value="1406">1406</option>
            </select>
        </div>
      </div>
      <button type="submit" name="add_tuition_manual" class="btn-primary">افزودن قسط</button>
    </form>
  </div>
  <?php endif; ?>

  <?php elseif ($tab === 'debtors'):
    $ref_date = $_POST['ref_date'] ?? get_jalali_today();
    $db = getDB();

    // واکشی همه کاربران و محاسبات بدهی
    $all_users = $db->query("SELECT username, full_name FROM users ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $debtors = [];

    foreach ($all_users as $u) {
        $stmt = $db->prepare("SELECT SUM(amount) as total_due, SUM(paid_amount) as total_paid FROM tuition WHERE national_id = ? AND academic_year = ? AND due_date <= ?");
        $stmt->execute([$u['username'], $active_year, $ref_date]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_due  = (int)$res['total_due'];
        $total_paid = (int)$res['total_paid'];
        $debt = $total_due - $total_paid;

        if ($debt > 0) {
            $debtors[] = [
                'full_name' => $u['full_name'],
                'username'  => $u['username'],
                'total_due' => $total_due,
                'total_paid'=> $total_paid,
                'debt'      => $debt
            ];
        }
    }
  ?>
  <div class="card">
    <h3>📉 گزارش بدهکاران</h3>
    <form method="POST" style="display:flex; gap:10px; align-items:flex-end; margin-bottom:20px;">
      <div class="field" style="flex:1; margin-bottom:0;">
        <label>تاریخ مرجع (بدهی تا این تاریخ)</label>
        <input type="text" name="ref_date" value="<?= htmlspecialchars($ref_date) ?>" placeholder="1405/01/01">
      </div>
      <button type="submit" class="btn-primary" style="width:auto; padding:11px 24px;">بروزرسانی گزارش</button>
    </form>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>نام دانش‌آموز</th>
            <th>کد ملی</th>
            <th>مبلغ سررسید شده</th>
            <th>مبلغ پرداخت شده</th>
            <th>بدهی</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($debtors)): ?>
            <tr><td colspan="6" style="text-align:center; padding:30px; color:var(--gray);">هیچ بدهکاری تا این تاریخ یافت نشد.</td></tr>
          <?php endif; ?>
          <?php foreach ($debtors as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['full_name']) ?></td>
            <td><?= htmlspecialchars($d['username']) ?></td>
            <td><?= number_format($d['total_due']) ?> تومان</td>
            <td><?= number_format($d['total_paid']) ?> تومان</td>
            <td style="color:var(--red); font-weight:bold;"><?= number_format($d['debt']) ?> تومان</td>
            <td>
              <a href="?tab=manage_student&username=<?= urlencode($d['username']) ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">مدیریت</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php elseif ($tab === 'news'):
    $db = getDB();
    $all_news = $db->query("SELECT * FROM news ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <?php if (isset($_GET['edit_news'])):
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([(int)$_GET['edit_news']]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_item):
      $imgs = json_decode($edit_item['images'], true) ?: [];
  ?>
  <div class="card">
    <h3>✏️ ویرایش خبر</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="news_id" value="<?= $edit_item['id'] ?>">
      <div class="field">
        <label>عنوان خبر</label>
        <input type="text" name="title" value="<?= htmlspecialchars($edit_item['title']) ?>" required>
      </div>
      <div class="field">
        <label>تاریخ</label>
        <input type="text" name="date" value="<?= htmlspecialchars($edit_item['date']) ?>" required>
      </div>
      <div class="field">
        <label>متن خبر</label>
        <textarea name="content" rows="10" style="width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:11px 14px; font-family:Vazirmatn;"><?= htmlspecialchars($edit_item['content']) ?></textarea>
      </div>
      <div class="field">
        <label>تصاویر فعلی (جهت حذف انتخاب کنید)</label>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
          <?php foreach ($imgs as $img): ?>
            <div style="position:relative; width:100px; height:100px;">
              <img src="<?= htmlspecialchars($img) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
              <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img) ?>" style="position:absolute; top:5px; right:5px;">
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field">
        <label>افزودن تصاویر جدید</label>
        <input type="file" name="news_images[]" multiple accept="image/*">
      </div>
      <div class="field">
        <label>کد امبد ویدیو</label>
        <input type="text" name="video_embed" value="<?= htmlspecialchars($edit_item['video_embed']) ?>">
      </div>
      <div style="display:flex; gap:10px;">
        <button type="submit" name="update_news" class="btn-primary">بروزرسانی خبر</button>
        <a href="?tab=news" class="btn-sm" style="background:var(--gray); text-decoration:none; padding:12px 20px; border-radius:12px;">انصراف</a>
      </div>
    </form>
  </div>
  <?php endif; endif; ?>

  <div class="card">
    <h3>📰 افزودن خبر جدید</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>عنوان خبر</label>
        <input type="text" name="title" required>
      </div>
      <div class="field">
        <label>تاریخ (مثلاً ۱۶ اردیبهشت ۱۴۰۵)</label>
        <input type="text" name="date" value="<?= get_jalali_today() ?>" required>
      </div>
      <div class="field">
        <label>متن خبر</label>
        <textarea name="content" rows="6" style="width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:11px 14px; font-family:Vazirmatn;"></textarea>
      </div>
      <div class="field">
        <label>تصاویر گالری (چند انتخابی)</label>
        <input type="file" name="news_images[]" multiple accept="image/*">
      </div>
      <div class="field">
        <label>کد امبد ویدیو (آپارات)</label>
        <input type="text" name="video_embed" placeholder='<script type="text/JavaScript" src="..."></script>'>
      </div>
      <button type="submit" name="add_news" class="btn-primary">انتشار خبر</button>
    </form>
  </div>

  <div class="card">
    <h3>📋 لیست اخبار منتشر شده</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>عنوان</th>
            <th>تاریخ</th>
            <th>تصاویر</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($all_news as $n):
            $imgs = json_decode($n['images'], true) ?: [];
          ?>
          <tr>
            <td><?= htmlspecialchars($n['title']) ?></td>
            <td><?= htmlspecialchars($n['date']) ?></td>
            <td><?= count($imgs) ?> تصویر</td>
            <td>
              <a href="?tab=news&edit_news=<?= $n['id'] ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">ویرایش</a>
              <a href="?tab=news&delete_news=<?= $n['id'] ?>" class="btn-del" onclick="return confirm('آیا از حذف این خبر اطمینان دارید؟')">حذف</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($all_news)): ?>
            <tr><td colspan="4" style="text-align:center; padding:20px;">هیچ خبری ثبت نشده است.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</main>
<?php endif; ?>

<script>
function filterStudents() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("studentSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("studentTable");
    tr = table.getElementsByTagName("tr");
    for (i = 1; i < tr.length; i++) {
        tr[i].style.display = "none";
        tds = tr[i].getElementsByTagName("td");
        for (var j = 1; j < tds.length - 1; j++) {
            if (tds[j]) {
                txtValue = tds[j].textContent || tds[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                    break;
                }
            }
        }
    }
}

function sortTable(n) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.getElementById("studentTable");
    switching = true;
    dir = "asc";
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[n];
            y = rows[i + 1].getElementsByTagName("TD")[n];
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}
</script>

</body>
</html>
