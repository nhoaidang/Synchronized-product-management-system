$(document).ready(function() {
    let productIdToDelete;
    let allProducts = [];
    let currentPage = 1;
    const productsPerPage = 5; 

    function fetchAllProducts() {
        return $.ajax({
            url: 'http://localhost/danghoainam/db/fetch-products.php',
            type: 'GET',
            dataType: 'json'
        });
    }

    function updateProductTable() {
        const tbody = $('#product-table-body');
        tbody.empty();
    
        const startIndex = (currentPage - 1) * productsPerPage;
        const endIndex = startIndex + productsPerPage;
        const productsToShow = allProducts.slice(startIndex, endIndex);
    
        productsToShow.forEach(product => {
            const galleryImages = product.gallery ? product.gallery.split(',') : [];
            let galleryHTML = '';
            galleryImages.forEach(img => {
                galleryHTML += `<img src="http://localhost/danghoainam/${img.trim()}" width="50" style="margin-right: 5px;">`;
            });
            
            const featureImagePath = product.feature_image ? `http://localhost/danghoainam/${product.feature_image}` : '';
    
            const categories = product.categories ? product.categories.split(',').join(', ') : 'No categories';
            const tags = product.tags ? product.tags.split(',').join(', ') : 'No tags';
    
            const row = `
                <tr class="product-item">
                    <td>${new Date(product.created_at).toLocaleDateString()}</td>
                    <td>${product.product_name}</td>
                    <td>${product.sku}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td><img src="${featureImagePath}" width="50"></td>
                    <td>${galleryHTML}</td>
                    <td>${categories}</td>
                    <td>${tags}</td>
                    <td>
                        <button class='ui icon button edit-btn' data-id='${product.id}'>
                            <i class='edit icon'></i>
                        </button>
                        <button class='ui icon button delete-btn' data-id='${product.id}'>
                            <i class='trash icon'></i>
                        </button>
                    </td>
                </tr>
            `;
    
            tbody.append(row);
        });
    }
    
    function updatePagination() {
        const totalPages = Math.ceil(allProducts.length / productsPerPage);
        const pageNumbers = $('#page-numbers');
        pageNumbers.empty();

        for (let i = 1; i <= totalPages; i++) {
            pageNumbers.append(`
                <a class="item ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>
            `);
        }

        $('#prev-page').toggleClass('disabled', currentPage === 1);
        $('#next-page').toggleClass('disabled', currentPage === totalPages);

        $('#prev-page').off('click').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updateProductTable();
                updatePagination();
            }
        });

        $('#next-page').off('click').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                updateProductTable();
                updatePagination();
            }
        });

        $('#page-numbers .item').off('click').on('click', function() {
            currentPage = parseInt($(this).data('page'));
            updateProductTable();
            updatePagination();
        });
    }

    $(document).on('click', '.delete-btn', function() {
        productIdToDelete = $(this).data('id');
        $('#confirmModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: 'http://localhost/danghoainam/db/delete_product.php',
            type: 'POST',
            data: { id: productIdToDelete },
            success: function(response) {
                $('#confirmModal').modal('hide');

                fetchAllProducts().done(function(data) {
                    allProducts = data;

                    const totalPages = Math.ceil(allProducts.length / productsPerPage);

                    if (currentPage > totalPages) {
                        currentPage = totalPages;
                    }

                    const startIndex = (currentPage - 1) * productsPerPage;
                    const productsOnCurrentPage = allProducts.slice(startIndex, startIndex + productsPerPage);

                    if (productsOnCurrentPage.length === 0 && currentPage > 1) {
                        currentPage--;
                    }

                    updateProductTable();
                    updatePagination();
                }).fail(function(error) {
                    console.log('Error fetching products: ' + error);
                });
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
                $('#confirmModal').modal('hide');
            }
        });
    });

    $('#cancelDelete').click(function() {
        $('#confirmModal').modal('hide');
    });

    $('#trash-icon').click(function() {
        $('#confirmModal .content p').text('Are you sure you want to delete all products?');
        productIdToDelete = null; 
        $('#confirmModal').modal('show');

        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: 'http://localhost/danghoainam/db/delete_all_products.php',
                type: 'POST',
                success: function(response) {
                    $('#confirmModal').modal('hide');

                    allProducts = [];
                    currentPage = 1;

                    updateProductTable();
                    updatePagination();
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                    $('#confirmModal').modal('hide');
                }
            });
        });
    });

    fetchAllProducts().done(function(products) {
        allProducts = products;
        updateProductTable();
        updatePagination();
    });
});
