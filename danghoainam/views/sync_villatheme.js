$(document).ready(function () {
    let currentPage = 1;
    const productsPerPage = 5;
    let allProducts = [];
    let syncInProgress = false;
    let lastSyncedIndex = -1;
    let currentSyncPage = 1;

    // Cache for categories and tags
    let cache = {
       categories: null,
       tags: null
   };

   // Sync button handler
   $('#syncFromVillaTheme').on('click', function () {
       if (!syncInProgress) {
           syncInProgress = true;
           $(this).addClass('loading');
           disableInterface();
           currentSyncPage = 1;
           allProducts = [];
           syncProducts();
       }
   });

   // Sync products from VillaTheme
   function syncProducts() {
       const url = currentSyncPage === 1
           ? 'https://villatheme.com/extensions/'
           : `https://villatheme.com/extensions/page/${currentSyncPage}/`;

       $.ajax({
           url: 'http://localhost/danghoainam/db/villatheme-proxy.php',
           method: 'GET',
           data: { url: url },
           success: function (response) {
               const newProducts = extractProductUrls(response);
               if (newProducts.length > 0) {
                   allProducts = allProducts.concat(newProducts);
                   currentSyncPage++;
                   syncProducts();
               } else {
                   processProducts();
               }
           },
           error: function (xhr, status, error) {
               console.error("Error while getting product list:", error);
               finishSync();
           }
       });
   }

   // Extract product URLs
   function extractProductUrls(html) {
       const urls = [];
       const parser = new DOMParser();
       const doc = parser.parseFromString(html, 'text/html');

       const productElements = doc.querySelectorAll('li.product div.col-sm-6:first-of-type a');
       productElements.forEach(el => {
           urls.push(el.href);
       });

       return urls;
   }

   // Process products
   async function processProducts() {
       const startIndex = lastSyncedIndex + 1;
       const endIndex = Math.min(startIndex + productsPerPage, allProducts.length);
       const batchProducts = allProducts.slice(startIndex, endIndex);

       try {
           const detailedProducts = await Promise.all(batchProducts.map(getProductDetails));
           await updateOrCreateProducts(detailedProducts);
           lastSyncedIndex += detailedProducts.length;

           console.log("Synchronization complete for batch of products");

           if (lastSyncedIndex < allProducts.length - 1) {
               processProducts();
           } else {
               finishSync();
           }
       } catch (error) {
           console.error("Error processing products:", error);
           finishSync();
       }
   }

   // Get product details
   function getProductDetails(url) {
       return new Promise((resolve, reject) => {
           console.log("Getting details for URL:", url);
           $.ajax({
               url: 'http://localhost/danghoainam/db/villatheme-proxy.php',
               method: 'GET',
               data: { url: url },
               success: function (response) {
                   const detailedProduct = extractProductDetails(response);
                   resolve(detailedProduct);
               },
               error: function (xhr, status, error) {
                   console.error("Error getting product details:", error);
                   reject(error);
               }
           });
       });
   }

   function extractProductDetails(html) {
      
       const product = {};

       function decodeHTMLEntities(text) {
           const txt = document.createElement("textarea");
           txt.innerHTML = text;
           return txt.value;
       }

       // Extract product name
       const nameMatch = html.match(/<h1 class="product_title entry-title">([^<]+)<\/h1>/);
       product.product_name = nameMatch ? decodeHTMLEntities(nameMatch[1].trim()) : '';

       // Extract SKU
       const skuMatch = html.match(/<span class="sku">([^<]+)<\/span>/);
       if (skuMatch) {
           product.sku = decodeHTMLEntities(skuMatch[1].trim());
       } else {
           product.sku = product.product_name
               .toLowerCase()
               .replace(/\s+/g, '-')
               .replace(/[^\w-]/g, '')
               .replace(/--+/g, '-')
               .replace(/^-+|-+$/g, '');
       }

       // Extract Price
       const discountPriceMatch = html.match(/<ins[^>]*><span[^>]*class="woocommerce-Price-amount amount"[^>]*><bdi[^>]*>.*?([\d.,]+)<\/bdi><\/span><\/ins>/);
       const regularPriceMatch = html.match(/<span[^>]*class="woocommerce-Price-amount amount"[^>]*><bdi[^>]*>.*?([\d.,]+)<\/bdi><\/span>(?=<\/p>)/);

       // Use discount price if available; otherwise, use regular price
       let priceText = discountPriceMatch ? discountPriceMatch[1] : (regularPriceMatch ? regularPriceMatch[1] : '0.00');

       // Clean and parse the price
       let cleanedPrice = priceText.replace(/[^0-9.]/g, '');
       let productPrice = parseFloat(cleanedPrice);
       product.price = isNaN(productPrice) ? '0.00' : productPrice.toFixed(2);

       // Extract Feature Image
       const featureImageMatch = html.match(/<a[^>]*href="([^"]+)"[^>]*>\s*<img[^>]*src="([^"]+)"[^>]*>/);
       product.feature_image = featureImageMatch ? featureImageMatch[2] : '';


       // Extract gallery images
       const regex = /<div[^>]+class="woocommerce-product-gallery__image"[^>]*>.*?<img[^>]+data-src="([^"]+)"/g;
       let match;
       const galleryImages = [];

       while ((match = regex.exec(html)) !== null) {
           galleryImages.push(match[1]); 
       }
       product.gallery = galleryImages;

       // Extract Categories
       const categoryMatch = html.match(/<span[^>]*class="posted_in"[^>]*>(.*?)<\/span>/);
       product.categories = categoryMatch ? Array.from(categoryMatch[1].matchAll(/<a[^>]*>(.*?)<\/a>/g)).map(match => decodeHTMLEntities(match[1])).join(', ') : '';

       // Extract Tags
       const tagsRegex = /<span class="tagged_as">Tags: (.*?)<\/span>/s;
       const tagsMatch = html.match(tagsRegex);
       product.tags = tagsMatch ? tagsMatch[1].replace(/<\/?a[^>]*>/g, '').trim() : '';

       return product;
   }


   function updateOrCreateProducts(products) {
       return new Promise((resolve, reject) => {
           $.ajax({
               url: 'http://localhost/danghoainam/db/villatheme-sync-script.php',
               method: 'POST',
               data: JSON.stringify(products),
               contentType: 'application/json',
               success: function (response) {
                   console.log("Products have been updated/created:", response);
                   resolve();
               },
               error: function (xhr, status, error) {
                   console.error("Error when updating/creating new products:", error);
                   reject(error);
               }
           });
       });
   }

   function finishSync() {
       syncInProgress = false;
       $('#syncFromVillaTheme').removeClass('loading');
       enableInterface();
       console.log("Completed synchronization of all products");

       // Fetch the latest products and update the table
       fetchUpdatedProducts();
   }

   function fetchUpdatedProducts() {
       $.ajax({
           url: 'http://localhost/danghoainam/db/fetch-products.php',
           method: 'GET',
           success: function (response) {
               allProducts = JSON.parse(response);
               currentPage = 1; // Reset to first page after sync
               updateProductTable();
               updatePagination();
           },
           error: function (xhr, status, error) {
               console.error("Error fetching updated products:", error);
           }
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
               const imagePath = `http://localhost/danghoainam${img.trim()}`;
               galleryHTML += `<img src="${imagePath}" width="50" style="margin-right: 5px;">`;
           });


           const categories = Array.isArray(product.categories)
               ? product.categories.map(cat => cat.name).join(', ')
               : (typeof product.categories === 'string' ? product.categories : '');


           const tags = Array.isArray(product.tags)
               ? product.tags.map(tag => tag.name).join(', ')
               : (typeof product.tags === 'string' ? product.tags : '');


           const featureImagePath = product.feature_image ? `http://localhost/danghoainam/${product.feature_image}` : '';

           const row = `
               <tr>
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

       // Generate page numbers
       for (let i = 1; i <= totalPages; i++) {
           pageNumbers.append(`
               <a class="item ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>
           `);
       }

       // Update prev/next buttons
       $('#prev-page').toggleClass('disabled', currentPage === 1);
       $('#next-page').toggleClass('disabled', currentPage === totalPages);

       // Event listeners for pagination
       $('#prev-page').off('click').on('click', function () {
           if (currentPage > 1) {
               currentPage--;
               updateProductTable();
               updatePagination();
           }
       });

       $('#next-page').off('click').on('click', function () {
           if (currentPage < totalPages) {
               currentPage++;
               updateProductTable();
               updatePagination();
           }
       });

       $('#page-numbers .item').off('click').on('click', function () {
           currentPage = parseInt($(this).data('page'));
           updateProductTable();
           updatePagination();
       });
   }

   function disableInterface() {
       $('body').append('<div id="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;"></div>');
       $('button, a, input, select').not('#syncFromVillaTheme').prop('disabled', true).css('pointer-events', 'none');
   }

   function enableInterface() {
       $('#overlay').remove();
       $('button, a, input, select').prop('disabled', false).css('pointer-events', 'auto');
   }

   // Initial load
   fetchUpdatedProducts();
});