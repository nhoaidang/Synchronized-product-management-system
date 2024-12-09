<?php
include 'connect.php'; 

header('Content-Type: application/json');


$sql = "SELECT id, name FROM tags"; 
$result = mysqli_query($conn, $sql);

$tags = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tags[] = $row;
}

echo json_encode($tags);

mysqli_close($conn);
?>