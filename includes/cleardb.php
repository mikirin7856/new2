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

// Получаем текущую дату в формате ддмм
$date = date('dm');

// Создаем имена новых таблиц
$newTableNameCC = "cc" . $date;
$newTableNameFullLog = "full_log" . $date;

// Копируем данные в новые таблицы
$db->query("CREATE TABLE $newTableNameCC LIKE cc");
$db->query("INSERT INTO $newTableNameCC SELECT * FROM cc");

$db->query("CREATE TABLE $newTableNameFullLog LIKE full_log");
$db->query("INSERT INTO $newTableNameFullLog SELECT * FROM full_log");

// Очистка оригинальных таблиц
$db->query("TRUNCATE TABLE cc");
$db->query("TRUNCATE TABLE full_log");

$db->close();

exit();
?>
