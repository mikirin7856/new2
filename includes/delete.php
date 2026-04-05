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
// delete.php
require 'config.php'; 
$id = $_GET['id'] ?? null;
$referer = $_SERVER['HTTP_REFERER'] ?? '../cards.php'; // Значение по умолчанию - 'cards.php'

if ($id) {
    

    // Начало транзакции
    $db->begin_transaction();

    try {
        // Получаем user_cookie для данного id из таблицы cc
        $cookieQuery = $db->prepare("SELECT uniquecookies FROM cc WHERE id = ?");
        $cookieQuery->bind_param("i", $id);
        $cookieQuery->execute();
        $result = $cookieQuery->get_result();
        $cookieRow = $result->fetch_assoc();
        $userCookie = $cookieRow['uniquecookies'];

        // Удаление из таблицы cc
        $deleteCC = $db->prepare("DELETE FROM cc WHERE id = ?");
        $deleteCC->bind_param("i", $id);
        $deleteCC->execute();

        // Удаление из таблицы full_log
        $deleteLog = $db->prepare("DELETE FROM full_log WHERE user_cookie = ?");
        $deleteLog->bind_param("s", $userCookie);
        $deleteLog->execute();

        // Если оба запроса успешны, зафиксируем транзакцию
        $db->commit();
    } catch (Exception $e) {
        // Если произошла ошибка, откатим изменения
        $db->rollback();
        die("Ошибка при удалении: " . $e->getMessage());
    }

    $db->close();

    header('Location: ' . $referer);
    exit();
}
?>
