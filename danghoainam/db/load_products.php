<?php
include '/xampp/htdocs/danghoainam/db/connect.php';

$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . date('m/d/Y', strtotime($row['created_at'])) . "</td>
                <td>" . $row['product_name'] . "</td>
                <td>" . $row['sku'] . "</td>
                <td>" . $row['price'] . "</td>
                <td><img src='" . $row['feature_image'] . "' width='50'></td>
                <td><img src='" . $row['gallery'] . "' width='50'></td>
                <td>" . $row['categories'] . "</td>
                <td>" . $row['tags'] . "</td>
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
}
?>