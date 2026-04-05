
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - Merchant Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a; /* черный фон */
            color: #d4af37; /* золотой текст */
            margin: 0;
            padding: 20px;
        }

        .login-form {
            width: 300px;
            padding: 40px;
            background-color: #333; /* темный фон формы */
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            margin: 100px auto 0; /* центрирование формы на странице */
        }

        .login-form h2 {
            color: #d4af37;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #d4af37; /* золотая граница */
            background-color: #1a1a1a; /* черный фон */
            color: #d4af37; /* золотой текст */
            box-sizing: border-box;
        }

        .login-form button {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: none;
            background-color: #d4af37; /* золотой фон */
            color: #1a1a1a; /* черный текст */
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-form button:hover {
            background-color: #bfa145; /* темно-золотой фон при наведении */
        }
    </style>
</head>
<body>
    <div class="login-form">
        <form action="includes/process_registration.php" method="post">
            <h2>Registration</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>