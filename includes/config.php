<?php
// config.php

$host = 'localhost';
$dbUsername = 'user';
$dbPassword = 'pass';
$dbName = 'dbname';

// Создаем подключение к базе данных
$db = new mysqli($host, $dbUsername, $dbPassword, $dbName);

// Проверяем подключение
if ($db->connect_error) {
    die("Ошибка подключения: " . $db->connect_error);
}
?>
