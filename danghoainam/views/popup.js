$(document).ready(function() {
     $('#add-product-btn').click(function() {
         $('#addProductModal').modal('show');
         
        //  $('#addProductModal')[0].reset();
     });
     $('#add-property-btn').click(function() {
         $('#addPropertyModal').modal('show');
        //  $('#addPropertyModal')[0].reset();
     });

  
    // cancel
        $('#cancel-product-btn').click(function() {
            $('#addProductModal').modal('hide');
            $('#addProductForm')[0].reset(); 
        });
        $('#cancel-property-btn').on('click', function() {
          
            $('#addPropertyModal').modal('hide');
            $('#addPropertyForm')[0].reset();
        });
        $('#cancel-edit-btn').on('click', function() {
          
            $('#editProductModal').modal('hide');
            $('#editProductModal')[0].reset();
        });
    //save

    

    

  
     
  
});//end

