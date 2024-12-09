<?php

include 'connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $propertyName = isset($_POST['propertyName']) ? $_POST['propertyName'] : '';
    $propertyType = isset($_POST['propertyType']) ? $_POST['propertyType'] : '';

   
    if (!empty($propertyName) && !empty($propertyType)) {
       
        if ($propertyType === 'category') {
          
            $sql = "INSERT INTO categories (name) VALUES (?)";
        } elseif ($propertyType === 'tag') {
           
            $sql = "INSERT INTO tags (name) VALUES (?)";
        } else {
            echo json_encode(['error' => 'Invalid property type.']);
            exit();
        }

       
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $propertyName);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => 'Property saved successfully.',
                'propertyType' => $propertyType
            ]);
        } else {
            echo json_encode(['error' => 'Please enter another property name.']);
        }

        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Please fill in all fields.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}

$conn->close();
?>