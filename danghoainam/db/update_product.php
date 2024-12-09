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
    $productId = isset($_POST['product_id']) ? $_POST['product_id'] : '';
    $productName = isset($_POST['product_name']) ? $_POST['product_name'] : '';
    $sku = isset($_POST['sku']) ? $_POST['sku'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : '';
    $categories = isset($_POST['categories']) ? json_decode($_POST['categories']) : [];
    $tags = isset($_POST['tags']) ? json_decode($_POST['tags']) : [];

    $errors = [];
    if (empty($productName)) {
        $errors['name_error'] = 'Product Name is required';
    }

    if (empty($sku)) {
        $errors['sku_error'] = 'SKU is required';
    }

    if (!empty($errors)) {
        echo json_encode($errors);
        exit;
    }

    // Check SKU
    $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
    $stmt->bind_param("si", $sku, $productId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['sku_error' => 'SKU already exists']);
        exit;
    }
    $stmt->close();

    // Check product name
    $stmt = $conn->prepare("SELECT id FROM products WHERE product_name = ? AND id != ?");
    $stmt->bind_param("si", $productName, $productId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['name_error' => 'Product Name already exists']);
        exit;
    }
    $stmt->close();
    
    $uploadDir = '../views/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $featureImageSql = '';
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
        $featureImageSql = ", feature_image = '$featureImagePath'";
    }

    $newGalleryPaths = [];
    if (isset($_FILES['gallery'])) {
        foreach ($_FILES['gallery']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['gallery']['error'][$index] === UPLOAD_ERR_OK) {
                $fileHash = getFileHash($tmpName);
                $existingFile = findExistingFile($uploadDir, $fileHash);
                
                if ($existingFile) {
                    $newGalleryPaths[] = '/views/uploads/' . $existingFile;
                } else {
                    $galleryImageName = uniqid() . '_' . basename($_FILES['gallery']['name'][$index]);
                    $galleryPath = $uploadDir . $galleryImageName;
                    move_uploaded_file($tmpName, $galleryPath);
                    $newGalleryPaths[] = "/views/uploads/$galleryImageName";
                }
            }
        }
    }

    // Fetch current gallery images
    $stmt = $conn->prepare("SELECT gallery FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentProduct = $result->fetch_assoc();
    $currentGallery = $currentProduct['gallery'] ? explode(',', $currentProduct['gallery']) : [];

    // Merge current and new gallery images
  
    $allGalleryPaths = array_merge($currentGallery, $newGalleryPaths);
    $allGalleryPaths = array_unique($allGalleryPaths); // Remove duplicates
    $galleryPathsStr = implode(',', $allGalleryPaths);
    $gallerySql = ", gallery = '$galleryPathsStr'";

    $conn->begin_transaction();

    try {
        // Update product details
        $query = "UPDATE products SET 
                  product_name = ?, 
                  sku = ?, 
                  price = ?
                  $featureImageSql
                  $gallerySql
                  WHERE id = ?";

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception('Unable to prepare statement.');
        }

        $stmt->bind_param("sssi", $productName, $sku, $price, $productId);

        if (!$stmt->execute()) {
            throw new Exception('Error updating product in the database.');
        }

        $stmt->close();

        // Update categories
        $stmt = $conn->prepare("DELETE FROM product_category WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

        foreach ($categories as $categoryId) {
            $stmt = $conn->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $productId, $categoryId);
            $stmt->execute();
            $stmt->close();
        }

        // Update tags
        $stmt = $conn->prepare("DELETE FROM product_tag WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

        foreach ($tags as $tagId) {
            $stmt = $conn->prepare("INSERT INTO product_tag (product_id, tag_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $productId, $tagId);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        // Fetch the updated product data
        $stmt = $conn->prepare("
            SELECT p.*, 
                   GROUP_CONCAT(DISTINCT c.name) AS categories,
                   GROUP_CONCAT(DISTINCT t.name) AS tags
            FROM products p
            LEFT JOIN product_category pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            LEFT JOIN product_tag pt ON p.id = pt.product_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.id = ?
            GROUP BY p.id
        ");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $updatedProduct = $result->fetch_assoc();

        // Format the date
        $updatedProduct['created_at'] = date('m/d/Y', strtotime($updatedProduct['created_at']));

        // Format categories and tags as arrays
        $updatedProduct['categories'] = $updatedProduct['categories'] ? explode(',', $updatedProduct['categories']) : [];
        $updatedProduct['tags'] = $updatedProduct['tags'] ? explode(',', $updatedProduct['tags']) : [];

        $responseProduct = [
            'id' => $updatedProduct['id'],
            'created_at' => $updatedProduct['created_at'],
            'product_name' => $updatedProduct['product_name'],
            'sku' => $updatedProduct['sku'],
            'price' => $updatedProduct['price'],
            'feature_image' => $updatedProduct['feature_image'],
            'gallery' => $updatedProduct['gallery'],
            'categories' => $updatedProduct['categories'],
            'tags' => $updatedProduct['tags']
        ];

        echo json_encode(['success' => 'Product updated successfully', 'product' => $responseProduct]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }

} else {
    echo json_encode(['error' => 'Unsupported method.']);
}
?>