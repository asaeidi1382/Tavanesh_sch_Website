<?php

require_once 'auth_new.php';

requireAdmin();

$db = getDB();

$id = (int)($_GET['id'] ?? 0);

if ($id) {

    $stmt = $db->prepare("
        DELETE FROM students
        WHERE id = ?
    ");

    $stmt->execute([$id]);
}

header('Location: students.php');
exit;