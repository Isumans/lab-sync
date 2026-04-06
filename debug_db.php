<?php
require_once 'c:/xampp/htdocs/lab_sync/config/db.php';
$db = connect();

if (!$db) {
    file_put_contents('debug_schema.txt', "Connection failed: " . mysqli_connect_error());
    exit;
}

$table = 'partner_labs';
$sql = "SHOW COLUMNS FROM $table";
$result = $db->query($sql);

$columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    file_put_contents('c:/xampp/htdocs/lab_sync/debug_schema.txt', print_r($columns, true));
} else {
    file_put_contents('c:/xampp/htdocs/lab_sync/debug_schema.txt', "Error showing columns: " . $db->error);
}
?>
