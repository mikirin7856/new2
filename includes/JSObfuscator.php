<?php

function substituteCharacters($input) {
        $substitutedStr = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $substitutedStr .= chr(ord($input[$i]) + 1);
        }
        return $substitutedStr;
    }

function ofStr($str) {
    $substitutedStr = substituteCharacters($str);
    $reversedStr = strrev($substitutedStr);
    $hex = '';
    for ($i = 0; $i < strlen($reversedStr); $i++) {
        $hex .= '' . dechex(ord($reversedStr[$i]));
    }

    return $hex;
}

$names = array();

function generateRandomString() {
        $result = '';
        $characters = 'Il1';
        $length = mt_rand(10, 14);
        for ($i = 0; $i < $length; $i++) {
            $randomChar = $characters[mt_rand(0, strlen($characters) - 1)];
            $result .= $randomChar;
        }
        return "_".$result;
    }

function generateName() {
    global $names;

    $generated = '';

    while (true) {
        $generated = generateRandomString();
        if (!in_array($generated, $names)) {
            $names[] = $generated;
            break;
        }
    }
    return $generated;
}

function substituteSums($script) {
    $pattern = '/(\w+)\s*\+\s*(\w+)/';
    $replace = '(lsll = (lI, Il) => Il === 0 ? lI : lsll(lI^Il, (lI & Il) << 1))($1, $2)';
    $script = preg_replace($pattern, $replace, $script);
    return $script;
}

function addSemicolons($script) {

    $script = trim($script);

    $lines = explode("\n", $script);
    $script = '';
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {

            if (substr($line, -1) !== ';' && substr($line, -1) !== '{' && substr($line, -1) !== ',' && substr($line, -1) !== '(') {

                $line .= ';';
            }
            $script .= $line . "\n";
        }
    }

    return $script;
}

function obfuscatorStrings($script) {
    $stringRegex = "/(['\"])(.*?)\\1/";
  	$count = 0;
    $script = preg_replace_callback($stringRegex, function($matches) use (&$count) {
      	$count++;
        $hexString = ofStr($matches[2]);
        return 'des("' . $hexString . '")';
    }, $script);
	if ($count > 0){
    	$script .= '
    function des(hex){
        function reverseSubstituteCharacters(input) {
            let originalStr = \'\';
            for (let i = 0; i < input.length; i++) {
                originalStr += String.fromCharCode(input.charCodeAt(i) - 1);
            }
            return originalStr;
        }
        let reversedStr = \'\';
        for (let i = 0; i < hex.length; i += 2) {
            let charCode = parseInt(hex.substr(i, 2), 16);
            reversedStr = String.fromCharCode(charCode) + reversedStr;
        }
        let originalString = reverseSubstituteCharacters(reversedStr);

        return originalString;
    };
    ';
    }
    return $script;
}

function minify($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(

            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',

            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',

            '#;+\}#',

            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',

            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ),
    $input);
}

function transformString($string) {
    $transformedString = preg_replace('/\n/', '"+\n"', $string);
    $transformedString = '"' . $transformedString . '"';
    return $transformedString;
}

function replaceToQuotes($input) {
    $regex = '/`([^`]*?)`/s';
    $result = preg_replace_callback($regex, function($matches) {
        $stringWithEscapes = str_replace("\n", '\\n', $matches[1]);
        $stringWithEscapes = preg_replace('/\$\{(.*?)\}/', '" + $1 + "', $stringWithEscapes);
        return '"' . $stringWithEscapes . '"';
    }, $input);

    return $result;
}

function renameParameters($script) {
    $lines = explode("\n", $script);
    $funRgx = '/\bfunction\s+([^\(]+)/';
    $paramsRgx = '/(\w+)\s*(?=\)|,|\.\.\.)/';
    $params = [];
    $opened_braces = 0;
    $closed_braces = 0;
    $newFun = [];
    $funsStartedLine = [];

    preg_match_all($funRgx, $script, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $functionName = $match[0];
        foreach ($lines as $index => $line) {
            if (strpos($line, $functionName) !== false && preg_match('/\([^)\s]+\)/', $line)) {
                $funsStartedLine[] = $index;
                break;
            }
        }
    }

    foreach ($funsStartedLine as $startIndex) {
        for ($index = $startIndex; $index < count($lines); $index++) {
            $line = $lines[$index];
            if ($startIndex === $index) {
                preg_match_all($paramsRgx, $line, $matches);
                foreach ($matches[0] as $p) {
                    $params[] = [$p, generateName()]; 
                }
            }

            foreach ($params as $pname) {
                $line = preg_replace('/\b' . preg_quote($pname[0], '/') . '\b/', $pname[1], $line);
                $newFun[] = $line;
            }

            $opened_braces += substr_count($line, '{');

            $closed_braces += substr_count($line, '}');

            if ($opened_braces > 0 && $opened_braces === $closed_braces) {
                for ($lineIndex = 0; $lineIndex < count($newFun); $lineIndex++) {
                    $lines[$startIndex + $lineIndex] = $newFun[$lineIndex];
                }
                $opened_braces = 0;
                $closed_braces = 0;
                $params = [];
                $newFun = [];
                break;
            }
        }
    }

    return implode("\n", $lines);
}

function renameVars($script) {
    $templateStringRegex = '`(?:\\\\[\\s\\S]|[^\\\\`])*`';
    $templateStrings = [];
    $script = preg_replace_callback('/' . $templateStringRegex . '/', function($match) use (&$templateStrings) {
        $index = array_push($templateStrings, $match[0]) - 1;
        return '__TEMPLATE_STRING_' . $index . '__';
    }, $script);

    $declarationRegex = '/\b(let|var|const|function)\s+([a-zA-Z_]\w*|\(\s*\)|\w+\s*=\s*function\s*\()/';
    $variableMap = [];
    $script = preg_replace_callback($declarationRegex, function($matches) use (&$variableMap) {
        $declaration = $matches[2];
        if (!isset($variableMap[$declaration])) {
            $variableMap[$declaration] = generateName();
        }
        return $matches[1] . ' ' . $variableMap[$declaration];
    }, $script);

    foreach ($variableMap as $oldName => $newName) {
        $regex = '/\b' . preg_quote($oldName, '/') . '\b/';
        $script = preg_replace($regex, $newName, $script);
    }

    foreach ($templateStrings as $i => $templateString) {
        $marker = '__TEMPLATE_STRING_' . $i . '__';
        $script = str_replace($marker, $templateString, $script);
    }

    return $script;
}

function removeComments($text) {
    $text = preg_replace('/\/\*[\s\S]*?\*\//', '', $text);
    $text = preg_replace('/\/\/.*/', '', $text);
    return $text;
}

function transformObject($script) {
    $objectRgx = '/let\s+(\w+)\s*=\s*{([^}]*)}/';
    preg_match_all($objectRgx, $script, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $objectName = $match[1];
        $objectProperties = $match[2];

        $expandedObject = "let $objectName = {};\n";
        $properties = explode(',', $objectProperties);

        foreach ($properties as $prop) {
            list($key, $value) = array_map('trim', explode(':', $prop));
            $expandedObject .= "{$objectName}[$key] = $value;\n";
        }

        $script = str_replace($match[0], $expandedObject, $script);
    }

    return $script;
}

function unminify($script) {
    $script = preg_replace('/;/', ";\n", $script);
    return $script;
}

function numbersToHex($script) {
    $numberRegex = "/(?<!['\"])\b(\d+)\b(?!['\"])/";
    $script = preg_replace_callback($numberRegex, function($matches) {
        $hexValue = dechex($matches[1]);
        return '0x' . $hexValue;
    }, $script);

    return $script;
}

function obfuscator($script) {
    $script = removeComments($script);
    $script = unminify($script);
    $script = transformObject($script);
    $script = replaceToQuotes($script);
    $script = obfuscatorStrings($script);

    $script = addSemicolons($script);
    $script = renameVars($script);
    $script = renameParameters($script);
  	$script = substituteSums($script);
    $script = numbersToHex($script);
    $script = minify($script);
    $script = str_replace(array("\r\n", "\r", "\n"), "\\n", $script);
    $script = str_replace("\t", "\\t", $script);

    return $script;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["JS_code"])) {

            $JS_code = $_POST["JS_code"];

            if (!empty($JS_code)) {

                echo obfuscator($JS_code);
            } else {

            }
    } else {


    }
} else {

?>


<?php
}
?>