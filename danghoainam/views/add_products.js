$(document).ready(function() {
    
    let currentPage = 1;
    let totalPages = 1;

    function loadProducts(page = 1) {
        $.ajax({
            url: 'http://localhost/danghoainam/db/fetch-add.php',
            type: 'GET',
            data: { page: page },
            success: function(response) {
                var data = JSON.parse(response);
                updateProductTable(data.products);
                updatePagination(data.currentPage, data.totalPages);
                currentPage = data.currentPage;
                totalPages = data.totalPages;
            },
            error: function(xhr, status, error) {
                $('#notificationMessage p').text('Failed to load products: ' + error);
                $('#notificationModal').modal('show');
            }
        });
    }

    function updateProductTable(products) {
        var tableBody = $('table tbody');
        tableBody.empty();
        products.forEach(function(product) {
            var newRow = `<tr>
                <td>${new Date(product.created_at).toLocaleDateString()}</td>
                <td>${product.product_name}</td>
                <td>${product.sku}</td>
                <td>$${parseFloat(product.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${product.feature_image ? `<img src='http://localhost/${product.feature_image}' width='50'>` : ''}</td>
                <td>${product.gallery ? product.gallery.split(',').map(img => `<img src='http://localhost/${img}' width='50'>`).join('') : ''}</td>
                <td>${product.categories.join(', ') || ' '}</td> 
                <td>${product.tags.join(', ') || ' '}</td>         
                <td>
                    <button class='ui icon button edit-btn' data-id='${product.id}'>
                        <i class='edit icon'></i>
                    </button>
                    <button class='ui icon button delete-btn' data-id='${product.id}'>
                        <i class='trash icon'></i>
                    </button>
                </td>
            </tr>`;
            tableBody.append(newRow);
        });
    }

    function updatePagination(currentPage, totalPages) {
        var pageNumbers = $('#page-numbers');
        pageNumbers.empty();

        for (var i = 1; i <= totalPages; i++) {
            var pageLink = $(`<a class="item ${i === currentPage ? 'active' : ''}">${i}</a>`);
            pageLink.click(function() {
                loadProducts(parseInt($(this).text()));
            });
            pageNumbers.append(pageLink);
        }

        $('#prev-page').toggleClass('disabled', currentPage === 1);
        $('#next-page').toggleClass('disabled', currentPage === totalPages);
    }

    $('#prev-page').click(function() {
        if (currentPage > 1) {
            loadProducts(currentPage - 1);
        }
    });

    $('#next-page').click(function() {
        if (currentPage < totalPages) {
            loadProducts(currentPage + 1);
        }
    });

    function clearErrors() {
        $('.error-message').text(''); 
    }

    function loadCategoriesAndTags() {
        $.ajax({
            url: 'http://localhost/danghoainam/db/load_categories_tags.php',
            type: 'GET',
            success: function(response) {
                var data = JSON.parse(response);
                $('#productCategories').html(data.categories);
                $('#productTags').html(data.tags);
                $('#productCategories').dropdown();
                $('#productTags').dropdown();
            },
            error: function(xhr, status, error) {
                $('#notificationMessage p').text('Failed to load categories and tags: ' + error);
                $('#notificationModal').modal('show');
            }
        });
    }

    loadCategoriesAndTags();

    // Preview feature image
    $('#productFeatureImage').on('change', function(e) {
        var file = e.target.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#featureImagePreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
    });

   
    $('#productGallery').on('change', function(e) {
        $('#galleryPreviews').empty();
        var files = e.target.files;
        for (var i = 0; i < files.length; i++) {
            var reader = new FileReader();
            reader.onload = (function(file) {
                return function(e) {
                    $('#galleryPreviews').append('<img src="' + e.target.result + '" style="width:50px; height:auto; margin:5px;">');
                };
            })(files[i]);
            reader.readAsDataURL(files[i]);
        }
    });

    $('#save-product-btn').on('click', function() {
        clearErrors();  

        var form = $('#addProductForm')[0];
        var formData = new FormData(form);
    
        var allowedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        var isValid = true; 
        var featureImage = $('#productFeatureImage')[0].files[0];
        if (featureImage && !allowedFormats.includes(featureImage.type)) {
            $('#errorFeatureImage').text('Invalid feature image format. Only JPG, JPEG, PNG, GIF, and WebP are allowed.');
            isValid = false;
        }
    
        var galleryImages = $('#productGallery')[0].files;
        for (var i = 0; i < galleryImages.length; i++) {
            if (!allowedFormats.includes(galleryImages[i].type)) {
                $('#errorGallery').text('Invalid gallery image format. Only JPG, JPEG, PNG, GIF, and WebP are allowed.');
                isValid = false;
                break;
            }
        }
    
        var productName = $('#productName').val().trim();
        var sku = $('#productSKU').val().trim();
        var price = $('#productPrice').val().trim();
        
        if (productName === '') {
            $('#errorProductName').text('Product name cannot be empty.');
            isValid = false;
        }

        if (sku === '') {
            $('#errorSKU').text('SKU cannot be empty.');
            isValid = false;
        }

        if (price !== '' && (isNaN(price) || parseFloat(price) < 0)) {
            $('#errorPrice').text('Price must be a non-negative number.');
            isValid = false;
        }

        if (!isValid) {
            return; 
        }
    
        if (price === '') {
            price = '0.00';
        }

        var selectedCategories = $('#productCategories').dropdown('get value').map(function(id) {
            return { id: id, name: $('#productCategories option[value="'+id+'"]').text() };
        });
    
        var selectedTags = $('#productTags').dropdown('get value').map(function(id) {
            return { id: id, name: $('#productTags option[value="'+id+'"]').text() };
        });
    

        formData.append('categories', selectedCategories.map(c => c.name).join(','));
        formData.append('tags', selectedTags.map(t => t.name).join(','));
    
        $.ajax({
            url: 'http://localhost/danghoainam/db/save_product.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                var data = JSON.parse(response);
        
                if (data.error) {
                    $('#errorProductName').text('');
                    $('#errorSKU').text('');
        
                    if (data.messages.sku) {
                        $('#errorSKU').text(data.messages.sku);
                    }
        
                    if (data.messages.product_name) {
                        $('#errorProductName').text(data.messages.product_name);
                    }
                    return; 
                }
        
                $('#notificationMessage p').text('Product saved successfully!');
                $('#notificationMessage').removeClass('hidden').addClass('visible');
                
                $('#addProductModal').modal('hide');
                
                setTimeout(function() {
                    $('#notificationMessage').removeClass('visible').addClass('hidden');
                }, 3000);

                $('#addProductForm')[0].reset();
                $('#featureImagePreview').attr('src', '').hide();
                $('#galleryPreviews').empty();
                $('#productCategories').dropdown('clear');
                $('#productTags').dropdown('clear');
        
                loadProducts(currentPage);
            },
            error: function(xhr, status, error) {
                $('#notificationMessage').removeClass('positive').addClass('negative');
                $('#notificationMessage p').text('An error occurred: ' + error);
                $('#notificationMessage').removeClass('hidden').addClass('visible');
            }
        });
        
    });
    loadProducts();
});