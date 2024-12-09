<?php
require_once 'connect.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 5; 
$offset = ($page - 1) * $perPage;

$countQuery = "SELECT COUNT(*) as total FROM products";
$countResult = $conn->query($countQuery);
$totalProducts = $countResult->fetch_assoc()['total'];

$query = "SELECT p.*, 
          GROUP_CONCAT(DISTINCT c.name) as categories,
          GROUP_CONCAT(DISTINCT t.name) as tags
          FROM products p
          LEFT JOIN product_category pc ON p.id = pc.product_id
          LEFT JOIN categories c ON pc.category_id = c.id
          LEFT JOIN product_tag pt ON p.id = pt.product_id
          LEFT JOIN tags t ON pt.tag_id = t.id
          GROUP BY p.id
          ORDER BY p.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['categories'] = $row['categories'] ? explode(',', $row['categories']) : [];
    $row['tags'] = $row['tags'] ? explode(',', $row['tags']) : [];
    $row['feature_image'] = $row['feature_image'] ? '/danghoainam' . $row['feature_image'] : '';
    $row['gallery'] = $row['gallery'] ? implode(',', array_map(function($img) { return '/danghoainam' . $img; }, explode(',', $row['gallery']))) : '';
    $products[] = $row;
}

$totalPages = ceil($totalProducts / $perPage);

echo json_encode([
    'products' => $products,
    'totalProducts' => $totalProducts,
    'currentPage' => $page,
    'perPage' => $perPage,
    'totalPages' => $totalPages
]);

$stmt->close();
$conn->close();
?>