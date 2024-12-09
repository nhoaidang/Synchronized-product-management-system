$(document).ready(function() {
   
    $('.ui.dropdown').dropdown();
    loadProducts(1);
});

var productCache = {};
var currentFilters = {};
var isViewingAll = true;
var debounceTimer;

function loadProducts(page) {
    fetchProductsFromServer(page, isViewingAll ? {} : currentFilters);
}

function applyFilters() {
    isViewingAll = false;
    currentFilters = {
        sort_date: $('#sort-date').val(),
        sort_order: $('#sort-order').val(),
        category: $('#category-select').val() ? $('#category-select').val().join(',') : '',
        tags: $('#tag-select').val() ? $('#tag-select').val().join(',') : '',
        start_date: $('#start-date').val(),
        end_date: $('#end-date').val(),
        price_from: $('#price-from').val(),
        price_to: $('#price-to').val(),
        search: $('#search-input').val()
    };
    productCache = {};
    loadProducts(1);
}

function fetchProductsFromServer(page, filters) {
    $.ajax({
        url: 'http://localhost/danghoainam/db/filter_products.php',
        type: 'GET',
        data: Object.assign({}, filters, { page: page }),
        success: function(response) {
            var data = JSON.parse(response);
            productCache[page] = {
                products: data.products,
                currentPage: data.currentPage,
                totalPages: data.totalPages
            };
            renderProducts(data.products, data.currentPage, data.totalPages);
        },
        error: function() {
            alert('An error occurred while fetching products.');
        }
    });
}

function renderProducts(products, currentPage, totalPages) {
    $('#product-table-body').html(products); 
    updatePagination(currentPage, totalPages); 
}

$('#filter-button, #search-button').on('click', function() {
    applyFilters(); 
});


$('#search-input').on('keyup', function(e) {
    clearTimeout(debounceTimer); 
    debounceTimer = setTimeout(function() {
        applyFilters(); 
    }, 300); 
});


$('#view-all-button').on('click', function() {
    isViewingAll = true;
    currentFilters = {};
    productCache = {}; 
    loadProducts(1); 
 
    $('.ui.dropdown').dropdown('clear');
    $('input').val('');
});

function updatePagination(currentPage, totalPages) {
    var paginationHtml = '';
    for (var i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            paginationHtml += '<a class="active item" data-page="' + i + '">' + i + '</a>';
        } else {
            paginationHtml += '<a class="item" data-page="' + i + '">' + i + '</a>';
        }
    }
    $('#page-numbers').html(paginationHtml);
    $('#prev-page').toggleClass('disabled', currentPage === 1);
    $('#next-page').toggleClass('disabled', currentPage === totalPages);
}


$(document).on('click', '#page-numbers a.item:not(.disabled)', function() {
    var page = $(this).data('page');
    loadProducts(page);
});

$('#prev-page').on('click', function() {
    var currentPage = parseInt($('#page-numbers a.active.item').data('page'));
    if (currentPage > 1) {
        loadProducts(currentPage - 1);
    }
});

$('#next-page').on('click', function() {
    var currentPage = parseInt($('#page-numbers a.active.item').data('page'));
    var totalPages = parseInt($('#page-numbers a.item:last').data('page'));
    if (currentPage < totalPages) {
        loadProducts(currentPage + 1);
    }
});
