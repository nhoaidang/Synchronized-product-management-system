<?php
$host = 'localhost';
$db   = 'danghoainam_sql';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$query = "
    SELECT 
        p.id, p.product_name, p.sku, p.price, p.feature_image, p.gallery, p.created_at,
        GROUP_CONCAT(DISTINCT t.name) AS tags,
        GROUP_CONCAT(DISTINCT c.name) AS categories
    FROM 
        products p
    LEFT JOIN 
        product_tag pt ON p.id = pt.product_id
    LEFT JOIN 
        tags t ON pt.tag_id = t.id
    LEFT JOIN 
        product_category pc ON p.id = pc.product_id
    LEFT JOIN 
        categories c ON pc.category_id = c.id
    GROUP BY 
        p.id
    ORDER BY 
        p.created_at DESC
";

$stmt = $pdo->query($query);
$products = $stmt->fetchAll();

echo json_encode($products);
?>