<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<?php

session_start();

session_destroy();

header('Location: admin_login.php');

exit;