<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

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

$json = file_get_contents('php://input');
$products = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$results = [];
$upload_dir = __DIR__ . '/../views/uploads/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Cache file path
$cacheFile = __DIR__ . '/../cache/categories_tags_cache.json';

// Load categories and tags from cache or database
$cacheData = [];
if (file_exists($cacheFile)) {
    // Load from cache
    $cacheData = json_decode(file_get_contents($cacheFile), true);
} else {
    // Fetch from database
    $existingTags = [];
    $stmt = $pdo->query("SELECT id, name FROM tags");
    while ($row = $stmt->fetch()) {
        $existingTags[$row['name']] = $row['id'];
    }

    $existingCategories = [];
    $stmt = $pdo->query("SELECT id, name FROM categories");
    while ($row = $stmt->fetch()) {
        $existingCategories[$row['name']] = $row['id'];
    }

    $cacheData = [
        'tags' => $existingTags,
        'categories' => $existingCategories,
    ];

    // Save to cache
    file_put_contents($cacheFile, json_encode($cacheData));
}


$existingTags = $cacheData['tags'];
$existingCategories = $cacheData['categories'];

function getOrCreateTag($pdo, &$existingTags, $tagName) {
    if (!isset($existingTags[$tagName])) {
        $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
        $stmt->execute([$tagName]);
        $existingTags[$tagName] = $pdo->lastInsertId();
        updateCache();
    }
    return $existingTags[$tagName];
}

function getOrCreateCategory($pdo, &$existingCategories, $categoryName) {
    if (!isset($existingCategories[$categoryName])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$categoryName]);
        $existingCategories[$categoryName] = $pdo->lastInsertId();
        updateCache();
    }
    return $existingCategories[$categoryName];
}

function updateCache() {
    global $pdo, $cacheFile;
    
    // Fetch updated data from the database
    $existingTags = [];
    $stmt = $pdo->query("SELECT id, name FROM tags");
    while ($row = $stmt->fetch()) {
        $existingTags[$row['name']] = $row['id'];
    }

    $existingCategories = [];
    $stmt = $pdo->query("SELECT id, name FROM categories");
    while ($row = $stmt->fetch()) {
        $existingCategories[$row['name']] = $row['id'];
    }

    // Save updated data to cache
    $cacheData = [
        'tags' => $existingTags,
        'categories' => $existingCategories,
    ];
    file_put_contents($cacheFile, json_encode($cacheData));
}


function getUniqueFilename($url) {
    $extension = pathinfo($url, PATHINFO_EXTENSION);
    $content_hash = md5($url); 
    return $content_hash . '.' . $extension;
}

function downloadImage($url, $upload_dir) {
    $unique_filename = getUniqueFilename($url);
    $filepath = $upload_dir . $unique_filename;
    $relative_path = '/views/uploads/' . $unique_filename;

    if (file_exists($filepath)) {
        return $relative_path;
    }

    $image_data = file_get_contents($url);
    if ($image_data === false) {
        return false;
    }

    if (file_put_contents($filepath, $image_data) === false) {
        return false;
    }

    return $relative_path;
}

$pdo->beginTransaction();

try {
    $existingProducts = [];
    $stmt = $pdo->query("SELECT id, sku FROM products");
    while ($row = $stmt->fetch()) {
        $existingProducts[$row['sku']] = $row['id'];
    }

    $productInsertStmt = $pdo->prepare("INSERT INTO products (sku, product_name, price, feature_image, gallery) VALUES (?, ?, ?, ?, ?)");
    $productUpdateStmt = $pdo->prepare("UPDATE products SET product_name = ?, price = ?, feature_image = ?, gallery = ? WHERE sku = ?");
    $tagInsertStmt = $pdo->prepare("INSERT INTO product_tag (product_id, tag_id) VALUES (?, ?)");
    $categoryInsertStmt = $pdo->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");

    foreach ($products as $data) {
        if (empty($data['sku']) || empty($data['product_name']) || empty($data['price'])) {
            $results[] = ['error' => 'Missing required fields (sku, product_name, price)', 'sku' => $data['sku'] ?? 'unknown'];
            continue;
        }

        $tagIds = [];
        $categoryIds = [];

        $tags = array_map('trim', explode(',', $data['tags']));
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $tagIds[] = getOrCreateTag($pdo, $existingTags, $tag);
            }
        }

        $categories = array_map('trim', explode(',', $data['categories']));
        foreach ($categories as $category) {
            if (!empty($category)) {
                $categoryIds[] = getOrCreateCategory($pdo, $existingCategories, $category);
            }
        }

        $feature_image_path = downloadImage($data['feature_image'], $upload_dir);
        if ($feature_image_path === false) {
            throw new Exception("Failed to download feature image");
        }

        $gallery_paths = [];
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $gallery_image) {
                $gallery_image_path = downloadImage($gallery_image, $upload_dir);
                if ($gallery_image_path !== false) {
                    $gallery_paths[] = $gallery_image_path;
                }
            }
        }

        $gallery_paths = array_unique($gallery_paths);
        if (!in_array($feature_image_path, $gallery_paths)) {
            array_unshift($gallery_paths, $feature_image_path);
        }
        $gallery_string = implode(',', $gallery_paths);

        if (isset($existingProducts[$data['sku']])) {
            $productUpdateStmt->execute([
                $data['product_name'],
                $data['price'],
                $feature_image_path,
                $gallery_string,
                $data['sku']
            ]);
            $productId = $existingProducts[$data['sku']];
            $action = 'updated';
        } else {
            $productInsertStmt->execute([
                $data['sku'],
                $data['product_name'],
                $data['price'],
                $feature_image_path,
                $gallery_string
            ]);
            $productId = $pdo->lastInsertId();
            $existingProducts[$data['sku']] = $productId;
            $action = 'created';
        }

        // Clear existing tags and categories
        $pdo->exec("DELETE FROM product_tag WHERE product_id = $productId");
        $pdo->exec("DELETE FROM product_category WHERE product_id = $productId");

        // Insert new tags and categories
        foreach ($tagIds as $tagId) {
            $tagInsertStmt->execute([$productId, $tagId]);
        }
        foreach ($categoryIds as $categoryId) {
            $categoryInsertStmt->execute([$productId, $categoryId]);
        }

        $results[] = ['message' => "Product $action successfully", 'sku' => $data['sku']];
    }

    $pdo->commit();
} catch (\Exception $e) {
    $pdo->rollBack();
    $results[] = ['error' => 'Error processing products: ' . $e->getMessage()];
}

echo json_encode($results);
?>