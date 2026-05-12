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
function upsertStudent($national_id, $full_name) {
    $db = getDB();
    $hash = password_hash($national_id, PASSWORD_BCRYPT);
    // اگر وجود داشت فقط نام را آپدیت کن، وگرنه بساز
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$national_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $db->prepare("UPDATE users SET full_name = ? WHERE username = ?")
           ->execute([$full_name, $national_id]);
        return 'updated';
    } else {
            -$db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)")
            ->execute([
                $national_id,
                $national_id . '@tavanesh.local',
                $hash,
                $full_name
            ]);
        return 'created';
    }
}
