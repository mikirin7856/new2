<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header('Location: ../login.php');
    exit();
}

require 'config.php';

$field_key = $_POST['field_key'];
$url_id = $_POST['url_id'];
$id = $_POST['id'];
$block_status = $_POST['block_status'];

$query = "UPDATE field_mappings SET block = '$block_status' WHERE field_key = '$field_key' AND url_id = '$url_id'";

if ($db->query($query)) {
    header('Location: details.php?id=' . $id . '&url_id=' . urlencode($url_id));
} else {
    echo "Error updating record: " . $db->error;
}

$db->close();
?>
