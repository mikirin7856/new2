<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    die("Unauthorized access");
}

if (isset($_GET['form'])) {
    $formName = $_GET['form'];
    $formPath = '../inject/forms/' . $formName . '.txt';
    
    if (file_exists($formPath)) {
        $formContent = file_get_contents($formPath);
        
        if (isset($_GET['action']) && $_GET['action'] === 'edit') {
            // Возвращаем содержимое формы для редактирования
            echo $formContent;
        } else {
            // Отображаем форму
            header('Content-Type: text/html');
            echo "<!DOCTYPE html>
                  <html>
                  <head>
                      <meta charset='UTF-8'>
                      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                      <title>Form Preview</title>
                  </head>
                  <body style='background-color:white;'>
                      $formContent
                  </body>
                  </html>";
        }
    } else {
        echo "Form not found.";
    }
} else {
    echo "No form specified.";
}
?>