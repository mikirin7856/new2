<?php

session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

// Проверка роли пользователя
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    header('Location: ../dashboard.php');
    exit();
}
require 'config.php';

if (isset($_GET['user_id']) && isset($_GET['site_id'])) {
    $userId = $_GET['user_id'];
    $siteId = $_GET['site_id'];

    $query = "DELETE FROM user_sites WHERE user_id = ? AND url_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('is', $userId, $siteId);
    $stmt->execute();
}