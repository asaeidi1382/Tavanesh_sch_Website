<?php
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
            full_name   TEXT DEFAULT '',
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // اضافه کردن ستون full_name به جدول قدیمی (اگر وجود نداشت)
        try { $db->exec("ALTER TABLE users ADD COLUMN full_name TEXT DEFAULT ''"); } catch(Exception $e){}

        // جدول اقساط شهریه
        $db->exec("CREATE TABLE IF NOT EXISTS tuition (
            id              INTEGER PRIMARY KEY AUTOINCREMENT,
            national_id     TEXT NOT NULL,
            installment_no  INTEGER NOT NULL,
            description     TEXT,
            amount          INTEGER NOT NULL DEFAULT 0,
            due_date        TEXT,
            paid_amount     INTEGER DEFAULT 0,
            paid_date       TEXT,
            status          TEXT DEFAULT 'unpaid'
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
        $_SESSION['full_name']  = $user['full_name'] ?: $user['username'];
        return ['success' => true];
    }
    return ['success' => false];
}

function registerUser($username, $email, $password, $full_name = '') {
    if (strlen($username) < 3)
        return ['success' => false, 'error' => 'Username must be at least 3 characters.'];
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['success' => false, 'error' => 'Invalid email address.'];
    if (strlen($password) < 6)
        return ['success' => false, 'error' => 'Password must be at least 6 characters.'];

    $db = getDB();
    try {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email ?: null, $hash, $full_name]);
        return ['success' => true];
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false)
            return ['success' => false, 'error' => 'already taken'];
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

// ایجاد یا به‌روزرسانی حساب دانش‌آموز (برای ایمپورت)
function upsertStudent($national_id, $full_name, $academic_year = '1404-1405') {
    $db = getDB();
    $hash = password_hash($national_id, PASSWORD_BCRYPT);
    // اگر وجود داشت فقط نام را آپدیت کن، وگرنه بساز
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$national_id]);
    $user_exists = $stmt->fetch();

    if (!$user_exists) {
        $db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)")
            ->execute([
                $national_id,
                $national_id . '@tavanesh.local',
                $hash,
                $full_name
            ]);
        $status = 'created';
    } else {
        $db->prepare("UPDATE users SET full_name = ? WHERE username = ?")
           ->execute([$full_name, $national_id]);
        $status = 'updated';
    }

    // اطمینان از وجود پروفایل برای این سال تحصیلی
    $stmt = $db->prepare("SELECT national_id FROM student_profiles WHERE national_id = ? AND academic_year = ?");
    $stmt->execute([$national_id, $academic_year]);
    if (!$stmt->fetch()) {
        $db->prepare("INSERT INTO student_profiles (national_id, academic_year) VALUES (?, ?)")
           ->execute([$national_id, $academic_year]);
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
