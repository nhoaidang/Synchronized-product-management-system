
<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Dang Hoai Nam</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
     
     <style>
.ui.container{
     margin-top: 30px;
}
.ui .input {
     width: 100%;
}
.ui.grid{
     margin-bottom: 5px;
} 

.ui .form select {
     width: auto;
}
.ui .form input::placeholder{
     color: black;
}
.ui.celled.table i{
margin-left: 20px;
}
.ui.celled.table .button{
     color: black;
     background-color: #fff;
     width: 20px;
     padding: 0px;
}
.box_page{
     display: flex;
     justify-content: center;
}
.inline .fields{
  width: 100%;
}

th {
    position: relative;
}


th .trash.icon {
    display: none;
    position: absolute;
    right: 30px; 
    top: 45%; 
    transform: translateY(-50%); 
  
}


th:hover .trash.icon {
    display: inline-block;
}

th {
    padding-right: 30px; 
}
.action-column {
    width: 120px; 
    padding-right: 40px; 
}
.ui.dropdown {
    width: 100%;
}

.ui.dropdown .menu {
    max-height: 200px;
    overflow-y: auto;
}
.auto-width-dropdown {
    display: inline-block;
    width: auto !important; 
    min-width: 0 !important; 
}
.fixed-width-dropdown {
    width: 160px !important; 
}
ui.default.dropdown:not(.button)>.text, .ui.dropdown:not(.button)>.default.text {
    color: black;
}
.horizontal-pages {
    display: flex;
    flex-direction: row;
    overflow-x: auto;
    white-space: nowrap;
}

.horizontal-pages .item {
    display: inline-block;
    margin: 0 2px;
}
#notificationMessage ,
#updateMessage,
#propertyMessage
{
  position: fixed; 
  top: 4%; 
  left: 50%; 
  transform: translateX(-50%); 
  z-index: 1000; 
  width: 100%; 
  max-width: 500px; 
  margin: 10px 0; 
}
#updateMessage1{
position: fixed; 
  top: 4%; 
  left: 50%; 
  transform: translateX(-50%); 
  z-index: 1000; 
  width: 100%; 
  max-width: 500px; 
  margin: 10px 0; 
}

     </style>
</head>
<body>
     <div class="ui container">
        
     <div class="ui segment">
        <div class="ui grid">
            <div class="eight wide column">
                <button class="ui blue button" id="add-product-btn">Add product</button>
                <button class="ui button" id="add-property-btn">Add property</button>
                <button class="ui button" id="syncFromVillaTheme">Sync from VillaTheme</button>
            </div>
            <div class="eight wide column">
                <div class="ui input">
                    <input type="text" id="search-input" placeholder="Search product...">
                </div>
            </div>
        </div>
        <!--  -->
<div class="ui form">
    <div class="fields">
        <div class="field">
            <select class="ui dropdown auto-width-dropdown" id="sort-date">
                <option value="1">Date</option>
                <option value="2">Sku</option>
                <option value="3">Name</option>
                <option value="4">Price</option>
            </select>
        </div>
        <div class="field">
            <select class="ui dropdown auto-width-dropdown" id="sort-order">
                <option value="1">ASC</option>
                <option value="2">DESC</option>
            </select>
        </div>
        <div class="field">
            <select class="ui fluid multiple search dropdown fixed-width-dropdown" multiple="" id="category-select">
                <option value="">Select Categories</option>
            </select>
        </div>
        <div class="field">
            <select class="ui fluid multiple search dropdown fixed-width-dropdown" multiple="" id="tag-select">
                <option value="">Select Tags</option>
               
            </select>
        </div>
        <div class="field">
            <input type="date" id="start-date" placeholder="mm/dd/yyyy">
        </div>
        <div class="field">
            <input type="date" id="end-date" placeholder="mm/dd/yyyy">
        </div>
        <div class="field">
            <input type="text" id="price-from" placeholder="Price from">
        </div>
        <div class="field">
            <input type="text" id="price-to" placeholder="Price to">
        </div>
        <div class="field">
            <button class="ui button" id="filter-button">Filter</button>
        </div>
    </div>
</div>
<!--  -->
</div>
     <!-- Product Table -->
<?php

include '/xampp/htdocs/danghoainam/db/connect.php';


$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;


$sql = "SELECT * FROM products LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>





<div  id="notificationMessage" class="ui  hidden icon green message">
  <i class="chevron circle down icon"></i>
  <div class="content">
     <p></p>
  </div>
</div>



<div  id="updateMessage"  class="ui hidden icon green message">
  <i class="chevron circle down icon"></i>
  <div class="content">
     <p></p>
  </div>
</div>

<div  id="updateMessage1"  class="ui hidden icon blue message">
  <i class="recycle icon"></i>
  <div class="content">
     <p></p>
  </div>
</div>


  <div  id="propertyMessage"  class="ui hidden icon green message">
  <i class="chevron circle down icon"></i>
  <div class="content">
     <p></p>
  </div>
</div>



<table class="ui celled table">
    <thead>
        <tr data-id="<?php echo $product['id']; ?>">
            <th>Date</th>
            <th>Product name</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Feature Image</th>
            <th>Gallery</th>
            <th>Categories</th>
            <th>Tags</th>
            <th class="action-column">
                Action
                <i class="trash icon" id="trash-icon"></i>
            </th>
        </tr>
    </thead>
    <tbody id="product-table-body">
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $galleryImages = explode(',', $row['gallery']); 

            
            $featureImagePath = 'http://localhost/danghoainam' . $row['feature_image']; 
            echo "<tr>
                <td>" . date('m/d/Y', strtotime($row['created_at'])) . "</td>
                <td>" . htmlspecialchars($row['product_name']) . "</td>
                <td>" . htmlspecialchars($row['sku']) . "</td>
                <td>$" . number_format($row['price'], 2) . "</td>
                <td><img src='" . $featureImagePath . "' width='50'></td>
                <td>";
        
            foreach ($galleryImages as $galleryImage) {
                
                $galleryImagePath = 'http://localhost/danghoainam' . trim($galleryImage); 
                echo "<img src='" . $galleryImagePath . "' width='50' style='margin-right: 5px;'>";
            }

            echo "</td>
                <td>" . ($row['categories'] ? $row['categories'] : ' ') . "</td> <!-- Hiển thị chuỗi rỗng nếu không có categories -->
                <td>" . ($row['tags'] ? $row['tags'] : ' ') . "</td> <!-- Hiển thị chuỗi rỗng nếu không có tags -->
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
        echo "<tr><td colspan='9'>No products found.</td></tr>";
    }
    ?>
    </tbody>
</table>


<?php
// Đếm tổng số sản phẩm để tính tổng số trang
$total_query = "SELECT COUNT(*) as total FROM products";
$total_result = mysqli_query($conn, $total_query);
$total_products = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_products / $limit);
?>

<!-- Hiển thị phân trang -->
<div class="box_page">
    <div class="ui pagination menu">
        <a class="icon item" id="prev-page">
            <i class="left arrow icon"></i>
        </a>
        <div id="page-numbers" class="horizontal-pages">
            <!-- Số trang page  -->
        </div>
        <a class="icon item" id="next-page">
            <i class="right arrow icon"></i>
        </a>
    </div>
</div>
    


    <!-- Add Product Modal -->
    <div class="ui modal" id="addProductModal">
    <div class="header">Add New Product
        <div id="successMessage" style="color: green; margin-top: 10px;"></div>
    </div>
   
    <div class="content">
        <form class="ui form" id="addProductForm">
            <div class="field">
                <label>Product Name</label>
                <input type="text" name="product_name" id="productName" placeholder="Product Name" required>
                <div class="error-message" id="errorProductName" style="color: red;"></div>
            </div>
            <div class="field">
                <label>SKU</label>
                <input type="text" name="sku" id="productSKU" placeholder="SKU" required>
                <div class="error-message" id="errorSKU" style="color: red;"></div>
            </div>
            <div class="field">
                <label>Price</label>
                <input type="number" name="price" id="productPrice" placeholder="Price" min="0">
                <div class="error-message" id="errorPrice" style="color: red;"></div>
            </div>
            <div class="field">
                <label>Feature Image</label>
                <input type="file" name="feature_image" id="productFeatureImage" accept="image/*">
                <img id="featureImagePreview" style="display:none; width:50px; height:auto;" />
                <div class="error-message" id="errorFeatureImage" style="color: red;"></div>
            </div>
            <div class="field">
                <label>Gallery</label>
                <input type="file" name="gallery[]" id="productGallery" multiple accept="image/*">
                <div id="galleryPreviews"></div>
                <div class="error-message" id="errorGallery" style="color: red;"></div>
            </div>
            <div class="field">
                <label>Categories</label>
                <select name="categories" id="productCategories" multiple class="ui dropdown"></select>
            </div>
            <div class="field">
                <label>Tags</label>
                <select name="tags[]" id="productTags" multiple class="ui dropdown"></select>
            </div>
            <button type="button" class="ui button" id="cancel-product-btn">Cancel</button>
            <button type="button" class="ui button blue" id="save-product-btn">Save</button>
        </form>
    </div>
</div>
<!-- Add Property Modal -->
<div class="ui modal" id="addPropertyModal">
  <div class="header">Add Property</div>
  <div id="propertyMessage" class="ui hidden positive message">
  <p></p>
</div>
  <div id="propertyMessage" class="ui hidden positive message">
  <p></p>
</div>
  <div class="content">
    <form class="ui form" id="addPropertyForm">
      <div class="field">
        <label>Attribute name</label>
        <input type="text" name="propertyName" id="propertyName" placeholder="Attribute name" required>
        <div id="error-message" style="color: red; display: none;"></div> 
      </div>
      <div class="field">
        <label>Attribute type</label>
        <select name="propertyType" id="propertyType">
          <option value="category">Category</option>
          <option value="tag">Tag</option>
        </select>
      </div>
     
      <button type="button" class="ui button" id="cancel-property-btn">Cancel</button>
      <button type="button" class="ui button blue" id="save-property-btn"> Save </button>
    </form>
  </div>
</div>


<!-- Edit Product Modal -->
<!-- Edit Product Modal -->
<div class="ui modal" id="editProductModal">
  <div class="header">Edit Product</div>
  <div id="updateMessage" class="ui hidden positive message">
  <p></p>
</div>
  <div class="content">
    <form class="ui form" id="editProductForm">
      <input type="hidden" id="editProductId" name="product_id">
      
      <div class="field">
        <label>Product Name</label>
        <input type="text" name="product_name" id="editProductName" placeholder="Product Name" required>
        <div id="error-product-name" style="color: red; display: none;"></div>
      </div>

      <div class="field">
        <label>SKU</label>
        <input type="text" name="sku" id="editProductSKU" placeholder="SKU" required>
        <div id="error-sku" style="color: red; display: none;"></div>
      </div>

      <div class="field">
        <label>Price</label>
        <input type="number" name="price" id="editProductPrice" placeholder="Price" min="0">
        <div id="error-price" style="color: red; display: none;"></div>
      </div>

      <div class="field">
        <label>Feature Image</label>
        <input type="file" name="feature_image" id="editProductFeatureImage">
        <img id="currentFeatureImage" src="" alt="Current Feature Image" style="max-width: 200px; display: none;">
        <div id="error-feature-image" style="color: red; display: none;"></div>
      </div>
      <div class="field">
        <label>Gallery</label>
        <input type="file" name="gallery[]" id="editProductGallery" multiple >
        <div id="error-gallery" style="color: red; display: none;"></div>
        <div id="currentGallery"></div>
      </div>

      <div class="field">
        <label>Categories</label>
        <select name="categories[]" id="editProductCategories" multiple class="ui fluid dropdown"></select>
      </div>

      <div class="field">
        <label>Tags</label>
        <select name="tags[]" id="editProductTags" multiple class="ui fluid dropdown"></select>
      </div>

      <button type="button" class="ui button" id="cancel-edit-product-btn">Cancel</button>
      <button type="button" class="ui button blue" id="update-product-btn">Update</button>
    </form>
  </div>
</div>

<!-- delete -->
<div class="ui modal" id="confirmModal">
    <div class="header">Delete</div>
    <div class="content">
        
        <p>Are you sure you want to delete  products?</p>
    </div>
    <div class="actions">
        <div class="ui button" id="cancelDelete">Cancel</div>
        <div class="ui red button" id="confirmDelete">Delete</div>
    </div>
</div>




     </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <script src="popup.js"></script>
    <script src="add_products.js"></script>
    <script src="add_property.js"></script>
    <script src="update_product.js"></script>
    <script src="delete_product.js"></script>
    <script src="get_tags_categories.js"></script>
    <!-- <script src="search_products.js"></script> -->
     <script src="filter.js"></script>
    <script src="sync_villatheme.js"></script> 
</body>
