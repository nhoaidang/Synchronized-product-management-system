<?php
include 'connect.php';

$sql = "DELETE FROM products"; 
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'All products deleted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting products: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>