$(document).ready(function() {
    function reloadDropdown(propertyType) {
        $.ajax({
            url: 'http://localhost/danghoainam/db/load_categories_tags.php',
            type: 'GET',
            data: { propertyType: propertyType },
            success: function(response) {
                var result = JSON.parse(response);
                if (propertyType === 'category') {
                    $('#productCategories, #editProductCategories, #category-select').empty(); 
                    result.categories.forEach(function(category) {
                        $('#productCategories, #editProductCategories, #category-select').append(`<option value="${category}">${category}</option>`);
                    });
                } else if (propertyType === 'tag') {
                    $('#productTags, #editProductTags, #tag-select').empty(); 
                    result.tags.forEach(function(tag) {
                        $('#productTags, #editProductTags, #tag-select').append(`<option value="${tag}">${tag}</option>`);
                    });
                }
            },
            error: function() {
                console.log('Failed to load categories or tags');
            }
        });
    }

    function showPropertyNotification(message) {
        $('#propertyMessage p').text(message); 
        $('#propertyMessage').removeClass('hidden').addClass('visible'); 

        setTimeout(function() {
            $('#propertyMessage').removeClass('visible').addClass('hidden'); 
        }, 3000); 
    }

    function updateDropdown(propertyType, propertyName) {
        if (propertyType === 'category') {
            $('#productCategories, #editProductCategories, #category-select').append(`<option value="${propertyName}">${propertyName}</option>`);
        } else if (propertyType === 'tag') {
            $('#productTags, #editProductTags, #tag-select').append(`<option value="${propertyName}">${propertyName}</option>`);
        }
    }

    $('#save-property-btn').on('click', function() {
        var propertyName = $('#propertyName').val().trim();
        var propertyType = $('#propertyType').val().trim();
        $('#error-message').hide();
    
        if (propertyName && propertyType) {
            $.ajax({
                url: 'http://localhost/danghoainam/db/save_property.php',
                type: 'POST',
                data: {
                    propertyName: propertyName,
                    propertyType: propertyType
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        // Hiện thông báo
                        showPropertyNotification(result.success); 
                        
                        // Cập nhật dropdown mà không cần reload
                        updateDropdown(result.propertyType, propertyName); 
                        
                        // Đóng modal và reset form
                        $('#addPropertyModal').modal('hide');
                        $('#addPropertyForm')[0].reset();
                    } else {
                        $('#error-message').text(result.error).show();
                    }
                },
                error: function() {
                    $('#error-message').text('Please enter another property name.').show();
                }
            });
        } else {
            $('#error-message').text('Please fill in all fields.').show();
        }
    });
});
