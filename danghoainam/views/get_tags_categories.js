$(document).ready(function() {
    $.ajax({
        url: 'http://localhost/danghoainam/db/get_categories.php',
        type: 'GET',
        success: function(data) {
            var categorySelect = $('#category-select');
            $.each(data, function(index, category) {
                categorySelect.append(
                    $('<option>', {
                        value: category.name, 
                        text: category.name
                    })
                );
            });
        },
        error: function() {
            alert('Failed to load categories.');
        }
    });


    $.ajax({
        url: 'http://localhost/danghoainam/db/get_tags.php',
        type: 'GET',
        success: function(data) {
            var tagSelect = $('#tag-select');
            $.each(data, function(index, tag) {
                tagSelect.append(
                    $('<option>', {
                        value: tag.name, 
                        text: tag.name
                    })
                );
            });
        },
        error: function() {
            alert('Failed to load tags.');
        }
    });
});