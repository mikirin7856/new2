<?php
include __DIR__ . '/../vendor/autoload.php';
require 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $db->real_escape_string($_POST['username']);
    $password = $db->real_escape_string($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Проверка наличия строк в таблице users
    $countSql = "SELECT COUNT(*) AS total FROM users";
    $countResult = $db->query($countSql);
    $countRow = $countResult->fetch_assoc();
    $usersCount = $countRow['total'];

    // Определение роли пользователя
    $role = ($usersCount == 0) ? 'admin' : 'user';
    
    // Проверка наличия пользователя с таким же именем
    $checkUserSql = "SELECT * FROM users WHERE username = '$username'";
    $checkUserResult = $db->query($checkUserSql);
    
    if ($checkUserResult->num_rows > 0) {
        echo "User with this username already exists";
    } else {
        $insertSql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashedPassword', '$role')";
        if ($db->query($insertSql) === TRUE) {
            echo "User successfully registered";
        } else {
            echo "Error registering user: " . $db->error;
        }
    }
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #d4af37;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            padding: 20px;
            text-align: center;
        }
        .center-content {
            padding: 20px;
            border: 2px solid #2f2e2c;
            background-color: #333;
            color: #d4af37;
            margin-top: 50px;
            border-radius: 10px;
        }
        img {
            margin-top: 15px;
            margin-bottom: 15px;
            max-width: 100%;
            height: auto;
        }
        .complete-btn {
            background-color: #d4af37;
            color: #333;
            border: none;
            padding: 10px 20px;
            text-transform: uppercase;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .complete-btn:hover {
            background-color: #b3922f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="center-content">
            <p>Registration Complete</p>
            <form action="../login.php">
                <button type="submit" class="complete-btn">Complete</button>
            </form>
        </div>
    </div>
</body>
</html>
