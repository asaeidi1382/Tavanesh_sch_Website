<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<?php
require_once 'auth.php';
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
