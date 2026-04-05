<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_role']) || 
    ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $obfuscatedCode = $_POST['code'];
    
    // Создаем директорию, если она не существует
    $dir = __DIR__ . '/file_js';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Сохраняем обфусцированный код
    $result = file_put_contents($dir . '/sniffobf.txt', $obfuscatedCode);
    
    if ($result !== false) {
        echo 'success';
    } else {
        echo 'error: Failed to save file';
    }
} else {
    echo 'error: Invalid request';
}
?>