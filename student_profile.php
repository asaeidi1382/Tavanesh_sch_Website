<?php

require_once 'auth_new.php';

requireAdmin();

$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$academic_year = $_GET['year'] ?? '1404-1405';

$stmt = $db->prepare("
    SELECT *
    FROM student_profiles
    WHERE national_id = (SELECT username FROM users WHERE id = ?) AND academic_year = ?
");

$stmt->execute([$id, $academic_year]);

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
// واکشی نام از جدول کاربران چون در پروفایل ممکن است خالی باشد
$u_stmt = $db->prepare("SELECT full_name FROM users WHERE username = ?");
$u_stmt->execute([$student['national_id']]);
$user_data = $u_stmt->fetch();
$display_name = $user_data['full_name'] ?: ($student['first_name'] . ' ' . $student['last_name']);
?>

<h1>
<?= htmlspecialchars($display_name) ?>
</h1>

<div style="margin-bottom:20px; color:var(--gray); font-size:0.9rem;">
    سال تحصیلی: <?= htmlspecialchars($academic_year) ?>
</div>

<div class="item">
<span class="label">کد ملی:</span>
<?= htmlspecialchars($student['national_id']) ?>
</div>

<div class="item">
<span class="label">پایه:</span>
<?= htmlspecialchars($student['grade']) ?>
</div>

<div class="item">
<span class="label">رشته:</span>
<?= htmlspecialchars($student['major']) ?>
</div>

<div class="item">
<span class="label">نام پدر:</span>
<?= htmlspecialchars($student['father_name']) ?>
</div>

<div class="item">
<span class="label">شماره پدر:</span>
<?= htmlspecialchars($student['father_phone']) ?>
</div>

<div class="item">
<span class="label">آدرس:</span>
<?= nl2br(htmlspecialchars($student['address'])) ?>
</div>

</div>

</body>

</html>