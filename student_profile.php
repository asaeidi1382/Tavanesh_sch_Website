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

$stmt = $db->prepare("SELECT * FROM student_profiles WHERE national_id = ? AND academic_year = ?");
$stmt->execute([$national_id, $academic_year]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('دانش‌آموز پیدا نشد.');
}
?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<title>پروفایل دانش‌آموز</title>

<style>

body{
    font-family:Vazirmatn,sans-serif;
    background:#f5fbfd;
    margin:0;
}

.container{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:35px;
    border-radius:24px;
}

.item{
    margin-bottom:18px;
}

.label{
    font-weight:700;
    color:#0c8790;
}

</style>

</head>

<body>

<div class="container">

<?php
$display_name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?: $student['national_id'];
?>

<h1>
<?= to_persian_num(htmlspecialchars($display_name)) ?>
</h1>

<div style="margin-bottom:20px; color:var(--gray); font-size:0.9rem;">
    سال تحصیلی: <?= to_persian_num(htmlspecialchars($academic_year)) ?>
</div>

<div class="item">
<span class="label">کد ملی:</span>
<?= to_persian_num(htmlspecialchars($student['national_id'])) ?>
</div>

<div class="item">
<span class="label">پایه:</span>
<?= to_persian_num(htmlspecialchars($student['grade'])) ?>
</div>

<div class="item">
<span class="label">رشته:</span>
<?= to_persian_num(htmlspecialchars($student['major'])) ?>
</div>

<div class="item">
<span class="label">نام پدر:</span>
<?= to_persian_num(htmlspecialchars($student['father_name'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">تلفن پدر:</span>
<?= to_persian_num(htmlspecialchars($student['father_phone'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">نام مادر:</span>
<?= to_persian_num(htmlspecialchars($student['mother_name'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">تلفن مادر:</span>
<?= to_persian_num(htmlspecialchars($student['mother_phone'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">تلفن ثابت:</span>
<?= to_persian_num(htmlspecialchars($student['home_phone'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">تلفن همراه دانش‌آموز:</span>
<?= to_persian_num(htmlspecialchars($student['student_phone'] ?? '—')) ?>
</div>

<div class="item">
<span class="label">وضعیت:</span>
<?= ($student['left_handed'] ?? 0) ? 'چپ‌دست' : 'راست‌دست' ?>
</div>

<div class="item">
<span class="label">آدرس:</span>
<?= nl2br(htmlspecialchars($student['address'] ?? '—')) ?>
</div>

</div>

</body>

</html>