<?php
include __DIR__ . '/../vendor/autoload.php';
require 'config.php'; 

$g = new \PHPGangsta_GoogleAuthenticator();

$username = $_POST['username'];
$code = $_POST['code'];

// Получаем секретный ключ из базы данных для этого пользователя
// Предположим, у вас есть подключение к базе данных $conn
$sql = "SELECT secret FROM users WHERE username='$username'";
$result = $db->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $secret = $row['secret'];

    $checkResult = $g->verifyCode($secret, $code, 2); // 2 = 2*30sec clock tolerance
    if ($checkResult) {
        echo 'Login successful';
        // Здесь можете перенаправить пользователя на защищенную страницу
    } else {
        echo 'Invalid 2FA code';
        // Обработка неверного кода 2FA
    }
} else {
    echo 'User not found';
}

?>