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
$id = isset($_GET['id']) ? $db->real_escape_string($_GET['id']) : null;
$url_id = isset($_GET['url_id']) ? $db->real_escape_string($_GET['url_id']) : null;
$field_key = isset($_GET['field_key']) ? $db->real_escape_string($_GET['field_key']) : null;

if ($url_id && $field_key) {
    $query = "DELETE FROM field_mappings WHERE url_id = '$url_id' AND field_key = '$field_key'";
    if ($db->query($query)) {
        echo "Record deleted successfully.";
    } else {
        echo "Error deleting record: " . $db->error;
    }
} else {
    echo "URL ID or Field Key not provided.";
}

$db->close();

// Перенаправление обратно на страницу details.php
header('Location: details.php?id=' . $id . '&url_id=' . urlencode($url_id));
exit();
?>
