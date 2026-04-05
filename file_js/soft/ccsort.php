<?php
function splitArray($inputArray, $percentage) {
    shuffle($inputArray);
    $partSize = ceil(count($inputArray) * ($percentage / 100));
    $partOne = array_slice($inputArray, 0, $partSize);
    $partTwo = array_slice($inputArray, $partSize);
    return ['partOne' => $partOne, 'partTwo' => $partTwo];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $strings = [];

    if (!empty($_POST['textData'])) {
        $strings = explode("\n", str_replace("\r", "", $_POST['textData']));
    } elseif (!empty($_FILES['file']['tmp_name'])) {
        $strings = file($_FILES['file']['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    if (!empty($strings)) {
        $percentage = !empty($_POST['percentage']) ? (int)$_POST['percentage'] : 50;
        $divided = splitArray($strings, $percentage);
        $partOne = $divided['partOne'];
        $partTwo = $divided['partTwo'];

        $fileOne = 'partOne.txt';
        $fileTwo = 'partTwo.txt';
        file_put_contents($fileOne, implode("\n", $partOne));
        file_put_contents($fileTwo, implode("\n", $partTwo));
    }
}

// Обработка запросов на скачивание файлов
if (isset($_GET['download']) && file_exists($_GET['download'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="'.basename($_GET['download']).'"');
    readfile($_GET['download']);
    unlink($_GET['download']); // Опционально: удалить файл после скачивания
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Разделение списка строк</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        textarea, input[type="file"], input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box; /* Учитывает padding в ширине */
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Введите строки или загрузите файл TXT</h2>
    <form method="post" enctype="multipart/form-data">
        <textarea name="textData" rows="10" cols="30" placeholder="Введите строки здесь..."></textarea><br>
        <input type="file" name="file"><br>
        <input type="number" name="percentage" min="1" max="99" placeholder="Процент для первой части" required><br><br>
        <input type="submit" name="submit" value="Разделить строки">
    </form>

<?php if (!empty($fileOne) && !empty($fileTwo)): ?>
    <a href="?download=partOne.txt">Скачать Part One (<?php echo $percentage; ?>%)</a>
    <a href="?download=partTwo.txt">Скачать Part Two (<?php echo 100 - $percentage; ?>%)</a>
<?php endif; ?>
</body>
</html>
