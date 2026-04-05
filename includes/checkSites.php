<?php

$filePath = __DIR__ . '/../inject/sites_data.txt';
$missingSites = [];

// Проверяем, существует ли файл и читаем его содержимое
if (file_exists($filePath)) {
    $sitesData = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($sitesData as $data) {
        list($siteUrl, $searchText) = explode('|', trim($data));

        // Настройка cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $siteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36');

        // Дополнительные заголовки
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            // Другие заголовки, которые могут понадобиться
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Получаем содержимое сайта
        $content = curl_exec($ch);
        curl_close($ch);

        // Проверяем наличие строки
        if ($content === false || strpos($content, $searchText) === false) {
            array_push($missingSites, $siteUrl);
        }
    }
}

// Сохраняем данные в файл JSON
file_put_contents(__DIR__ . '/../file_js/missing_sites.json', json_encode($missingSites, JSON_PRETTY_PRINT));
?>
