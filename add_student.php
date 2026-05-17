<?php

require_once 'auth_new.php';

requireAdmin();

$db = getDB();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $major = trim($_POST['major'] ?? '');

    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');

    $student_phone = trim($_POST['student_phone'] ?? '');
    $father_phone = trim($_POST['father_phone'] ?? '');
    $mother_phone = trim($_POST['mother_phone'] ?? '');

    $address = trim($_POST['address'] ?? '');

    if (!$full_name || !$national_id) {

        $error = 'نام و کد ملی الزامی هستند.';

    } else {

        $check = $db->prepare("
            SELECT id FROM students
            WHERE national_id = ?
        ");

        $check->execute([$national_id]);

        if ($check->fetch()) {

            $error = 'این دانش‌آموز قبلاً ثبت شده است.';

        } else {

            $passwordHash = password_hash(
                $national_id,
                PASSWORD_DEFAULT
            );

            $stmt = $db->prepare("
                INSERT INTO students
                (
                    national_id,
                    username,
                    password,
                    full_name,

                    grade,
                    major,

                    father_name,
                    mother_name,

                    student_phone,
                    father_phone,
                    mother_phone,

                    address
                )

                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([

                $national_id,
                $national_id,
                $passwordHash,
                $full_name,

                $grade,
                $major,

                $father_name,
                $mother_name,

                $student_phone,
                $father_phone,
                $mother_phone,

                $address
            ]);

            $success = 'دانش‌آموز با موفقیت ثبت شد.';
        }
    }
}
?>

<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>افزودن دانش‌آموز</title>

<link rel="icon" href="/images/logo-T.png">

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
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

h1{
    margin-bottom:25px;
    color:#103d42;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.field label{
    display:block;
    margin-bottom:8px;
    font-size:.9rem;
    font-weight:700;
}

.field input,
.field textarea,
.field select{

    width:100%;
    padding:14px;
    border-radius:14px;
    border:1.5px solid #d7eef1;
    box-sizing:border-box;
    font-family:Vazirmatn;
}

textarea{
    min-height:120px;
    resize:vertical;
}

.full{
    grid-column:1/-1;
}

button{

    margin-top:25px;

    background:#19b8c2;

    color:#fff;

    border:none;

    padding:15px 30px;

    border-radius:16px;

    font-family:Vazirmatn;

    font-size:1rem;

    cursor:pointer;
}

.success{
    background:#e8fff1;
    color:#008a3d;
    padding:15px;
    border-radius:14px;
    margin-bottom:20px;
}

.error{
    background:#fff0f0;
    color:#c62828;
    padding:15px;
    border-radius:14px;
    margin-bottom:20px;
}

.top-links{
    margin-bottom:20px;
}

.top-links a{

    text-decoration:none;

    color:#0c8790;

    margin-left:14px;
}

</style>

  <?php include 'header_styles.php'; ?>
</head>

<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">

















<div class="container">

<div class="top-links">

<a href="students.php">لیست دانش‌آموزان</a>

<a href="admin_dashboard.php">داشبورد</a>

</div>

<h1>➕ افزودن دانش‌آموز</h1>

<?php if($success): ?>

<div class="success">

<?= $success ?>

</div>

<?php endif; ?>

<?php if($error): ?>

<div class="error">

<?= $error ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="grid">

<div class="field">
<label>نام کامل</label>
<input type="text" name="full_name" required>
</div>

<div class="field">
<label>کد ملی</label>
<input type="text" name="national_id" required>
</div>

<div class="field">
<label>پایه</label>
<select name="grade">
<option value="">انتخاب پایه</option>
<option>دهم</option>
<option>یازدهم</option>
<option>دوازدهم</option>
</select>
</div>

<div class="field">
<label>رشته</label>
<select name="major">
<option value="">انتخاب رشته</option>
<option>ریاضی</option>
<option>تجربی</option>
<option>انسانی</option>
</select>
</div>

<div class="field">
<label>نام پدر</label>
<input type="text" name="father_name">
</div>

<div class="field">
<label>نام مادر</label>
<input type="text" name="mother_name">
</div>

<div class="field">
<label>شماره دانش‌آموز</label>
<input type="text" name="student_phone">
</div>

<div class="field">
<label>شماره پدر</label>
<input type="text" name="father_phone">
</div>

<div class="field">
<label>شماره مادر</label>
<input type="text" name="mother_phone">
</div>

<div class="field full">
<label>آدرس</label>
<textarea name="address"></textarea>
</div>

</div>

<button type="submit">

ثبت دانش‌آموز

</button>

</form>

</div>







</main>
</div>
</body>
</html>