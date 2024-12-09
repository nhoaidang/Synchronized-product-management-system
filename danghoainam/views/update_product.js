$(document).ready(function() {
    
    var originalFeatureImage = '';
    var originalGalleryImages = [];
    var originalProductData = {};

    loadCategoriesAndTags();

    $(document).on('click', '.edit-btn', function() {
        var productId = $(this).data('id');
        loadProductData(productId);
    });

    function loadCategoriesAndTags() {
        $.ajax({
            url: 'http://localhost/danghoainam/db/load_categories_tags.php',
            type: 'GET',
            success: function(response) {
                var data = JSON.parse(response);
                $('#editProductCategories').html(data.categories);
                $('#editProductTags').html(data.tags);
                
                $('#editProductCategories, #editProductTags').dropdown({
                    fullTextSearch: true
                });
            },
            error: function() {
                $('#error-message').text('Error loading categories and tags.').show();
            }
        });
    }

    function loadProductData(productId) {
        $.ajax({
            url: 'http://localhost/danghoainam/db/get_product.php',
            type: 'GET',
            data: { id: productId },
            success: function(response) {
                var product = JSON.parse(response);
                originalProductData = JSON.parse(JSON.stringify(product));  //lưu mảng sp trữ dữ liệu gốc 
                
                $('#editProductId').val(product.id);
                $('#editProductName').val(product.product_name);
                $('#editProductSKU').val(product.sku);
                $('#editProductPrice').val(product.price);
    
                $('#editProductCategories').dropdown('clear');
                $('#editProductTags').dropdown('clear');
                
                $('#editProductCategories').dropdown('set selected', product.category_names);
                $('#editProductTags').dropdown('set selected', product.tag_names);
    
                $('#editProductCategories').dropdown('refresh');
                $('#editProductTags').dropdown('refresh');
    
                originalFeatureImage = product.feature_image;
                originalGalleryImages = product.gallery ? product.gallery.split(',') : [];
    
                if (originalFeatureImage) {
                    $('#currentFeatureImage').attr('src', 'http://localhost/danghoainam' + originalFeatureImage).show();
                } else {
                    $('#currentFeatureImage').hide();
                }
    
                $('#currentGallery').empty();
                if (originalGalleryImages.length) {
                    originalGalleryImages.forEach(function(image) {
                        $('#currentGallery').append('<img src="http://localhost/danghoainam' + image + '" alt="Gallery Image" style="max-width: 50px; margin-right: 10px;">');
                    });
                }
    
                $('#editProductFeatureImage').val('');
                $('#editProductGallery').val('');

                $('#editProductModal').modal('show');
            },
            error: function() {
                $('#error-message').text('Error loading product data.').show();
            }
        });
    }

    $('#update-product-btn').on('click', function() {
        $('#error-product-name, #error-sku, #error-price, #error-feature-image, #error-gallery').hide();
        
        var formData = new FormData($('#editProductForm')[0]);
    
        var selectedCategoryIds = $('#editProductCategories').dropdown('get value').map(Number);
        var selectedTagIds = $('#editProductTags').dropdown('get value').map(Number);
    
        formData.append('categories', JSON.stringify(selectedCategoryIds));
        formData.append('tags', JSON.stringify(selectedTagIds));
    
        var price = $('#editProductPrice').val().trim();
        if (isNaN(price) || parseFloat(price) < 0) {
            $('#error-price').text('Price must be a non-negative number.').show();
            return;
        }
    
        var allowedFormats = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
        var featureImage = $('#editProductFeatureImage')[0].files[0];
        if (featureImage && !allowedFormats.includes(featureImage.type)) {
            $('#error-feature-image').text('Invalid feature image format. Only JPG, JPEG, PNG, GIF, and WebP are allowed.').show();
            return;
        }
    
        var galleryImages = $('#editProductGallery')[0].files;
        for (var i = 0; i < galleryImages.length; i++) {
            if (!allowedFormats.includes(galleryImages[i].type)) {
                $('#error-gallery').text('Invalid gallery image format. Only JPG, JPEG, PNG, GIF, and WebP are allowed.').show();
                return;
            }
        }
        
        if (featureImage) {
            formData.append('feature_image', featureImage);
        } else {
            formData.append('feature_image', originalFeatureImage);
        }

        if (galleryImages.length > 0) {
            for (var i = 0; i < galleryImages.length; i++) {
                formData.append('gallery[]', galleryImages[i]);
            }
        } else {
            formData.append('gallery', originalGalleryImages.join(','));
        }

        // Updated isChanged check
        var isChanged = false;
        if (
            $('#editProductName').val() !== originalProductData.product_name ||
            $('#editProductSKU').val() !== originalProductData.sku ||
            parseFloat($('#editProductPrice').val()) !== parseFloat(originalProductData.price) ||
            !arraysEqual(selectedCategoryIds, originalProductData.category_ids) ||
            !arraysEqual(selectedTagIds, originalProductData.tag_ids) ||
            featureImage ||
            galleryImages.length > 0
        ) {
            isChanged = true;
        }

        // If nothing changed, show notification and return
        if (!isChanged) {
            $('#updateMessage1 p').text('No changes were made.');
            $('#updateMessage1').removeClass('hidden').addClass('visible');
            $('#editProductModal').modal('hide');
            setTimeout(function() {
                $('#updateMessage1').removeClass('visible').addClass('hidden');
            }, 3000);
            return;
        }
        

        
        $.ajax({
            url: 'http://localhost/danghoainam/db/update_product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var result = JSON.parse(response);

                if (result.success) {
                    $('#updateMessage p').text(result.success);
                    $('#updateMessage').removeClass('hidden').addClass('visible');
                    
                    setTimeout(function() {
                        $('#updateMessage').removeClass('visible').addClass('hidden');
                    }, 3000);
                    
                    if (result.product) {
                        
                        updateTableRow(result.product);

                        originalProductData = result.product;
                        originalFeatureImage = result.product.feature_image;
                        originalGalleryImages = result.product.gallery ? result.product.gallery.split(',') : [];

                        if (result.product.feature_image) {
                            $('#currentFeatureImage').attr('src', 'http://localhost/danghoainam' + result.product.feature_image).show();
                        } else {
                            $('#currentFeatureImage').hide();
                        }

                        $('#currentGallery').empty();
                        if (originalGalleryImages.length) {
                            originalGalleryImages.forEach(function(image) {
                                $('#currentGallery').append('<img src="http://localhost/danghoainam' + image + '" alt="Gallery Image" style="max-width: 50px; margin-right: 10px;">');
                            });
                        }

                        $('#editProductFeatureImage').val('');
                        $('#editProductGallery').val('');
                    }

                    $('#editProductModal').modal('hide');
                } else {
                    if (result.sku_error) {
                        $('#error-sku').text(result.sku_error).show();
                    }
                    if (result.name_error) {
                        $('#error-product-name').text(result.name_error).show();
                    }
                    if (result.error) {
                        $('#error-message').text('Error updating product: ' + result.error).show();
                    }
                }
            },
            error: function() {
                $('#error-message').text('Error updating product.').show();
            }
        });
    });

    $('#cancel-edit-product-btn').click(function() {
        $('#editProductModal').modal('hide');
    });

    function updateTableRow(product) {
        var row = $('button.edit-btn[data-id="' + product.id + '"]').closest('tr');
        if (row.length) {
            row.find('td:eq(0)').text(product.created_at);
            row.find('td:eq(1)').text(product.product_name);
            row.find('td:eq(2)').text(product.sku);
            row.find('td:eq(3)').text('$' + parseFloat(product.price).toFixed(2));
            
            var featureImagePath = product.feature_image ? 'http://localhost/danghoainam' + product.feature_image : '';
            row.find('td:eq(4) img').attr('src', featureImagePath);
            
            var galleryHtml = '';
            if (product.gallery) {
                var galleryImages = product.gallery.split(',');
                galleryImages.forEach(function(image) {
                    galleryHtml += '<img src="http://localhost/danghoainam' + image + '" alt="Gallery Image" style="max-width: 50px; margin-right: 10px;">';
                });
            }
            row.find('td:eq(5)').html(galleryHtml);

            var categoriesHtml = Array.isArray(product.categories) ? product.categories.join(', ') : '';
            row.find('td:eq(6)').text(categoriesHtml);

            var tagsHtml = Array.isArray(product.tags) ? product.tags.join(', ') : '';
            row.find('td:eq(7)').text(tagsHtml);
        }
    }

    function arraysEqual(arr1, arr2) {
        if (!Array.isArray(arr1) || !Array.isArray(arr2)) {
            return false;
        }
        if (arr1.length !== arr2.length) {
            return false;
        }
        const sortedArr1 = [...arr1].sort((a, b) => a - b);
        const sortedArr2 = [...arr2].sort((a, b) => a - b);
        return sortedArr1.every((value, index) => value === sortedArr2[index]);
    }

    loadCategoriesAndTags();
});