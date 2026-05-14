<?php
date_default_timezone_set('Asia/Tehran');
session_start();

define('DB_PATH', __DIR__ . '/database.sqlite');

function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // جدول کاربران
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            username    TEXT UNIQUE NOT NULL,
            email       TEXT UNIQUE,
            password    TEXT NOT NULL,
            role        TEXT DEFAULT 'student',
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // ستون‌های مورد نیاز
        try { $db->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'student'"); } catch(Exception $e){}

        // جدول اقساط شهریه
        $db->exec("CREATE TABLE IF NOT EXISTS tuition (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            national_id     TEXT NOT NULL,
            installment_no  INTEGER NOT NULL,
            amount          INTEGER NOT NULL DEFAULT 0,
            due_date        TEXT,
            paid_amount     INTEGER DEFAULT 0,
            paid_date       TEXT,
            status          TEXT DEFAULT 'unpaid',
            academic_year   TEXT DEFAULT '1404-1405'
        )");
        try { $db->exec("ALTER TABLE tuition ADD COLUMN academic_year TEXT DEFAULT '1404-1405'"); } catch(Exception $e){}
        try { $db->exec("ALTER TABLE tuition DROP COLUMN description"); } catch(Exception $e){}

        // جدول پروفایل دانش‌آموزان
        $db->exec("CREATE TABLE IF NOT EXISTS student_profiles (
            national_id    TEXT NOT NULL,
            academic_year  TEXT NOT NULL DEFAULT '1404-1405',
            first_name     TEXT,
            last_name      TEXT,
            grade          TEXT,
            major          TEXT,
            father_name    TEXT,
            mother_name    TEXT,
            mother_phone   TEXT,
            father_phone   TEXT,
            home_phone     TEXT,
            left_handed    INTEGER DEFAULT 0,
            seat_no        TEXT,
            address        TEXT,
            student_phone  TEXT,
            PRIMARY KEY (national_id, academic_year)
        )");

        // جدول پروفایل کارکنان
        $db->exec("CREATE TABLE IF NOT EXISTS staff_profiles (
            national_id    TEXT NOT NULL,
            academic_year  TEXT NOT NULL,
            first_name     TEXT,
            last_name      TEXT,
            birth_date     TEXT,
            birth_place    TEXT,
            education      TEXT,
            position       TEXT,
            father_name    TEXT,
            home_phone     TEXT,
            address        TEXT,
            schedule       TEXT,
            mobile_phone   TEXT,
            contract_date  TEXT,
            bank           TEXT,
            sheba          TEXT,
            letter_no      TEXT,
            PRIMARY KEY (national_id, academic_year)
        )");

        // جدول فیش حقوقی
        $db->exec("CREATE TABLE IF NOT EXISTS paystubs (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            national_id    TEXT NOT NULL,
            academic_year  TEXT NOT NULL,
            title          TEXT NOT NULL,
            file_path      TEXT NOT NULL,
            upload_date    DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
    return $db;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($usernameOrEmail, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :u OR email = :u");
    $stmt->execute([':u' => $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['role']       = $user['role'] ?: 'student';

        // پیدا کردن نام واقعی از پروفایل
        $fullName = getUserRealName($user['username'], $user['role']);
        $_SESSION['full_name']  = $fullName ?: $user['username'];

        return ['success' => true];
    }
    return ['success' => false];
}

function getUserRealName($national_id, $role, $academic_year = null) {
    if (!$academic_year) {
        $academic_year = $_SESSION['active_year'] ?? '1404-1405';
    }
    $db = getDB();
    if ($role === 'staff') {
        $stmt = $db->prepare("SELECT first_name, last_name FROM staff_profiles WHERE national_id = ? AND academic_year = ?");
    } else {
        $stmt = $db->prepare("SELECT first_name, last_name FROM student_profiles WHERE national_id = ? AND academic_year = ?");
    }
    $stmt->execute([$national_id, $academic_year]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile && ($profile['first_name'] || $profile['last_name'])) {
        return trim($profile['first_name'] . ' ' . $profile['last_name']);
    }
    return null;
}

function registerUser($username, $email, $password) {
    if (strlen($username) < 3)
        return ['success' => false, 'error' => 'Username must be at least 3 characters.'];
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['success' => false, 'error' => 'Invalid email address.'];
    if (strlen($password) < 6)
        return ['success' => false, 'error' => 'Password must be at least 6 characters.'];

    $db = getDB();
    try {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email ?: null, $hash]);
        return ['success' => true];
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false)
            return ['success' => false, 'error' => 'already taken'];
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

// ایجاد یا به‌روزرسانی حساب دانش‌آموز (برای ایمپورت)
function upsertStudent($national_id, $first_name, $last_name, $academic_year = '1404-1405') {
    $db = getDB();
    $hash = password_hash($national_id, PASSWORD_BCRYPT);
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$national_id]);
    $user_exists = $stmt->fetch();

    if (!$user_exists) {
        $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')")
            ->execute([
                $national_id,
                $national_id . '@tavanesh.local',
                $hash
            ]);
        $status = 'created';
    } else {
        $db->prepare("UPDATE users SET role = 'student' WHERE username = ?")
           ->execute([$national_id]);
        $status = 'updated';
    }

    $stmt = $db->prepare("SELECT national_id FROM student_profiles WHERE national_id = ? AND academic_year = ?");
    $stmt->execute([$national_id, $academic_year]);
    if (!$stmt->fetch()) {
        $db->prepare("INSERT INTO student_profiles (national_id, academic_year, first_name, last_name) VALUES (?, ?, ?, ?)")
           ->execute([$national_id, $academic_year, $first_name, $last_name]);
    } else {
        $db->prepare("UPDATE student_profiles SET first_name = ?, last_name = ? WHERE national_id = ? AND academic_year = ?")
           ->execute([$first_name, $last_name, $national_id, $academic_year]);
    }

    return $status;
}

// ایجاد یا به‌روزرسانی حساب کارمند
function upsertStaff($national_id, $first_name, $last_name, $academic_year = '1404-1405') {
    $db = getDB();
    $hash = password_hash($national_id, PASSWORD_BCRYPT);
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$national_id]);
    $user_exists = $stmt->fetch();

    if (!$user_exists) {
        $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'staff')")
            ->execute([
                $national_id,
                $national_id . '@tavanesh.local',
                $hash
            ]);
        $status = 'created';
    } else {
        $db->prepare("UPDATE users SET role = 'staff' WHERE username = ?")
           ->execute([$national_id]);
        $status = 'updated';
    }

    $stmt = $db->prepare("SELECT national_id FROM staff_profiles WHERE national_id = ? AND academic_year = ?");
    $stmt->execute([$national_id, $academic_year]);
    if (!$stmt->fetch()) {
        $db->prepare("INSERT INTO staff_profiles (national_id, academic_year, first_name, last_name) VALUES (?, ?, ?, ?)")
           ->execute([$national_id, $academic_year, $first_name, $last_name]);
    } else {
        $db->prepare("UPDATE staff_profiles SET first_name = ?, last_name = ? WHERE national_id = ? AND academic_year = ?")
           ->execute([$first_name, $last_name, $national_id, $academic_year]);
    }

    return $status;
}

function get_jalali_today() {
    // This is a VERY simplified approximation for demonstration
    // In a real app, use a proper Jalali library.
    // We'll use the system date if it's already set to Jalali,
    // or just return a hardcoded/calculated string for now.
    // Given the context of the school, let's assume we want YYYY/MM/DD.
    // A better way is to use a simple Gregorian to Jalali algorithm.

    $g_y = (int)date('Y');
    $g_m = (int)date('m');
    $g_d = (int)date('d');

    $d_4 = $g_y % 4;
    $g_a = array(0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $doy_g = $g_a[$g_m] + $g_d;
    if ($d_4 == 0 && $g_m > 2) $doy_g++;
    $d_33 = (int)(($g_y - 16) % 132 / 33);
    $leap = (int)(($g_y - 16) % 132 * 8 / 33);
    $d_4 = (int)(($g_y - 16) % 4);
    if ($d_4 == 0) $leap++;

    $jy = $g_y - 621;
    $jd = $doy_g - 79;

    if ($jd <= 0) {
        $jy--;
        $jd += 365;
        if ($d_4 == 1) $jd++;
    }

    if ($jd <= 186) {
        $jm = (int)(($jd - 1) / 31) + 1;
        $jd = ($jd - 1) % 31 + 1;
    } else {
        $jm = (int)(($jd - 187) / 30) + 7;
        $jd = ($jd - 187) % 30 + 1;
    }

    return sprintf("%04d/%02d/%02d", $jy, $jm, $jd);
}

function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    $jy += (int)(($days - 1) / 365);
    if ($days > 365) $days = ($days - 1) % 365;
    if ($days < 186) {
        $jm = 1 + (int)($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

function convert_to_jalali($date_str) {
    if (empty($date_str)) return "";
    
    // Check if it's already in YYYY/MM/DD (Jalali) format
    if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date_str)) {
        return $date_str;
    }

    $parts = explode(' ', $date_str);
    $date_parts = explode('-', $parts[0]);
    if (count($date_parts) !== 3) return $date_str;
    $jalali = gregorian_to_jalali((int)$date_parts[0], (int)$date_parts[1], (int)$date_parts[2]);
    return sprintf("%04d/%02d/%02d", $jalali[0], $jalali[1], $jalali[2]);
}

function to_persian_num($str) {
    $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($en, $fa, (string)$str);
}
