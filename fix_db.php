<?php
require_once __DIR__ . '/config/db.php';

$conn = connect();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "
CREATE TABLE IF NOT EXISTS `supplier_items` (
  `supplier_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  PRIMARY KEY (`supplier_item_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`supplier_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (mysqli_query($conn, $sql)) {
    echo "Table supplier_items created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
