<?php
require_once 'auth.php';
requireLogin();
if ($_SESSION['role'] !== 'teacher') die('دسترسی محدود شده است.');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>فیش حقوقی</title>
<style>body{font-family:Vazirmatn,sans-serif; padding:20px;}</style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">











    <h1>فیش حقوقی</h1>
    <p>این بخش در حال طراحی است.</p>
    <a href="dashboard.php">بازگشت به داشبورد</a>




</main>
</div>
</body>
</html>