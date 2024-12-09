<?php
require_once 'connect.php';

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    
    // Fetch product details
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Fetch categories
        $categoryQuery = "SELECT c.id, c.name FROM categories c
                          JOIN product_category pc ON c.id = pc.category_id
                          WHERE pc.product_id = ?";
        $categoryStmt = $conn->prepare($categoryQuery);
        $categoryStmt->bind_param("i", $productId);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        $categories = [];
        $categoryIds = [];
        $categoryNames = [];
        while ($category = $categoryResult->fetch_assoc()) {
            $categories[] = [
                'id' => $category['id'],
                'name' => $category['name']
            ];
            $categoryIds[] = $category['id'];
            $categoryNames[] = $category['name'];
        }
        $product['categories'] = $categories;
        $product['category_ids'] = $categoryIds;
        $product['category_names'] = $categoryNames;
        
        // Fetch tags
        $tagQuery = "SELECT t.id, t.name FROM tags t
                     JOIN product_tag pt ON t.id = pt.tag_id
                     WHERE pt.product_id = ?";
        $tagStmt = $conn->prepare($tagQuery);
        $tagStmt->bind_param("i", $productId);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        
        $tags = [];
        $tagIds = [];
        $tagNames = [];
        while ($tag = $tagResult->fetch_assoc()) {
            $tags[] = [
                'id' => $tag['id'],
                'name' => $tag['name']
            ];
            $tagIds[] = $tag['id'];
            $tagNames[] = $tag['name'];
        }
        $product['tags'] = $tags;
        $product['tag_ids'] = $tagIds;
        $product['tag_names'] = $tagNames;
        
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    
    $stmt->close();
    $categoryStmt->close();
    $tagStmt->close();
} else {
    echo json_encode(['error' => 'No product ID provided']);
}

$conn->close();
?>