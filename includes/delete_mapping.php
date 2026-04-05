<?php

session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

// Проверка роли пользователя
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../dashboard.php');
    exit();
}
require 'config.php'; 

$url_id = $_GET['url_id'] ?? '';
$field_key = $_GET['field_key'] ?? '';

if ($url_id && $field_key) {
    $deleteQuery = "DELETE FROM field_mappings WHERE url_id = ? AND field_key = ?";
    if ($stmt = $db->prepare($deleteQuery)) {
        $stmt->bind_param("ss", $url_id, $field_key);
        $stmt->execute();
        $stmt->close();
    }
    // Перенаправляем обратно на страницу cards.php
    header("header('Location: ../cards.php');;
    exit();
} else {
    echo "Неверные параметры.";
}
$db->close();
?>
