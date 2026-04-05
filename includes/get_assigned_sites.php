<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    header('Location: ../dashboard.php');
    exit();
}

require 'config.php';

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    $query = "SELECT url_id FROM user_sites WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignedSites = [];
    while ($row = $result->fetch_assoc()) {
        $assignedSites[] = $row['url_id'];
        echo "<div class='flex items-center mb-2'>";
        echo "<span class='mr-2'>" . htmlspecialchars($row['url_id']) . "</span>";
        echo "<button class='bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded' onclick='removeAssignedSite(" . $userId . ", \"" . htmlspecialchars($row['url_id']) . "\")'>Delete</button>";
        echo "</div>";
    }

    // Возвращаем список назначенных сайтов для фильтрации в JavaScript
    echo "<script>var assignedSites = " . json_encode($assignedSites) . ";</script>";
}
?>
