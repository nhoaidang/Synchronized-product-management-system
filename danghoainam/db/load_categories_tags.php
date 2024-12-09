<?php
require 'connect.php';

$categories_query = "SELECT id, name FROM categories";
$categories_result = mysqli_query($conn, $categories_query);

$categories_options = '';
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories_options .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

$tags_query = "SELECT id, name FROM tags";
$tags_result = mysqli_query($conn, $tags_query);

$tags_options = '';
while ($row = mysqli_fetch_assoc($tags_result)) {
    $tags_options .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

echo json_encode([
    'categories' => $categories_options,
    'tags' => $tags_options
]);
?>