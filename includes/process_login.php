<?php
session_start();
include __DIR__ . '/../vendor/autoload.php';
require 'config.php'; 

$username = $_POST['username'];
$password = $_POST['password'];

// Проверяем имя пользователя и пароль
$query = $db->prepare("SELECT * FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        // Успешный вход
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: ../cards.php?sort=id&order=desc');
        exit();
    } else {
        echo "Неверное имя пользователя или пароль";
    }
} else {
    echo "Неверное имя пользователя или пароль";
}
$db->close();
?>
