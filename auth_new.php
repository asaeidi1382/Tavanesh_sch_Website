<?php

session_start();

require_once 'config.php';

function isLoggedIn() {

    return isset($_SESSION['student_id']);
}

function requireLogin() {

    if (!isLoggedIn()) {

        header('Location: login.php');
        exit;
    }
}

function studentLogin($username, $password) {

    $db = getDB();

    $stmt = $db->prepare("
        SELECT * FROM students
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $student &&
        password_verify($password, $student['password'])
    ) {

        $_SESSION['student_id'] = $student['id'];

        $_SESSION['username'] = $student['username'];

        $_SESSION['full_name'] = $student['full_name'];

        return true;
    }

    return false;
}

function adminLogin($username, $password) {

    $db = getDB();

    $stmt = $db->prepare("
        SELECT * FROM admins
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $admin &&
        password_verify($password, $admin['password'])
    ) {

        $_SESSION['admin_id'] = $admin['id'];

        $_SESSION['admin_name'] = $admin['full_name'];

        return true;
    }

    return false;
}

function isAdmin() {

    return isset($_SESSION['admin_id']);
}

function requireAdmin() {

    if (!isAdmin()) {

        header('Location: admin_login.php');
        exit;
    }
}