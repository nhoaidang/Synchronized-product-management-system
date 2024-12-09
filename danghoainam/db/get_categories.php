<?php
include 'connect.php'; 

header('Content-Type: application/json');


$sql = "SELECT id, name FROM categories"; 
$result = mysqli_query($conn, $sql);

$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

echo json_encode($categories);

mysqli_close($conn);
?>