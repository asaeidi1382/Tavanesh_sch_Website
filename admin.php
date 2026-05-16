<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'auth.php';
require_once 'vendor/autoload.php';
use Shuchkin\SimpleXLSX;

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

$isAdmin = !empty($_SESSION['is_admin']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// ─── مدیریت سال تحصیلی ───
$academic_years = ['1404-1405', '1405-1406'];
if (isset($_POST['set_active_year'])) {
    $db = getDB();
    $new_year = $_POST['active_year'];
    $_SESSION['active_year'] = $new_year;
    // ذخیره در دیتابیس برای پایداری
    $stmt = $db->prepare("UPDATE settings SET value = ? WHERE key = 'active_year'");
    $stmt->execute([$new_year]);
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
            if ($rowNum === 1 && (trim($row[0]) === 'first_name' || trim($row[0]) === 'نام')) continue;
            if (count($row) < 3) { $skipped++; continue; }

            $first_name  = trim($row[0]);
            $last_name   = trim($row[1]);
            $national_id = trim($row[2]);
            if (!$first_name || !$last_name || !$national_id) { $skipped++; continue; }

            $result = upsertStudent($national_id, $first_name, $last_name, $active_year);
            if ($result === 'created') $created++;
            else $updated++;
        }
        fclose($file);
        $msgs[] = ['type'=>'success', 'text'=>"✅ دانش‌آموزان: ایجاد شده: $created | به‌روز: $updated | رد شده: $skipped"];
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل CSV انتخاب نشده.'];
    }
}

// ─── ایمپورت دانش‌آموزان از Excel ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_students_excel'])) {
    if (!empty($_FILES['students_excel']['tmp_name'])) {
        if ($xlsx = SimpleXLSX::parse($_FILES['students_excel']['tmp_name'])) {
            $created = $updated = $skipped = 0;
            foreach ($xlsx->rows() as $i => $row) {
                if ($i === 0) continue; // Skip header
                if (count($row) < 3) { $skipped++; continue; }

                $first_name  = trim($row[0]);
                $last_name   = trim($row[1]);
                $national_id = trim($row[2]);
                if (!$first_name || !$last_name || !$national_id) { $skipped++; continue; }

                $extra = [
                    'grade'         => $row[3] ?? '',
                    'major'         => $row[4] ?? '',
                    'father_name'   => $row[5] ?? '',
                    'mother_name'   => $row[6] ?? '',
                    'mother_phone'  => $row[7] ?? '',
                    'father_phone'  => $row[8] ?? '',
                    'home_phone'    => $row[9] ?? '',
                    'left_handed'   => (isset($row[10]) && ($row[10] == 1 || $row[10] == 'بله')) ? 1 : 0,
                    'seat_no'       => $row[11] ?? '',
                    'address'       => $row[12] ?? '',
                    'student_phone' => $row[13] ?? ''
                ];

                $result = upsertStudent($national_id, $first_name, $last_name, $active_year, $extra);
                if ($result === 'created') $created++;
                else $updated++;
            }
            $msgs[] = ['type'=>'success', 'text'=>"✅ دانش‌آموزان (Excel): ایجاد شده: $created | به‌روز: $updated | رد شده: $skipped"];
        } else {
            $msgs[] = ['type'=>'error', 'text'=>'❌ خطا در خواندن فایل Excel: ' . SimpleXLSX::parseError()];
        }
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل Excel انتخاب نشده.'];
    }
}

// ─── ایمپورت کارکنان از Excel ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_staff_excel'])) {
    if (!empty($_FILES['staff_excel']['tmp_name'])) {
        if ($xlsx = SimpleXLSX::parse($_FILES['staff_excel']['tmp_name'])) {
            $created = $updated = $skipped = 0;
            foreach ($xlsx->rows() as $i => $row) {
                if ($i === 0) continue; // Skip header
                if (count($row) < 3) { $skipped++; continue; }

                $first_name  = trim($row[0]);
                $last_name   = trim($row[1]);
                $national_id = trim($row[2]);
                if (!$first_name || !$last_name || !$national_id) { $skipped++; continue; }

                $extra = [
                    'birth_date'    => $row[3] ?? '',
                    'birth_place'   => $row[4] ?? '',
                    'education'     => $row[5] ?? '',
                    'position'      => $row[6] ?? '',
                    'father_name'   => $row[7] ?? '',
                    'home_phone'    => $row[8] ?? '',
                    'address'       => $row[9] ?? '',
                    'schedule'      => $row[10] ?? '',
                    'mobile_phone'  => $row[11] ?? '',
                    'contract_date' => $row[12] ?? '',
                    'bank'          => $row[13] ?? '',
                    'sheba'         => $row[14] ?? '',
                    'letter_no'     => $row[15] ?? ''
                ];

                $result = upsertStaff($national_id, $first_name, $last_name, $active_year, $extra);
                if ($result === 'created') $created++;
                else $updated++;
            }
            $msgs[] = ['type'=>'success', 'text'=>"✅ کارکنان (Excel): ایجاد شده: $created | به‌روز: $updated | رد شده: $skipped"];
        } else {
            $msgs[] = ['type'=>'error', 'text'=>'❌ خطا در خواندن فایل Excel: ' . SimpleXLSX::parseError()];
        }
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل Excel انتخاب نشده.'];
    }
}

// ─── ایمپورت پرداخت‌های خودکار از Excel ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_payments_excel'])) {
    if (!empty($_FILES['payments_excel']['tmp_name'])) {
        if ($xlsx = SimpleXLSX::parse($_FILES['payments_excel']['tmp_name'])) {
            $db = getDB();
            $processed = $skipped = 0;
            $errors = [];

            try {
                $db->beginTransaction();
                foreach ($xlsx->rows() as $i => $row) {
                    if ($i === 0) continue; // Skip header
                    $rowNum = $i + 1;
                    if (count($row) < 2) { $skipped++; continue; }

                    $national_id = trim((string)$row[0]);
                    if (stripos($national_id, 'e+') !== false) {
                        $national_id = (string)number_format((float)$national_id, 0, '', '');
                    }
                    if (strpos($national_id, '.') !== false) {
                        $national_id = explode('.', $national_id)[0];
                    }

                    $pay_amount  = (int)preg_replace('/[^0-9]/', '', (string)$row[1]);
                    $raw_date    = trim((string)($row[2] ?? ''));
                    $pay_date    = !empty($raw_date) ? $raw_date : get_jalali_today();

                    if (!$national_id || $pay_amount <= 0) { $skipped++; continue; }

                    // واکشی نام دانش‌آموز برای پیام خطا
                    $u_stmt = $db->prepare("SELECT first_name, last_name FROM student_profiles WHERE national_id = ? AND academic_year = ?");
                    $u_stmt->execute([$national_id, $active_year]);
                    $u_prof = $u_stmt->fetch(PDO::FETCH_ASSOC);
                    $fullName = $u_prof ? trim($u_prof['first_name'] . ' ' . $u_prof['last_name']) : "کد ملی " . $national_id;

                    // واکشی اقساط برای محاسبه سقف پرداخت
                    $stmt = $db->prepare("SELECT * FROM tuition WHERE national_id=? AND academic_year=? AND status != 'paid' ORDER BY installment_no ASC");
                    $stmt->execute([$national_id, $active_year]);
                    $tuition_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $total_debt = 0;
                    foreach ($tuition_rows as $t_row) {
                        $total_debt += ($t_row['amount'] - $t_row['paid_amount']);
                    }

                    if ($pay_amount > $total_debt) {
                        $excess = number_format($pay_amount - $total_debt);
                        $errors[] = "❌ ردیف $rowNum: مبلغ وارد شده برای کاربر «{$fullName}» بیشتر از اقساط تعریف شده برای ایشان است (مبلغ مازاد: $excess تومان).";
                        $skipped++;
                        continue;
                    }

                    $remaining = $pay_amount;
                    foreach ($tuition_rows as $t_row) {
                        if ($remaining <= 0) break;
                        $needed = $t_row['amount'] - $t_row['paid_amount'];
                        if ($needed <= 0) continue;

                        if ($remaining >= $needed) {
                            $new_paid = $t_row['amount'];
                            $new_status = 'paid';
                            $remaining -= $needed;
                        } else {
                            $new_paid = $t_row['paid_amount'] + $remaining;
                            $new_status = 'partial';
                            $remaining = 0;
                        }
                        $db->prepare("UPDATE tuition SET paid_amount=?, status=?, paid_date=? WHERE id=?")
                           ->execute([$new_paid, $new_status, $pay_date, $t_row['id']]);
                    }
                    $processed++;
                }
                $db->commit();
                $msgs[] = ['type'=>'success', 'text'=>"✅ پرداخت‌های خودکار (Excel): پردازش شده: $processed | رد شده: $skipped"];
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $msgs[] = ['type'=>'error', 'text'=>'❌ خطای دیتابیس: ' . $e->getMessage()];
            }

            foreach ($errors as $err) {
                $msgs[] = ['type'=>'error', 'text'=>$err];
            }
        } else {
            $msgs[] = ['type'=>'error', 'text'=>'❌ خطا در خواندن فایل Excel: ' . SimpleXLSX::parseError()];
        }
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل Excel انتخاب نشده.'];
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
            if (!in_array($status, ['paid','partial','unpaid'])) $status = 'unpaid';

            if (!$national_id || !$installment_no) { $skipped++; continue; }

            // بررسی وجود رکورد
            $check = $db->prepare("SELECT id FROM tuition WHERE national_id=? AND installment_no=? AND academic_year=?");
            $check->execute([$national_id, $installment_no, $active_year]);
            $existing = $check->fetch();

            if ($existing) {
                $db->prepare("UPDATE tuition SET amount=?,due_date=?,paid_amount=?,paid_date=?,status=? WHERE national_id=? AND installment_no=? AND academic_year=?")
                   ->execute([$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$national_id,$installment_no,$active_year]);
                $updated++;
            } else {
                $db->prepare("INSERT INTO tuition (national_id,installment_no,amount,due_date,paid_amount,paid_date,status,academic_year) VALUES (?,?,?,?,?,?,?,?)")
                   ->execute([$national_id,$installment_no,$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$active_year]);
                $inserted++;
            }
        }
        fclose($file);
        $msgs[] = ['type'=>'success', 'text'=>"✅ اقساط (CSV): وارد شده: $inserted | به‌روز: $updated | رد شده: $skipped"];
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل CSV انتخاب نشده.'];
    }
}

// ─── ایمپورت اقساط از Excel ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_tuition_excel'])) {
    if (!empty($_FILES['tuition_excel']['tmp_name'])) {
        if ($xlsx = SimpleXLSX::parse($_FILES['tuition_excel']['tmp_name'])) {
            $db = getDB();
            $inserted = $updated = $skipped = 0;
            foreach ($xlsx->rows() as $i => $row) {
                if ($i === 0) continue; // Skip header
                if (count($row) < 3) { $skipped++; continue; }

                $national_id    = trim($row[0]);
                $installment_no = (int)trim($row[1]);
                $amount         = (int)str_replace([',', '،'], '', trim($row[2]));
                $due_date       = trim($row[3] ?? '');
                $paid_amount    = (int)str_replace([',', '،'], '', trim($row[4] ?? 0));
                $paid_date      = trim($row[5] ?? '');
                $status         = trim($row[6] ?? 'unpaid');
                if (!in_array($status, ['paid','partial','unpaid'])) $status = 'unpaid';

                if (!$national_id || !$installment_no) { $skipped++; continue; }

                // بررسی وجود رکورد
                $check = $db->prepare("SELECT id FROM tuition WHERE national_id=? AND installment_no=? AND academic_year=?");
                $check->execute([$national_id, $installment_no, $active_year]);
                $existing = $check->fetch();

                if ($existing) {
                    $db->prepare("UPDATE tuition SET amount=?,due_date=?,paid_amount=?,paid_date=?,status=? WHERE national_id=? AND installment_no=? AND academic_year=?")
                       ->execute([$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$national_id,$installment_no,$active_year]);
                    $updated++;
                } else {
                    $db->prepare("INSERT INTO tuition (national_id,installment_no,amount,due_date,paid_amount,paid_date,status,academic_year) VALUES (?,?,?,?,?,?,?,?)")
                       ->execute([$national_id,$installment_no,$amount,$due_date,$paid_amount,$paid_date ?: null,$status,$active_year]);
                    $inserted++;
                }
            }
            $msgs[] = ['type'=>'success', 'text'=>"✅ اقساط (Excel): وارد شده: $inserted | به‌روز: $updated | رد شده: $skipped"];
        } else {
            $msgs[] = ['type'=>'error', 'text'=>'❌ خطا در خواندن فایل Excel: ' . SimpleXLSX::parseError()];
        }
    } else {
        $msgs[] = ['type'=>'error', 'text'=>'❌ فایل Excel انتخاب نشده.'];
    }
}
// ─── افزودن دستی دانش‌آموز ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_manual'])) {
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');

    if (!$first_name || !$last_name || !$national_id) {
        $msgs[] = ['type' => 'error', 'text' => '❌ نام، نام خانوادگی و کد ملی الزامی هستند.'];
    } else {
        $result = upsertStudent($national_id, $first_name, $last_name, $active_year);
        if ($result === 'created') {
            $msgs[] = ['type' => 'success', 'text' => '✅ دانش‌آموز جدید ایجاد شد.'];
        } else {
            $msgs[] = ['type' => 'success', 'text' => '✅ اطلاعات دانش‌آموز به‌روزرسانی شد.'];
        }
    }
}

// ─── مدیریت کارکنان ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff_manual'])) {
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');

    if (!$first_name || !$last_name || !$national_id) {
        $msgs[] = ['type' => 'error', 'text' => '❌ نام، نام خانوادگی و کد ملی الزامی هستند.'];
    } else {
        $result = upsertStaff($national_id, $first_name, $last_name, $active_year);
        if ($result === 'created') {
            $msgs[] = ['type' => 'success', 'text' => '✅ کارمند جدید ایجاد شد.'];
        } else {
            $msgs[] = ['type' => 'success', 'text' => '✅ اطلاعات کارمند به‌روزرسانی شد.'];
        }
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_staff_profile'])) {
    $db = getDB();
    $old_username = $_POST['old_username'];
    $new_username = trim($_POST['new_username']);
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $new_password = trim($_POST['new_password'] ?? '');

    $fields = [
        'birth_date', 'birth_place', 'education', 'position', 'father_name',
        'home_phone', 'address', 'schedule', 'mobile_phone', 'contract_date',
        'bank', 'sheba', 'letter_no'
    ];
    $data = [];
    foreach ($fields as $f) {
        $data[$f] = $_POST[$f] ?? '';
    }

    try {
        $db->beginTransaction();
        if ($new_password) {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET username=?, password=? WHERE username=?")->execute([$new_username, $hash, $old_username]);
        } else {
            $db->prepare("UPDATE users SET username=? WHERE username=?")->execute([$new_username, $old_username]);
        }

        if ($new_username !== $old_username) {
            $db->prepare("UPDATE staff_profiles SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
        }

        $sql = "UPDATE staff_profiles SET first_name=?, last_name=?, " . implode("=?, ", $fields) . "=? WHERE national_id=? AND academic_year=?";
        $params = array_merge([$first_name, $last_name], array_values($data), [$new_username, $active_year]);
        $db->prepare($sql)->execute($params);

        $db->commit();
        $msgs[] = ['type'=>'success', 'text'=>'✅ پروفایل کارمند بروزرسانی شد.'];
        $_GET['username'] = $new_username;
    } catch (Exception $e) {
        $db->rollBack();
        $msgs[] = ['type'=>'error', 'text'=>'❌ خطا: ' . $e->getMessage()];
    }
}
// ─── افزودن دستی قسط ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tuition_manual'])) {

    $db = getDB();

    $national_id    = trim($_POST['national_id'] ?? '');
    $installment_no = (int)($_POST['installment_no'] ?? 0);
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
                amount,
                due_date,
                paid_amount,
                paid_date,
                status,
                academic_year
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $national_id,
            $installment_no,

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
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
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
            $stmt = $db->prepare("UPDATE users SET username=?, password=? WHERE username=?");
            $stmt->execute([$new_username, $hash, $old_username]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username=? WHERE username=?");
            $stmt->execute([$new_username, $old_username]);
        }

        if ($new_username !== $old_username) {
            $db->prepare("UPDATE tuition SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
            $db->prepare("UPDATE student_profiles SET national_id=? WHERE national_id=?")->execute([$new_username, $old_username]);
        }

        // Update student_profiles table for active year
        $stmt = $db->prepare("UPDATE student_profiles SET
            first_name=?, last_name=?, grade=?, major=?, father_name=?, mother_name=?, mother_phone=?, father_phone=?,
            home_phone=?, student_phone=?, address=?, left_handed=?
            WHERE national_id=? AND academic_year=?");
        $stmt->execute([
            $first_name, $last_name, $grade, $major, $father_name, $mother_name, $mother_phone, $father_phone,
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

    $stmt = $db->prepare("UPDATE tuition SET installment_no=?,  amount=?, due_date=?, paid_amount=?, paid_date=?, status=? WHERE id=?");
    $stmt->execute([$installment_no,  $amount, $due_date, $paid_amount, $paid_date ?: null, $status, $id]);
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


// ─── مدیریت امتحانات ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_exam'])) {
    $db = getDB();
    $id = $_POST['exam_id'] ?? null;
    $title = trim($_POST['title']);
    $date_y = $_POST['date_y'];
    $date_m = $_POST['date_m'];
    $date_d = $_POST['date_d'];
    $date = sprintf("%04d/%02d/%02d", $date_y, $date_m, $date_d);
    $lesson = trim($_POST['lesson']);
    $grade = $_POST['grade'];
    $major = $_POST['major'];
    $max_score = (float)$_POST['max_score'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $teacher_id = $_POST['teacher_id'];

    if ($id) {
        $stmt = $db->prepare("UPDATE exams SET title=?, date=?, lesson=?, grade=?, major=?, teacher_id=?, max_score=?, is_published=? WHERE id=?");
        $stmt->execute([$title, $date, $lesson, $grade, $major, $teacher_id, $max_score, $is_published, $id]);
        $msgs[] = ['type' => 'success', 'text' => '✅ امتحان با موفقیت بروزرسانی شد.'];
    } else {
        $stmt = $db->prepare("INSERT INTO exams (title, date, lesson, grade, major, teacher_id, max_score, is_published, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $date, $lesson, $grade, $major, $teacher_id, $max_score, $is_published, $active_year]);
        $msgs[] = ['type' => 'success', 'text' => '✅ امتحان جدید با موفقیت ثبت شد.'];
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam'])) {
    $db = getDB();
    $id = $_POST['exam_id'];
    $db->prepare("DELETE FROM exams WHERE id=?")->execute([$id]);
    $db->prepare("DELETE FROM scores WHERE exam_id=?")->execute([$id]);
    $msgs[] = ['type' => 'success', 'text' => '✅ امتحان و نمرات آن حذف شدند.'];
}

// ─── آپلود فیش حقوقی ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_paystubs'])) {
    $db = getDB();
    $title = trim($_POST['paystub_title'] ?? '');
    if (!$title) {
        $msgs[] = ['type'=>'error', 'text'=>'❌ عنوان فیش حقوقی الزامی است.'];
    } else {
        if (!is_dir('uploads/paystubs')) {
            mkdir('uploads/paystubs', 0777, true);
        }
        $count = 0;
        foreach ($_FILES['paystub_files']['tmp_name'] as $nid => $tmp_name) {
            if ($tmp_name && is_uploaded_file($tmp_name)) {
                $ext = pathinfo($_FILES['paystub_files']['name'][$nid], PATHINFO_EXTENSION);
                $filename = "paystub_{$nid}_" . time() . ".$ext";
                $target = "uploads/paystubs/" . $filename;
                if (move_uploaded_file($tmp_name, $target)) {
                    $stmt = $db->prepare("INSERT INTO paystubs (national_id, academic_year, title, file_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nid, $active_year, $title, $target]);
                    $count++;
                }
            }
        }
        $msgs[] = ['type'=>'success', 'text'=>"✅ تعداد $count فیش حقوقی با عنوان '{$title}' آپلود شد."];
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_paystub_title'])) {
    $db = getDB();
    $id = (int)$_POST['paystub_id'];
    $title = trim($_POST['new_title']);
    if ($title) {
        $stmt = $db->prepare("UPDATE paystubs SET title=? WHERE id=?");
        $stmt->execute([$title, $id]);
        $msgs[] = ['type'=>'success', 'text'=>'✅ عنوان فیش حقوقی بروزرسانی شد.'];
    }
}

if ($isAdmin && isset($_GET['delete_paystub'])) {
    $db = getDB();
    $id = (int)$_GET['delete_paystub'];
    $stmt = $db->prepare("SELECT file_path FROM paystubs WHERE id=?");
    $stmt->execute([$id]);
    $ps = $stmt->fetch();
    if ($ps) {
        if (file_exists($ps['file_path'])) {
            unlink($ps['file_path']);
        }
        $db->prepare("DELETE FROM paystubs WHERE id=?")->execute([$id]);
        $msgs[] = ['type'=>'success', 'text'=>'✅ فیش حقوقی حذف شد.'];
    }
}

// ─── آپلود کارنامه ───
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_report_cards'])) {
    $db = getDB();
    $title = trim($_POST['report_card_title'] ?? '');
    if (!$title) {
        $msgs[] = ['type'=>'error', 'text'=>'❌ عنوان کارنامه الزامی است.'];
    } else {
        if (!is_dir('uploads/report_cards')) {
            mkdir('uploads/report_cards', 0777, true);
        }
        $count = 0;
        foreach ($_FILES['report_card_files']['tmp_name'] as $nid => $tmp_name) {
            if ($tmp_name && is_uploaded_file($tmp_name)) {
                $ext = pathinfo($_FILES['report_card_files']['name'][$nid], PATHINFO_EXTENSION);
                $filename = "report_{$nid}_" . time() . ".$ext";
                $target = "uploads/report_cards/" . $filename;
                $is_visible = isset($_POST['report_card_visible'][$nid]) ? 1 : 0;
                if (move_uploaded_file($tmp_name, $target)) {
                    $stmt = $db->prepare("INSERT INTO report_cards (national_id, academic_year, title, file_path, is_visible) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nid, $active_year, $title, $target, $is_visible]);
                    $count++;
                }
            }
        }
        $msgs[] = ['type'=>'success', 'text'=>"✅ تعداد $count کارنامه با عنوان '{$title}' آپلود شد."];
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_report_card'])) {
    $db = getDB();
    $id = (int)$_POST['report_card_id'];
    $title = trim($_POST['new_title']);
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    if ($title) {
        $stmt = $db->prepare("UPDATE report_cards SET title=?, is_visible=? WHERE id=?");
        $stmt->execute([$title, $is_visible, $id]);
        $msgs[] = ['type'=>'success', 'text'=>'✅ کارنامه بروزرسانی شد.'];
    }
}

if ($isAdmin && isset($_GET['delete_report_card'])) {
    $db = getDB();
    $id = (int)$_GET['delete_report_card'];
    $stmt = $db->prepare("SELECT file_path FROM report_cards WHERE id=?");
    $stmt->execute([$id]);
    $rc = $stmt->fetch();
    if ($rc) {
        if (file_exists($rc['file_path'])) {
            unlink($rc['file_path']);
        }
        $db->prepare("DELETE FROM report_cards WHERE id=?")->execute([$id]);
        $msgs[] = ['type'=>'success', 'text'=>'✅ کارنامه حذف شد.'];
    }
}

// ─── مدیریت دیتابیس (Backup/Restore/Excel) ───
if ($isAdmin && isset($_GET['download_db'])) {
    if (file_exists(DB_PATH)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="backup_'.date('Y-m-d_H-i').'.sqlite"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize(DB_PATH));
        readfile(DB_PATH);
        exit;
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_db'])) {
    if (!empty($_FILES['db_file']['tmp_name'])) {
        if (move_uploaded_file($_FILES['db_file']['tmp_name'], DB_PATH)) {
            $msgs[] = ['type'=>'success', 'text'=>'✅ دیتابیس با موفقیت بازگردانی شد.'];
        } else {
            $msgs[] = ['type'=>'error', 'text'=>'❌ خطا در جایگزینی فایل دیتابیس.'];
        }
    }
}

if ($isAdmin && isset($_GET['export_excel'])) {
    $db = getDB();
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="tavanesh_export_'.date('Y-m-d').'.xls"');

    echo '<?xml version="1.0"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';

    $tables = [
        'users' => 'کاربران',
        'tuition' => 'اقساط',
        'student_profiles' => 'پروفایل دانش‌آموزان',
        'news' => 'اخبار'
    ];

    foreach ($tables as $tbl => $label) {
        echo '<Worksheet ss:Name="'.$label.'"><Table>';
        $stmt = $db->query("SELECT * FROM $tbl");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            // Header
            echo '<Row>';
            foreach (array_keys($rows[0]) as $col) {
                echo '<Cell><Data ss:Type="String">'.htmlspecialchars($col).'</Data></Cell>';
            }
            echo '</Row>';
            // Data
            foreach ($rows as $row) {
                echo '<Row>';
                foreach ($row as $val) {
                    $type = is_numeric($val) ? 'Number' : 'String';
                    echo '<Cell><Data ss:Type="'.$type.'">'.htmlspecialchars($val).'</Data></Cell>';
                }
                echo '</Row>';
            }
        }
        echo '</Table></Worksheet>';
    }
    echo '</Workbook>';
    exit;
}

// ─── لیست دانش‌آموزان ───
$students = [];
if ($isAdmin) {
    $db = getDB();
    $stmt = $db->prepare("SELECT u.id, u.username, sp.first_name, sp.last_name, u.created_at
                          FROM users u
                          JOIN student_profiles sp ON u.username = sp.national_id
                          WHERE sp.academic_year = ? AND u.role = 'student'
                          ORDER BY sp.last_name, sp.first_name ASC");
    $stmt->execute([$active_year]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─── لیست کارکنان ───
$staff = [];
if ($isAdmin) {
    $db = getDB();
    $stmt = $db->prepare("SELECT u.id, u.username, st.first_name, st.last_name, st.position, u.created_at
                          FROM users u
                          JOIN staff_profiles st ON u.username = st.national_id
                          WHERE st.academic_year = ? AND u.role = 'staff'
                          ORDER BY st.last_name, st.first_name ASC");
    $stmt->execute([$active_year]);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tab = $_GET['tab'] ?? 'db_mgmt';
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

.field-title { width: 80% !important; }
.field-lesson { width: 70% !important; }
.field-max-score { width: 33% !important; }

@media (max-width: 600px) {
    .exam-form-grid {
        grid-template-columns: 1fr !important;
    }
    .field-title, .field-lesson, .field-max-score {
        width: 100% !important;
    }
}
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
    <div style="display:flex; align-items:center; gap:20px;">
        <div id="live-clock" style="display:flex; align-items:center; gap:20px; color: #fff; font-size: 1.05rem; background: rgba(0,0,0,0.15); padding: 8px 18px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.25); box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="opacity: 0.8; font-size: 0.85rem;">📅</span>
                <span>امروز: <strong><?= to_persian_num(get_jalali_today()) ?></strong></span>
            </div>
            <div style="width: 1px; height: 20px; background: rgba(255,255,255,0.2);"></div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="opacity: 0.8; font-size: 0.85rem;">🕒</span>
                <span id="clock-time" style="font-weight: 800; letter-spacing: 1.5px; font-variant-numeric: tabular-nums;">۰۰:۰۰:۰۰</span>
            </div>
        </div>
        <form method="POST" style="display:flex; align-items:center; gap:5px; background:rgba(255,255,255,0.1); padding:5px 10px; border-radius:10px;">
            <label style="color:#fff; margin-bottom:0; font-size:0.75rem;">سال تحصیلی:</label>
            <select name="active_year" onchange="this.form.submit()" style="background:transparent; border:none; color:#fff; font-family:Vazirmatn; font-size:0.85rem; outline:none; cursor:pointer;">
                <?php foreach ($academic_years as $y): ?>
                    <option value="<?= $y ?>" <?= $y===$active_year?'selected':'' ?> style="color:#000;"><?= to_persian_num($y) ?></option>
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
    <a href="?tab=db_mgmt"   class="tab <?= $tab==='db_mgmt'   ?'active':'' ?>">🗄️ مدیریت دیتابیس</a>
    <a href="?tab=upload_paystubs"  class="tab <?= $tab==='upload_paystubs'  ?'active':'' ?>">💵 مدیریت فیش‌های حقوقی</a>
    <a href="?tab=report_cards"  class="tab <?= $tab==='report_cards'  ?'active':'' ?>">📜 مدیریت کارنامه‌ها</a>
    <a href="?tab=students"  class="tab <?= $tab==='students'  ?'active':'' ?>">👩‍🎓 لیست دانش‌آموزان (<?= to_persian_num(count($students)) ?>)</a>
    <a href="?tab=staff"     class="tab <?= $tab==='staff'     ?'active':'' ?>">👥 مدیریت کارکنان (<?= to_persian_num(count($staff)) ?>)</a>
    <a href="?tab=exams"     class="tab <?= $tab==='exams'     ?'active':'' ?>">📝 مدیریت نمرات و امتحانات</a>
    <a href="?tab=debtors"   class="tab <?= $tab==='debtors'   ?'active':'' ?>">📉 لیست بدهکاران</a>
    <a href="?tab=news"      class="tab <?= $tab==='news'      ?'active':'' ?>">📰 مدیریت اخبار</a>
  </div>

  <?php if ($tab === 'db_mgmt'): ?>

<!-- خروجی و پشتیبان‌گیری -->
<div class="card">
  <h3>💾 خروجی و پشتیبان‌گیری</h3>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a href="?download_db=1" class="btn-primary" style="width:auto; text-decoration:none; display:inline-block; padding:12px 20px;">📥 دانلود فایل دیتابیس (بکاپ)</a>
    <a href="?export_excel=1" class="btn-primary" style="width:auto; text-decoration:none; display:inline-block; padding:12px 20px; background:linear-gradient(135deg, #1a9960, #0d6e43); box-shadow:0 6px 20px rgba(26,153,96,0.3);">📊 خروجی اکسل حرفه‌ای</a>
  </div>
</div>

<!-- بازگردانی دیتابیس -->
<div class="card">
  <h3>🔄 بازگردانی فایل دیتابیس (Restore)</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="field">
      <label>فایل دیتابیس (.sqlite)</label>
      <input type="file" name="db_file" accept=".sqlite">
    </div>
    <button type="submit" name="restore_db" class="btn-primary" style="background:var(--red); box-shadow:0 6px 20px rgba(201,64,64,0.3);" onclick="return confirm('⚠️ با این کار تمام اطلاعات فعلی حذف و فایل جدید جایگزین می‌شود. آیا مطمئن هستید؟')">⚠️ جایگزینی و بازگردانی</button>
  </form>
</div>

<!-- افزودن دستی دانش‌آموز -->
<div class="card">
  <h3>➕ افزودن دستی دانش‌آموز</h3>

  <form method="POST">
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
        <div class="field">
          <label>نام</label>
          <input type="text" name="first_name" placeholder="مثلاً فاطمه">
        </div>
        <div class="field">
          <label>نام خانوادگی</label>
          <input type="text" name="last_name" placeholder="مثلاً محمدی">
        </div>
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
      ستون اول: نام — ستون دوم: نام خانوادگی — ستون سوم: کد ملی<br>
      <code>فاطمه,محمدی,1234567890</code><br><br>
      ⚡ نام کاربری و رمز عبور اولیه هر دانش‌آموز = کد ملی او<br>
      🔄 اگر کد ملی قبلاً وجود داشته باشد، اطلاعات به‌روز می‌شود.
    </div>
  </div>

  <!-- ایمپورت دانش‌آموزان اکسل -->
  <div class="card">
    <h3>👩‍🎓 وارد کردن دانش‌آموزان از فایل Excel</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل Excel دانش‌آموزان (.xlsx)</label>
        <input type="file" name="students_excel" accept=".xlsx">
      </div>
      <button type="submit" name="import_students_excel" class="btn-primary">آپلود و وارد کردن اکسل</button>
    </form>
    <div class="guide">
      <strong>ترتیب ستون‌های فایل Excel دانش‌آموزان:</strong><br>
      <small>۱. نام | ۲. نام خانوادگی | ۳. کد ملی | ۴. پایه | ۵. رشته | ۶. نام پدر | ۷. نام مادر | ۸. تلفن مادر | ۹. تلفن پدر | ۱۰. تلفن منزل | ۱۱. چپ‌دست (۱ یا بله) | ۱۲. شماره صندلی | ۱۳. آدرس | ۱۴. تلفن دانش‌آموز</small>
    </div>
  </div>

  <!-- ایمپورت کارکنان اکسل -->
  <div class="card">
    <h3>👥 وارد کردن کارکنان از فایل Excel</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل Excel کارکنان (.xlsx)</label>
        <input type="file" name="staff_excel" accept=".xlsx">
      </div>
      <button type="submit" name="import_staff_excel" class="btn-primary">آپلود و وارد کردن اکسل</button>
    </form>
    <div class="guide">
      <strong>ترتیب ستون‌های فایل Excel کارکنان:</strong><br>
      <small>۱. نام | ۲. نام خانوادگی | ۳. کد ملی | ۴. تاریخ تولد | ۵. محل صدور | ۶. مدرک تحصیلی | ۷. سمت | ۸. نام پدر | ۹. تلفن منزل | ۱۰. آدرس | ۱۱. برنامه حضور | ۱۲. تلفن همراه | ۱۳. تاریخ قرارداد | ۱۴. بانک | ۱۵. شبا | ۱۶. شماره نامه</small>
    </div>
  </div>

  <!-- ایمپورت پرداخت‌های خودکار اکسل -->
  <div class="card">
    <h3>💰 ثبت خودکار پرداختی با فایل Excel</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل Excel پرداخت‌ها (.xlsx)</label>
        <input type="file" name="payments_excel" accept=".xlsx">
      </div>
      <button type="submit" name="import_payments_excel" class="btn-primary">آپلود و ثبت پرداخت‌ها</button>
    </form>
    <div class="guide">
      <strong>ترتیب ستون‌های فایل Excel پرداخت‌ها:</strong><br>
      <small>۱. کد ملی | ۲. مبلغ (تومان) | ۳. تاریخ پرداخت (مثلاً ۱۴۰۳/۰۷/۰۱)</small><br>
      ⚡ مبالغ به ترتیب بین اقساط پرداخت نشده دانش‌آموز توزیع می‌شود.
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
          <option value="<?= htmlspecialchars($s['username']) ?>"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?> (<?= htmlspecialchars($s['username']) ?>)</option>
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
      <code>1234567890,1,5000000,1403/07/01,5000000,1403/06/28,paid</code><br><br>
      <strong>مقادیر وضعیت:</strong>
      <code>paid</code> پرداخت شده &nbsp;|&nbsp;
      <code>partial</code> ناقص &nbsp;|&nbsp;
      <code>unpaid</code> پرداخت نشده<br>
      🔄 اگر کد ملی + شماره قسط قبلاً موجود بود، به‌روز می‌شود.
    </div>
  </div>

  <!-- ایمپورت اقساط اکسل -->
  <div class="card">
    <h3>💳 وارد کردن اقساط شهریه از فایل Excel</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="field">
        <label>فایل Excel اقساط (.xlsx)</label>
        <input type="file" name="tuition_excel" accept=".xlsx">
      </div>
      <button type="submit" name="import_tuition_excel" class="btn-primary">آپلود و وارد کردن اکسل</button>
    </form>
    <div class="guide">
      <strong>ترتیب ستون‌های فایل Excel اقساط (۷ ستون):</strong><br>
      <small>۱. کد ملی | ۲. شماره قسط | ۳. مبلغ | ۴. تاریخ سررسید | ۵. مبلغ پرداخت شده | ۶. تاریخ پرداخت | ۷. وضعیت (paid, partial, unpaid)</small>
    </div>
  </div>

  <?php elseif ($tab === 'students'): ?>

  <!-- لیست دانش‌آموزان -->
  <div class="card">
    <h3>👩‍🎓 لیست دانش‌آموزان (<?= to_persian_num(count($students)) ?> نفر)</h3>

    <div class="field" style="margin-bottom: 20px;">
        <input type="text" id="studentSearch" placeholder="🔍 جستجوی نام یا کد ملی..." onkeyup="filterStudents()" style="padding: 12px 15px; border-radius: 12px; border: 1.5px solid var(--turquoise-light); width: 100%; font-family: Vazirmatn;">
    </div>

    <div class="table-wrap">
      <table id="studentTable">
        <thead>
          <tr>
            <th onclick="sortTable(0)" style="cursor:pointer;"># ↕</th>
            <th onclick="sortTable(1)" style="cursor:pointer;">نام ↕</th>
            <th onclick="sortTable(2)" style="cursor:pointer;">نام خانوادگی ↕</th>
            <th onclick="sortTable(3)" style="cursor:pointer;">کد ملی (نام کاربری) ↕</th>
            <th onclick="sortTable(4)" style="cursor:pointer;">تاریخ ثبت ↕</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--gray)">هنوز دانش‌آموزی ثبت نشده</td></tr>
          <?php endif; ?>
          <?php foreach ($students as $i => $s): ?>
          <tr>
            <td><?= to_persian_num($i + 1) ?></td>
            <td><?= htmlspecialchars($s['first_name'] ?: '—') ?></td>
            <td><?= htmlspecialchars($s['last_name'] ?: '—') ?></td>
            <td><?= to_persian_num(htmlspecialchars($s['username'])) ?></td>
            <td><?= to_persian_num(convert_to_jalali($s['created_at'])) ?></td>
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
      $fullName = trim(($prof['first_name']??'').' '.($prof['last_name']??'')) ?: $student_info['username'];

      $t_stmt = $db->prepare("SELECT * FROM tuition WHERE national_id = ? AND academic_year = ? ORDER BY installment_no ASC");
      $t_stmt->execute([$target_user, $active_year]);
      $student_tuition = $t_stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>⚙️ مدیریت پروفایل: <?= htmlspecialchars($fullName) ?> (سال <?= to_persian_num($active_year) ?>)</h3>
    <form method="POST">
      <input type="hidden" name="old_username" value="<?= htmlspecialchars($student_info['username']) ?>">

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
          <div class="field">
            <label>نام</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($prof['first_name']??'') ?>">
          </div>
          <div class="field">
            <label>نام خانوادگی</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($prof['last_name']??'') ?>">
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
                <?php foreach(['1404','1405','1406'] as $y) echo "<option value='$y' ".($ty==$y?'selected':'').">$y</option>"; ?>
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
                        <?php foreach(['1404','1405','1406'] as $y) echo "<option value='$y' ".($d_y==$y?'selected':'').">$y</option>"; ?>
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
                        <?php foreach(['1404','1405','1406'] as $y) echo "<option value='$y' ".($p_y==$y?'selected':'').">$y</option>"; ?>
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


  <?php elseif ($tab === 'report_cards'): ?>
  <?php
    $f_grade = $_GET['grade'] ?? '';
    $f_major = $_GET['major'] ?? '';
    $db = getDB();
    $sql = "SELECT u.username, sp.first_name, sp.last_name, sp.grade, sp.major 
            FROM users u 
            JOIN student_profiles sp ON u.username = sp.national_id 
            WHERE sp.academic_year = ? AND u.role = 'student'";
    $params = [$active_year];
    if ($f_grade) { $sql .= " AND sp.grade = ?"; $params[] = $f_grade; }
    if ($f_major) { $sql .= " AND sp.major = ?"; $params[] = $f_major; }
    $sql .= " ORDER BY sp.last_name, sp.first_name ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $filtered_students = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>📜 مدیریت و آپلود کارنامه‌ها (سال <?= to_persian_num($active_year) ?>)</h3>
    
    <div style="background:var(--turquoise-lighter); padding:15px; border-radius:12px; margin-bottom:20px; display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
        <span>فیلتر لیست:</span>
        <select onchange="location.href='?tab=report_cards&grade='+this.value+'&major=<?= urlencode($f_major) ?>'" style="padding:5px 10px; border-radius:8px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
            <option value="">همه پایه‌ها</option>
            <?php foreach(['دهم','یازدهم','دوازدهم'] as $g) echo "<option value='$g' ".($f_grade==$g?'selected':'').">$g</option>"; ?>
        </select>
        <select onchange="location.href='?tab=report_cards&grade=<?= urlencode($f_grade) ?>&major='+this.value" style="padding:5px 10px; border-radius:8px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
            <option value="">همه رشته‌ها</option>
            <?php foreach(['ریاضی','تجربی','انسانی'] as $m) echo "<option value='$m' ".($f_major==$m?'selected':'').">$m</option>"; ?>
        </select>
        <a href="?tab=report_cards" style="font-size:0.8rem; color:var(--red);">حذف فیلترها</a>
    </div>

    <form method="POST" action="?tab=report_cards" enctype="multipart/form-data">
      <div class="field">
        <label>عنوان کارنامه (مثلاً: کارنامه نوبت اول ۱۴۰۴)</label>
        <input type="text" name="report_card_title" placeholder="عنوان کارنامه را وارد کنید..." required>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>نام و نام خانوادگی</th>
              <th>کد ملی</th>
              <th>پایه و رشته</th>
              <th>انتخاب فایل</th>
              <th>قابل مشاهده</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filtered_students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
              <td><?= to_persian_num(htmlspecialchars($s['username'])) ?></td>
              <td><?= htmlspecialchars($s['grade'] . ' - ' . $s['major']) ?></td>
              <td><input type="file" name="report_card_files[<?= htmlspecialchars($s['username']) ?>]" accept=".pdf,.jpg,.jpeg,.png"></td>
              <td style="text-align:center;"><input type="checkbox" name="report_card_visible[<?= htmlspecialchars($s['username']) ?>]" checked></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="upload_report_cards" class="btn-primary" style="margin-top:20px;">📤 شروع آپلود کارنامه‌ها</button>
    </form>
  </div>

  <?php
    $stmt = $db->prepare("SELECT rc.*, sp.first_name, sp.last_name 
                          FROM report_cards rc
                          JOIN student_profiles sp ON rc.national_id = sp.national_id AND rc.academic_year = sp.academic_year
                          WHERE rc.academic_year = ?
                          ORDER BY rc.id DESC");
    $stmt->execute([$active_year]);
    $all_report_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>📋 مدیریت کارنامه‌های صادر شده (سال <?= to_persian_num($active_year) ?>)</h3>
    <div class="table-wrap">
      <table id="reportCardTable">
        <thead>
          <tr>
            <th>عنوان کارنامه</th>
            <th>نام دانش‌آموز</th>
            <th>کد ملی</th>
            <th>وضعیت نمایش</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_report_cards)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--gray)">هنوز کارنامه‌ای در این سال تحصیلی ثبت نشده است.</td></tr>
          <?php endif; ?>
          <?php foreach ($all_report_cards as $rc): ?>
          <tr>
            <td>
                <form method="POST" action="?tab=report_cards" style="display:flex; gap:5px; align-items:center;">
                    <input type="hidden" name="report_card_id" value="<?= $rc['id'] ?>">
                    <input type="text" name="new_title" value="<?= htmlspecialchars($rc['title']) ?>" style="padding:5px; font-size:0.85rem; flex:1;">
                    <label style="font-size:0.7rem; margin-bottom:0;"><input type="checkbox" name="is_visible" <?= $rc['is_visible']?'checked':'' ?>> نمایش</label>
                    <button type="submit" name="update_report_card" class="btn-sm" style="background:var(--green); padding:5px 10px;">💾</button>
                </form>
            </td>
            <td><?= htmlspecialchars($rc['first_name'] . ' ' . $rc['last_name']) ?></td>
            <td><?= to_persian_num(htmlspecialchars($rc['national_id'])) ?></td>
            <td><?= $rc['is_visible'] ? '<span style="color:var(--green)">✅ قابل مشاهده</span>' : '<span style="color:var(--red)">❌ غیرفعال</span>' ?></td>
            <td style="white-space: nowrap;">
              <a href="<?= htmlspecialchars($rc['file_path']) ?>" target="_blank" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">👁️</a>
              <a href="?tab=report_cards&delete_report_card=<?= $rc['id'] ?>" class="btn-del" onclick="return confirm('حذف کارنامه؟')">🗑️</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php elseif ($tab === 'upload_paystubs'): ?>
  <div class="card">
    <h3>💵 مدیریت و آپلود فیش‌های حقوقی (سال <?= to_persian_num($active_year) ?>)</h3>
    <form method="POST" action="?tab=upload_paystubs" enctype="multipart/form-data">
      <div class="field">
        <label>عنوان (مثلاً: فیش حقوقی اردیبهشت ۱۴۰۴)</label>
        <input type="text" name="paystub_title" placeholder="عنوان فیش را وارد کنید..." required>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>نام و نام خانوادگی</th>
              <th>کد ملی</th>
              <th>انتخاب فایل</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($staff as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
              <td><?= to_persian_num(htmlspecialchars($s['username'])) ?></td>
              <td><input type="file" name="paystub_files[<?= htmlspecialchars($s['username']) ?>]" accept=".pdf,.jpg,.jpeg,.png"></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="submit" name="upload_paystubs" class="btn-primary" style="margin-top:20px;">📤 شروع آپلود فیش‌ها</button>
    </form>
  </div>

  <?php
    $db = getDB();
    $stmt = $db->prepare("SELECT ps.*, st.first_name, st.last_name
                          FROM paystubs ps
                          JOIN staff_profiles st ON ps.national_id = st.national_id AND ps.academic_year = st.academic_year
                          WHERE ps.academic_year = ?
                          ORDER BY ps.id DESC");
    $stmt->execute([$active_year]);
    $all_paystubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <div class="card">
    <h3>📋 مدیریت فیش‌های صادر شده (سال <?= to_persian_num($active_year) ?>)</h3>

    <div class="field" style="margin-bottom: 20px;">
        <input type="text" id="paystubSearch" placeholder="🔍 جستجوی نام پرسنل یا عنوان فیش..." onkeyup="filterPaystubs()" style="padding: 12px 15px; border-radius: 12px; border: 1.5px solid var(--turquoise-light); width: 100%; font-family: Vazirmatn;">
    </div>

    <div class="table-wrap">
      <table id="paystubTable">
        <thead>
          <tr>
            <th>عنوان فیش</th>
            <th>نام پرسنل</th>
            <th>کد ملی</th>
            <th>تاریخ آپلود</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_paystubs)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--gray)">هنوز فیش حقوقی در این سال تحصیلی ثبت نشده است.</td></tr>
          <?php endif; ?>
          <?php foreach ($all_paystubs as $ps): ?>
          <tr>
            <td>
                <form method="POST" action="?tab=upload_paystubs" style="display:flex; gap:5px;">
                    <input type="hidden" name="paystub_id" value="<?= $ps['id'] ?>">
                    <input type="text" name="new_title" value="<?= htmlspecialchars($ps['title']) ?>" style="padding:5px; font-size:0.85rem; flex:1;">
                    <button type="submit" name="update_paystub_title" class="btn-sm" style="background:var(--green); padding:5px 10px;">💾</button>
                </form>
            </td>
            <td><?= htmlspecialchars($ps['first_name'] . ' ' . $ps['last_name']) ?></td>
            <td><?= to_persian_num(htmlspecialchars($ps['national_id'])) ?></td>
            <td><?= to_persian_num(convert_to_jalali($ps['upload_date'])) ?></td>
            <td style="white-space: nowrap;">
              <a href="<?= htmlspecialchars($ps['file_path']) ?>" target="_blank" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">👁️ مشاهده</a>
              <a href="?tab=upload_paystubs&delete_paystub=<?= $ps['id'] ?>"
                 class="btn-del"
                 onclick="return confirm('آیا از حذف این فیش حقوقی اطمینان دارید؟ (فایل نیز از سرور حذف خواهد شد)')">حذف</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  function filterPaystubs() {
      var input, filter, table, tr, td, i, txtValue;
      input = document.getElementById("paystubSearch");
      filter = input.value.toUpperCase();
      table = document.getElementById("paystubTable");
      tr = table.getElementsByTagName("tr");
      for (i = 1; i < tr.length; i++) {
          tr[i].style.display = "none";
          var tds = tr[i].getElementsByTagName("td");
          // Check title column (index 0) and staff name column (index 1)
          for (var j = 0; j <= 1; j++) {
              if (tds[j]) {
                  // For title column, we need to check the input value
                  if (j === 0) {
                      txtValue = tds[j].querySelector('input[name="new_title"]').value;
                  } else {
                      txtValue = tds[j].textContent || tds[j].innerText;
                  }
                  if (txtValue.toUpperCase().indexOf(filter) > -1) {
                      tr[i].style.display = "";
                      break;
                  }
              }
          }
      }
  }
  </script>

  <?php elseif ($tab === 'staff'): ?>

  <div class="card">
    <h3>👥 مدیریت کارکنان</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>نام و نام خانوادگی</th>
            <th>کد ملی</th>
            <th>سمت</th>
            <th>تاریخ ثبت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($staff)): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--gray)">هنوز کارمندی ثبت نشده</td></tr>
          <?php endif; ?>
          <?php foreach ($staff as $i => $s): ?>
          <tr>
            <td><?= to_persian_num($i + 1) ?></td>
            <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
            <td><?= to_persian_num(htmlspecialchars($s['username'])) ?></td>
            <td><?= htmlspecialchars($s['position'] ?: '—') ?></td>
            <td><?= to_persian_num(convert_to_jalali($s['created_at'])) ?></td>
            <td>
              <a href="?tab=manage_staff&username=<?= urlencode($s['username']) ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">ویرایش</a>
              <a href="?tab=staff&delete_user=<?= $s['id'] ?>" class="btn-del" onclick="return confirm('حذف این کارمند؟')">حذف</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <h3>➕ افزودن دستی کارمند</h3>
    <form method="POST">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
        <div class="field"><label>نام</label><input type="text" name="first_name"></div>
        <div class="field"><label>نام خانوادگی</label><input type="text" name="last_name"></div>
      </div>
      <div class="field"><label>کد ملی</label><input type="text" name="national_id"></div>
      <button type="submit" name="add_staff_manual" class="btn-primary">ثبت کارمند</button>
    </form>
  </div>

  <?php elseif ($tab === 'manage_staff' && isset($_GET['username'])):
    $db = getDB();
    $target_user = $_GET['username'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$target_user]);
    $u_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u_info):
      echo "<div class='alert error'>❌ کارمند یافت نشد.</div>";
    else:
      $stmt = $db->prepare("SELECT * FROM staff_profiles WHERE national_id = ? AND academic_year = ?");
      $stmt->execute([$target_user, $active_year]);
      $st_prof = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  ?>
  <div class="card">
    <h3>⚙️ مدیریت پروفایل کارمند: <?= htmlspecialchars(($st_prof['first_name']??'') . ' ' . ($st_prof['last_name']??'')) ?></h3>
    <form method="POST">
      <input type="hidden" name="old_username" value="<?= htmlspecialchars($u_info['username']) ?>">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
          <div class="field"><label>نام</label><input type="text" name="first_name" value="<?= htmlspecialchars($st_prof['first_name']??'') ?>"></div>
          <div class="field"><label>نام خانوادگی</label><input type="text" name="last_name" value="<?= htmlspecialchars($st_prof['last_name']??'') ?>"></div>
          <div class="field"><label>کد ملی (نام کاربری)</label><input type="text" name="new_username" value="<?= htmlspecialchars($u_info['username']) ?>"></div>
          <div class="field"><label>رمز عبور جدید</label><input type="password" name="new_password" placeholder="********"></div>
          <div class="field"><label>تاریخ تولد</label><input type="text" name="birth_date" value="<?= htmlspecialchars($st_prof['birth_date']??'') ?>"></div>
          <div class="field"><label>محل صدور</label><input type="text" name="birth_place" value="<?= htmlspecialchars($st_prof['birth_place']??'') ?>"></div>
          <div class="field"><label>مدرک تحصیلی</label><input type="text" name="education" value="<?= htmlspecialchars($st_prof['education']??'') ?>"></div>
          <div class="field"><label>سمت (دبیر، مستخدم و ...)</label><input type="text" name="position" value="<?= htmlspecialchars($st_prof['position']??'') ?>"></div>
          <div class="field"><label>نام پدر</label><input type="text" name="father_name" value="<?= htmlspecialchars($st_prof['father_name']??'') ?>"></div>
          <div class="field"><label>تلفن منزل</label><input type="text" name="home_phone" value="<?= htmlspecialchars($st_prof['home_phone']??'') ?>"></div>
          <div class="field"><label>تلفن همراه</label><input type="text" name="mobile_phone" value="<?= htmlspecialchars($st_prof['mobile_phone']??'') ?>"></div>
          <div class="field"><label>تاریخ قرارداد</label><input type="text" name="contract_date" value="<?= htmlspecialchars($st_prof['contract_date']??'') ?>"></div>
          <div class="field"><label>بانک</label><input type="text" name="bank" value="<?= htmlspecialchars($st_prof['bank']??'') ?>"></div>
          <div class="field"><label>شماره شبا</label><input type="text" name="sheba" value="<?= htmlspecialchars($st_prof['sheba']??'') ?>"></div>
          <div class="field"><label>شماره نامه</label><input type="text" name="letter_no" value="<?= htmlspecialchars($st_prof['letter_no']??'') ?>"></div>
      </div>
      <div class="field"><label>آدرس</label><textarea name="address" rows="2" style="width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:10px; font-family:Vazirmatn;"><?= htmlspecialchars($st_prof['address']??'') ?></textarea></div>
      <div class="field"><label>برنامه حضور</label><textarea name="schedule" rows="2" style="width:100%; border:1.5px solid #c0e5ea; border-radius:12px; padding:10px; font-family:Vazirmatn;"><?= htmlspecialchars($st_prof['schedule']??'') ?></textarea></div>
      <button type="submit" name="update_staff_profile" class="btn-primary">بروزرسانی پروفایل کارمند</button>
    </form>
  </div>
  <?php endif; ?>

  <?php elseif ($tab === 'debtors'):
    if (isset($_POST['ref_y'], $_POST['ref_m'], $_POST['ref_d'])) {
        $ref_date = sprintf("%04d/%02d/%02d", $_POST['ref_y'], $_POST['ref_m'], $_POST['ref_d']);
    } else {
        $ref_date = get_jalali_today();
    }
    $db = getDB();

    $stmt = $db->prepare("SELECT u.username, sp.first_name, sp.last_name FROM users u JOIN student_profiles sp ON u.username = sp.national_id WHERE sp.academic_year = ? AND u.role = 'student'");
    $stmt->execute([$active_year]);
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                'full_name' => trim($u['first_name'].' '.$u['last_name']),
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
        <?php
            list($ry, $rm, $rd) = explode('/', $ref_date);
        ?>
        <div style="display:flex; gap:5px; direction:rtl;">
            <select name="ref_d" id="ref_d" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$rd==$i?'selected':'').">$i</option>"; ?>
            </select>
            <select name="ref_m" id="ref_m" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$rm==$i?'selected':'').">$i</option>"; ?>
            </select>
            <select name="ref_y" id="ref_y" style="flex:1; padding:10px; border-radius:12px; border:1.5px solid #c0e5ea; font-family:Vazirmatn; font-size:1rem;">
                <?php foreach(['1404','1405','1406'] as $y) echo "<option value='$y' ".($ry==$y?'selected':'').">$y</option>"; ?>
            </select>
        </div>
      </div>
      <button type="submit" class="btn-primary" style="width:auto; padding:11px 24px;">بروزرسانی گزارش</button>
      <button type="button" onclick="setRefToday()" class="btn-sm" style="background:var(--gray); height: 45px; border-radius: 12px;">تاریخ امروز</button>
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
            <td><?= htmlspecialchars($d['full_name'] ?: $d['username']) ?></td>
            <td><?= to_persian_num(htmlspecialchars($d['username'])) ?></td>
            <td><?= to_persian_num(number_format($d['total_due'])) ?> تومان</td>
            <td><?= to_persian_num(number_format($d['total_paid'])) ?> تومان</td>
            <td style="color:var(--red); font-weight:bold;"><?= to_persian_num(number_format($d['debt'])) ?> تومان</td>
            <td>
              <a href="?tab=manage_student&username=<?= urlencode($d['username']) ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">مدیریت</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php elseif ($tab === 'exams'):
    $db = getDB();
    // Fetch Exams
    $stmt = $db->prepare("SELECT e.*, st.first_name, st.last_name FROM exams e LEFT JOIN staff_profiles st ON e.teacher_id = st.national_id AND e.academic_year = st.academic_year WHERE e.academic_year = ? ORDER BY e.date DESC");
    $stmt->execute([$active_year]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Teachers
    $stmt = $db->prepare("SELECT national_id, first_name, last_name FROM staff_profiles WHERE academic_year = ? AND position LIKE '%دبیر%'");
    $stmt->execute([$active_year]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $edit_exam = null;
    if (isset($_GET['edit_exam_id'])) {
        $stmt = $db->prepare("SELECT * FROM exams WHERE id=?");
        $stmt->execute([$_GET['edit_exam_id']]);
        $edit_exam = $stmt->fetch(PDO::FETCH_ASSOC);
    }
  ?>
  <div class="card">
    <h3><?= $edit_exam ? '✏️ ویرایش امتحان' : '➕ ایجاد امتحان جدید' ?></h3>
    <form method="POST">
        <?php if ($edit_exam): ?>
            <input type="hidden" name="exam_id" value="<?= $edit_exam['id'] ?>">
        <?php endif; ?>
        <div class="exam-form-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
            <div class="field"><label>عنوان امتحان</label><input type="text" name="title" class="field-title" value="<?= htmlspecialchars($edit_exam['title'] ?? '') ?>" required placeholder="مثلاً: میان‌ترم ریاضی"></div>
            <div class="field">
                <label>تاریخ امتحان</label>
                <?php
                    $d_y = $d_m = $d_d = "";
                    if (!empty($edit_exam['date'])) {
                        list($d_y, $d_m, $d_d) = explode('/', $edit_exam['date']);
                    } else {
                        list($d_y, $d_m, $d_d) = explode('/', get_jalali_today());
                    }
                ?>
                <div style="display:flex; gap:5px; direction:rtl;">
                    <select name="date_d" style="flex:1; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;"><?php for($i=1; $i<=31; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_d==$i?'selected':'').">$i</option>"; ?></select>
                    <select name="date_m" style="flex:1; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;"><?php for($i=1; $i<=12; $i++) echo "<option value='".sprintf("%02d",$i)."' ".((int)$d_m==$i?'selected':'').">$i</option>"; ?></select>
                    <select name="date_y" style="flex:1; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;"><?php foreach(['1404','1405','1406'] as $y) echo "<option value='$y' ".($d_y==$y?'selected':'').">$y</option>"; ?></select>
                </div>
            </div>
            <div class="field"><label>درس</label><input type="text" name="lesson" class="field-lesson" value="<?= htmlspecialchars($edit_exam['lesson'] ?? '') ?>" required></div>
            <div class="field">
                <label>پایه</label>
                <select name="grade" required style="width:100%; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
                    <?php foreach(['دهم','یازدهم','دوازدهم'] as $g) echo "<option value='$g' ".((($edit_exam['grade']??'')==$g)?'selected':'').">$g</option>"; ?>
                </select>
            </div>
            <div class="field">
                <label>رشته</label>
                <select name="major" required style="width:100%; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
                    <?php foreach(['ریاضی','تجربی','انسانی'] as $m) echo "<option value='$m' ".((($edit_exam['major']??'')==$m)?'selected':'').">$m</option>"; ?>
                </select>
            </div>
            <div class="field"><label>نمره از چند</label><input type="number" step="0.25" name="max_score" class="field-max-score" value="<?= htmlspecialchars($edit_exam['max_score'] ?? '20') ?>" required></div>
            <div class="field">
                <label>دبیر</label>
                <select name="teacher_id" required style="width:100%; padding:8px; border-radius:10px; border:1.5px solid #c0e5ea; font-family:Vazirmatn;">
                    <?php foreach($teachers as $t) echo "<option value='{$t['national_id']}' ".((($edit_exam['teacher_id']??'')==$t['national_id'])?'selected':'').">{$t['first_name']} {$t['last_name']}</option>"; ?>
                </select>
            </div>
            <div class="field" style="display:flex; align-items:center; gap:10px; margin-top:25px;">
                <input type="checkbox" name="is_published" id="pub" <?= ($edit_exam ? ($edit_exam['is_published'] ? 'checked' : '') : 'checked') ?>>
                <label for="pub" style="margin-bottom:0;">انتشار برای دانش‌آموزان</label>
            </div>
        </div>
        <div style="margin-top:15px;">
            <button type="submit" name="save_exam" class="btn-primary" style="width:auto; padding:10px 25px;"><?= $edit_exam ? 'بروزرسانی امتحان' : 'ثبت امتحان' ?></button>
            <?php if ($edit_exam): ?><a href="?tab=exams" class="btn-sm" style="background:var(--gray); text-decoration:none; padding:10px 20px; border-radius:10px;">انصراف</a><?php endif; ?>
        </div>
    </form>
  </div>

  <div class="card">
    <h3>📋 لیست امتحانات (سال <?= to_persian_num($active_year) ?>)</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>عنوان</th>
            <th>درس</th>
            <th>پایه و رشته</th>
            <th>دبیر</th>
            <th>تاریخ</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($exams)): ?>
            <tr><td colspan="7" style="text-align:center; padding:20px;">هیچ امتحانی ثبت نشده است.</td></tr>
          <?php endif; ?>
          <?php foreach ($exams as $e): ?>
          <tr>
            <td><?= htmlspecialchars($e['title']) ?></td>
            <td><?= htmlspecialchars($e['lesson']) ?></td>
            <td><?= htmlspecialchars($e['grade'] . ' - ' . $e['major']) ?></td>
            <td><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></td>
            <td><?= to_persian_num($e['date']) ?></td>
            <td><?= $e['is_published'] ? '<span style="color:var(--green)">منتشر شده</span>' : '<span style="color:var(--gray)">منتشر نشده</span>' ?></td>
            <td>
              <a href="manage_scores.php?exam_id=<?= $e['id'] ?>" class="btn-sm" style="background:var(--green); text-decoration:none;">📝 نمرات</a>
              <a href="?tab=exams&edit_exam_id=<?= $e['id'] ?>" class="btn-sm" style="background:var(--turquoise-dark); text-decoration:none;">✏️</a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('حذف امتحان و نمرات؟')">
                <input type="hidden" name="exam_id" value="<?= $e['id'] ?>">
                <button type="submit" name="delete_exam" class="btn-del" style="padding: 5px 10px;">🗑️</button>
              </form>
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
            <td><?= to_persian_num(htmlspecialchars($n['date'])) ?></td>
            <td><?= to_persian_num(count($imgs)) ?> تصویر</td>
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
function setRefToday() {
    const today = '<?= get_jalali_today() ?>';
    const parts = today.split('/');
    if (document.getElementById('ref_y')) {
        document.getElementById('ref_y').value = parts[0];
        document.getElementById('ref_m').value = parts[1];
        document.getElementById('ref_d').value = parts[2];
    }
}

function toPersianNum(str) {
    const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str.toString().replace(/\d/g, x => fa[en.indexOf(x)]);
}

function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock-time').textContent = toPersianNum(`${h}:${m}:${s}`);
}
setInterval(updateClock, 1000);
updateClock();

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
