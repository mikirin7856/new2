<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

require 'config.php'; 




if ($_POST['action'] == 'Block') {
    $id = isset($_POST['id']) ? $db->real_escape_string($_POST['id']) : '';
    $block_field = isset($_POST['field']) ? $db->real_escape_string($_POST['field']) : '';
    $block_as = isset($_POST['assign_as']) ? $db->real_escape_string($_POST['assign_as']) : '';

    $query = "SELECT url_id FROM cc WHERE id = '$id'";
    $result = $db->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $url_id = $row['url_id'];

        // Проверяем наличие конкретной записи с field_key и assign_as
        $checkQuery = "SELECT * FROM field_mappings WHERE url_id = '$url_id' AND field_key = '$block_field' AND assign_as = '$block_as'";
        $checkResult = $db->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            // Обновление существующей записи
            $updateMappingQuery = "UPDATE field_mappings SET block = 1 WHERE url_id = '$url_id' AND field_key = '$block_field' AND assign_as = '$block_as'";
            if (!$db->query($updateMappingQuery)) {
                echo "Error updating block: " . $db->error;
            } else {
                echo "Field '$block_field' assigned as '$block_as' has been blocked for site: $url_id";
            }
        } else {
            // Если такой записи нет, выводим сообщение
            echo "No mapping found for field '$block_field' with assignment '$block_as' for URL ID '$url_id'.";
        }
    } else {
        echo "No data found for this ID.";
    }
}
 elseif ($_POST['action'] == 'Assign') {
    $id = isset($_POST['id']) ? $db->real_escape_string($_POST['id']) : '';
    $field = isset($_POST['field']) ? $db->real_escape_string($_POST['field']) : '';
    $assign_as = isset($_POST['assign_as']) ? $db->real_escape_string($_POST['assign_as']) : '';

    $query = "SELECT url_id FROM cc WHERE id = '$id'";
    $result = $db->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        $url_id = $row['url_id'];

        // Проверяем, существует ли уже такая комбинация url_id, field_key и assign_as
        $checkQuery = "SELECT * FROM field_mappings WHERE url_id = '$url_id' AND field_key = '$field' AND assign_as = '$assign_as'";
        $checkResult = $db->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            echo "A mapping for field '$field' with assignment '$assign_as' already exists for URL ID '$url_id'.";
        } else {
            // Добавляем новую запись, так как комбинация field_key и assign_as уникальна
            $insertMappingQuery = "INSERT INTO field_mappings (url_id, field_key, assign_as) VALUES ('$url_id', '$field', '$assign_as')";
            if (!$db->query($insertMappingQuery)) {
                echo "Error inserting new mapping: " . $db->error;
            } else {
                echo "New mapping for field '$field' has been added with assignment '$assign_as' for URL ID '$url_id'.";
            }
        }
    } else {
        echo "No data found for this ID.";
    }
}


 else {
    echo "No data found for this ID.";
}

$db->close();

header('Location: details.php?id=' . $id . '&url_id=' . urlencode($url_id));
exit();
?>