<?php
include 'connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sortDate = isset($_GET['sort_date']) ? $_GET['sort_date'] : '';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$tags = isset($_GET['tags']) ? $_GET['tags'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$priceFrom = isset($_GET['price_from']) ? $_GET['price_from'] : '';
$priceTo = isset($_GET['price_to']) ? $_GET['price_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : ''; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT p.* FROM products p";
$params = array();


$sql .= " LEFT JOIN product_category pc ON p.id = pc.product_id";
$sql .= " LEFT JOIN product_tag pt ON p.id = pt.product_id";

$sql .= " WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (p.product_name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $categoryArray = explode(',', $category);
    $placeholders = implode(',', array_fill(0, count($categoryArray), '?'));
    
   
    $catSql = "SELECT id FROM categories WHERE name IN ($placeholders)";
    $catStmt = mysqli_prepare($conn, $catSql);
    mysqli_stmt_bind_param($catStmt, str_repeat('s', count($categoryArray)), ...$categoryArray);
    mysqli_stmt_execute($catStmt);
    $catResult = mysqli_stmt_get_result($catStmt);

   
    $categoryIds = [];
    while ($catRow = mysqli_fetch_assoc($catResult)) {
        $categoryIds[] = $catRow['id'];
    }

  
    if (!empty($categoryIds)) {
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql .= " AND pc.category_id IN ($placeholders)";
        $params = array_merge($params, $categoryIds);
    }
}


if (!empty($tags)) {
    $tagsArray = explode(',', $tags);
    $placeholders = implode(',', array_fill(0, count($tagsArray), '?'));
    
   
    $tagSql = "SELECT id FROM tags WHERE name IN ($placeholders)";
    $tagStmt = mysqli_prepare($conn, $tagSql);
    mysqli_stmt_bind_param($tagStmt, str_repeat('s', count($tagsArray)), ...$tagsArray);
    mysqli_stmt_execute($tagStmt);
    $tagResult = mysqli_stmt_get_result($tagStmt);

  
    $tagIds = [];
    while ($tagRow = mysqli_fetch_assoc($tagResult)) {
        $tagIds[] = $tagRow['id'];
    }

    
    if (!empty($tagIds)) {
        $sql .= " AND p.id IN (SELECT DISTINCT product_id FROM product_tag WHERE tag_id IN (" . implode(',', array_fill(0, count($tagIds), '?')) . "))";
        $params = array_merge($params, $tagIds);
    }
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND p.created_at BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

if (!empty($priceFrom) || $priceFrom === '0') {
    $sql .= " AND p.price >= ?";
    $params[] = $priceFrom;
}
if (!empty($priceTo) || $priceTo === '0') {
    $sql .= " AND p.price <= ?";
    $params[] = $priceTo;
}

$sql .= " GROUP BY p.id";

if ($sortDate == "1") {
    $sql .= " ORDER BY p.created_at";
} elseif ($sortDate == "2") {
    $sql .= " ORDER BY p.created_at";
} elseif ($sortDate == "3") {
    $sql .= " ORDER BY p.product_name";
} elseif ($sortDate == "4") {
    $sql .= " ORDER BY p.price";
}

if ($sortOrder == "2") {
    $sql .= " DESC";
} elseif ($sortOrder == "1") {
    $sql .= " ASC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = mysqli_prepare($conn, $sql);
if ($stmt === false) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

$totalResult = mysqli_query($conn, "SELECT FOUND_ROWS() as total");
$total = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($total / $limit);

$products = '';
$uploadsPath = "http://localhost/danghoainam/";
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Fetch categories for this product
        $catSql = "SELECT c.name FROM categories c JOIN product_category pc ON c.id = pc.category_id WHERE pc.product_id = ?";
        $catStmt = mysqli_prepare($conn, $catSql);
        mysqli_stmt_bind_param($catStmt, 'i', $row['id']);
        mysqli_stmt_execute($catStmt);
        $catResult = mysqli_stmt_get_result($catStmt);
        $categories = [];
        while ($catRow = mysqli_fetch_assoc($catResult)) {
            $categories[] = $catRow['name'];
        }

        // Fetch tags for this product
        $tagSql = "SELECT t.name FROM tags t JOIN product_tag pt ON t.id = pt.tag_id WHERE pt.product_id = ?";
        $tagStmt = mysqli_prepare($conn, $tagSql);
        mysqli_stmt_bind_param($tagStmt, 'i', $row['id']);
        mysqli_stmt_execute($tagStmt);
        $tagResult = mysqli_stmt_get_result($tagStmt);
        $tags = [];
        while ($tagRow = mysqli_fetch_assoc($tagResult)) {
            $tags[] = $tagRow['name'];
        }

        $products .= "<tr>
            <td>" . date('m/d/Y', strtotime($row['created_at'])) . "</td>
            <td>" . htmlspecialchars($row['product_name']) . "</td>
            <td>" . htmlspecialchars($row['sku']) . "</td>
            <td>$" . number_format($row['price'], 2) . "</td>
            <td><img src='" . $uploadsPath . htmlspecialchars($row['feature_image']) . "' width='50'></td>
            <td>";
            $galleryImages = explode(',', $row['gallery']);
            foreach ($galleryImages as $image) {
                $image = trim($image);
                if (!empty($image)) {
                    $products .= "<img src='" . (filter_var($image, FILTER_VALIDATE_URL) ? htmlspecialchars($image) : $uploadsPath . htmlspecialchars($image)) . "' width='50'>";
                }
            }
        $products .= "</td>
            <td>" . htmlspecialchars(implode(', ', $categories)) . "</td>
            <td>" . htmlspecialchars(implode(', ', $tags)) . "</td>
            <td>
                <button class='ui icon button edit-btn' data-id='" . $row['id'] . "'>
                    <i class='edit icon'></i>
                </button>
                <button class='ui icon button delete-btn' data-id='" . $row['id'] . "'>
                    <i class='trash icon'></i>
                </button>
            </td>
        </tr>";
    }
} else {
    $products = "<tr><td colspan='9'>No products found</td></tr>";
}

echo json_encode([
    'products' => $products,
    'currentPage' => $page,
    'totalPages' => $totalPages
]);
?>