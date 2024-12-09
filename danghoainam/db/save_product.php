<?php
require_once 'connect.php';

function getFileHash($filePath) {
    return hash_file('md5', $filePath);
}

function findExistingFile($uploadDir, $fileHash) {
    $files = glob($uploadDir . '*');
    foreach ($files as $file) {
        if (is_file($file) && getFileHash($file) === $fileHash) {
            return basename($file);
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = isset($_POST['product_name']) ? $_POST['product_name'] : '';
    $sku = isset($_POST['sku']) ? $_POST['sku'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    $categories = isset($_POST['categories']) ? array_filter(explode(',', $_POST['categories'])) : [];
    $tags = isset($_POST['tags']) ? array_filter(explode(',', $_POST['tags'])) : [];

    if (empty($productName) || empty($sku)) {
        echo json_encode(['error' => 'Product name and SKU are required.']);
        exit;
    }

    
    $checkQuery = "SELECT * FROM products WHERE sku = ? OR product_name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $sku, $productName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    $errorMessages = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['sku'] === $sku) {
                $errorMessages['sku'] = 'SKU already exists';
            }
            if ($row['product_name'] === $productName) {
                $errorMessages['product_name'] = 'Product name already exists';
            }
        }
    }

    if (!empty($errorMessages)) {
        echo json_encode(['error' => true, 'messages' => $errorMessages]);
        exit;
    }

    $checkStmt->close();

    $uploadDir = '../views/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $featureImagePath = '';
    if (isset($_FILES['feature_image']) && $_FILES['feature_image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['feature_image']['tmp_name'];
        $fileHash = getFileHash($tmpName);
        $existingFile = findExistingFile($uploadDir, $fileHash);
        
        if ($existingFile) {
            $featureImagePath = '/views/uploads/' . $existingFile;
        } else {
            $featureImageName = uniqid() . '_' . basename($_FILES['feature_image']['name']);
            $featureImagePath = $uploadDir . $featureImageName;
            move_uploaded_file($tmpName, $featureImagePath);
            $featureImagePath = '/views/uploads/' . $featureImageName;
        }
    }

    $galleryPaths = [];
    if (isset($_FILES['gallery'])) {
        foreach ($_FILES['gallery']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['gallery']['error'][$index] === UPLOAD_ERR_OK) {
                $fileHash = getFileHash($tmpName);
                $existingFile = findExistingFile($uploadDir, $fileHash);
                
                if ($existingFile) {
                    $galleryPaths[] = '/views/uploads/' . $existingFile;
                } else {
                    $galleryImageName = uniqid() . '_' . basename($_FILES['gallery']['name'][$index]);
                    $galleryImagePath = $uploadDir . $galleryImageName;
                    move_uploaded_file($tmpName, $galleryImagePath);
                    $galleryPaths[] = '/views/uploads/' . $galleryImageName;
                }
            }
        }
    }

    $conn->begin_transaction();

    try {
        // Add product to database
        $query = "INSERT INTO products (product_name, sku, price, feature_image, gallery) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception('Failed to prepare SQL statement.');
        }

        if (empty($price)) {
            $price = 0.00;
        }

        $galleryPathsStr = implode(',', $galleryPaths);
        $stmt->bind_param("ssdss", $productName, $sku, $price, $featureImagePath, $galleryPathsStr);

        if (!$stmt->execute()) {
            throw new Exception('Error adding product to the database.');
        }

        $productId = $stmt->insert_id;

        // Insert categories if not empty
        if (!empty($categories)) {
            $categoryQuery = "INSERT IGNORE INTO categories (name) VALUES (?)";
            $categoryStmt = $conn->prepare($categoryQuery);
            $productCategoryQuery = "INSERT INTO product_category (product_id, category_id) VALUES (?, ?)";
            $productCategoryStmt = $conn->prepare($productCategoryQuery);

            foreach ($categories as $category) {
                if (!empty(trim($category))) {
                    $categoryStmt->bind_param("s", $category);
                    $categoryStmt->execute();
                    $categoryId = $categoryStmt->insert_id ?: $conn->query("SELECT id FROM categories WHERE name = '$category'")->fetch_object()->id;
                    $productCategoryStmt->bind_param("ii", $productId, $categoryId);
                    $productCategoryStmt->execute();
                }
            }
        }

        // Insert tags if not empty
        if (!empty($tags)) {
            $tagQuery = "INSERT IGNORE INTO tags (name) VALUES (?)";
            $tagStmt = $conn->prepare($tagQuery);
            $productTagQuery = "INSERT INTO product_tag (product_id, tag_id) VALUES (?, ?)";
            $productTagStmt = $conn->prepare($productTagQuery);

            foreach ($tags as $tag) {
                if (!empty(trim($tag))) {
                    $tagStmt->bind_param("s", $tag);
                    $tagStmt->execute();
                    $tagId = $tagStmt->insert_id ?: $conn->query("SELECT id FROM tags WHERE name = '$tag'")->fetch_object()->id;
                    $productTagStmt->bind_param("ii", $productId, $tagId);
                    $productTagStmt->execute();
                }
            }
        }

        $conn->commit();

        $response = [
            'id' => $productId,
            'feature_image' => $featureImagePath,
            'gallery' => $galleryPaths
        ];
        echo json_encode($response);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }

    $stmt->close();
    if (isset($categoryStmt)) $categoryStmt->close();
    if (isset($productCategoryStmt)) $productCategoryStmt->close();
    if (isset($tagStmt)) $tagStmt->close();
    if (isset($productTagStmt)) $productTagStmt->close();

} else {
    echo json_encode(['error' => 'Unsupported request method.']);
}
?>
