<?php
require_once 'auth.php';
requireLogin();
if ($_SESSION['role'] !== 'teacher') die('دسترسی محدود شده است.');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>اطلاعات پرسنلی</title>
<style>body{font-family:Vazirmatn,sans-serif; padding:20px;}</style>
</head>
<body>
    <h1>اطلاعات پرسنلی</h1>
    <p>این بخش در حال طراحی است.</p>
    <a href="dashboard.php">بازگشت به داشبورد</a>
</body>
</html>