var manageProductTable;

$(document).ready(function() {
    $('#navProduct').addClass('active');

    // Initialize DataTable
    manageProductTable = $("#manageProductTable").DataTable({
        'ajax': 'php_action/fetchProduct.php',
        'order': []
    });

    // Initialize edit modal with proper options
    $('#editProductModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    // Add Product Form
    $("#submitProductForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();

        // Remove error messages
        $(".text-danger").remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        var productImage = $("#productImage").val();
        var productName = $("#productName").val();
        var quantity = $("#quantity").val();
        var rate = $("#rate").val();
        var brandName = $("#brandName").val();
        var categoryName = $("#categoryName").val();
        var productStatus = $("#productStatus").val();

        if(productImage == "") {
            $("#productImage").closest('.form-group').addClass('has-error');
            $("#productImage").after('<p class="text-danger">Product Image field is required</p>');
        } else {
            $("#productImage").closest('.form-group').addClass('has-success');
        }

        if(productName == "") {
            $("#productName").after('<p class="text-danger">Product Name field is required</p>');
            $('#productName').closest('.form-group').addClass('has-error');
        } else {
            $('#productName').closest('.form-group').addClass('has-success');
        }

        if(quantity == "") {
            $("#quantity").after('<p class="text-danger">Quantity field is required</p>');
            $('#quantity').closest('.form-group').addClass('has-error');
        } else {
            $('#quantity').closest('.form-group').addClass('has-success');
        }

        if(rate == "") {
            $("#rate").after('<p class="text-danger">Rate field is required</p>');
            $('#rate').closest('.form-group').addClass('has-error');
        } else {
            $('#rate').closest('.form-group').addClass('has-success');
        }

        if(brandName == "") {
            $("#brandName").after('<p class="text-danger">Brand Name field is required</p>');
            $('#brandName').closest('.form-group').addClass('has-error');
        } else {
            $('#brandName').closest('.form-group').addClass('has-success');
        }

        if(categoryName == "") {
            $("#categoryName").after('<p class="text-danger">Category Name field is required</p>');
            $('#categoryName').closest('.form-group').addClass('has-error');
        } else {
            $('#categoryName').closest('.form-group').addClass('has-success');
        }

        if(productStatus == "") {
            $("#productStatus").after('<p class="text-danger">Product Status field is required</p>');
            $('#productStatus').closest('.form-group').addClass('has-error');
        } else {
            $('#productStatus').closest('.form-group').addClass('has-success');
        }

        if(productImage && productName && quantity && rate && brandName && categoryName && productStatus) {
            var form = $(this);
            // Button loading state
            $("#createProductBtn").button('loading');

            var formData = new FormData(this);

            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success:function(response) {
                    // Button loading state
                    $("#createProductBtn").button('reset');

                    if(response.success == true) {
                        // Reset the form
                        $("#submitProductForm")[0].reset();
                        $("html, body, div.modal, div.modal-content, div.modal-body").animate({scrollTop: '0'}, 100);
                        
                        // Display success message
                        $('#add-product-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        // Reload the manage product table
                        manageProductTable.ajax.reload(null, false);

                        // Close modal after success
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                $('#addProductModal').modal('hide');
                            });
                        });
                    }
                }
            });   
        }
        return false;
    });

    // Edit Product Form
    $("#editProductForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();

        // Remove error messages
        $(".text-danger").remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        var productName = $("#editProductName").val();
        var quantity = $("#editQuantity").val();
        var rate = $("#editRate").val();
        var brandName = $("#editBrandName").val();
        var categoryName = $("#editCategoryName").val();
        var productStatus = $("#editProductStatus").val();

        if(productName == "") {
            $("#editProductName").after('<p class="text-danger">Product Name field is required</p>');
            $('#editProductName').closest('.form-group').addClass('has-error');
        } else {
            $('#editProductName').closest('.form-group').addClass('has-success');
        }

        if(quantity == "") {
            $("#editQuantity").after('<p class="text-danger">Quantity field is required</p>');
            $('#editQuantity').closest('.form-group').addClass('has-error');
        } else {
            $('#editQuantity').closest('.form-group').addClass('has-success');
        }

        if(rate == "") {
            $("#editRate").after('<p class="text-danger">Rate field is required</p>');
            $('#editRate').closest('.form-group').addClass('has-error');
        } else {
            $('#editRate').closest('.form-group').addClass('has-success');
        }

        if(brandName == "") {
            $("#editBrandName").after('<p class="text-danger">Brand Name field is required</p>');
            $('#editBrandName').closest('.form-group').addClass('has-error');
        } else {
            $('#editBrandName').closest('.form-group').addClass('has-success');
        }

        if(categoryName == "") {
            $("#editCategoryName").after('<p class="text-danger">Category Name field is required</p>');
            $('#editCategoryName').closest('.form-group').addClass('has-error');
        } else {
            $('#editCategoryName').closest('.form-group').addClass('has-success');
        }

        if(productStatus == "") {
            $("#editProductStatus").after('<p class="text-danger">Product Status field is required</p>');
            $('#editProductStatus').closest('.form-group').addClass('has-error');
        } else {
            $('#editProductStatus').closest('.form-group').addClass('has-success');
        }

        if(productName && quantity && rate && brandName && categoryName && productStatus) {
            var form = $(this);
            // Button loading state
            $("#editProductBtn").button('loading');

            var formData = new FormData(this);

            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success:function(response) {
                    // Button loading state
                    $("#editProductBtn").button('reset');

                    if(response.success == true) {
                        // Display success message
                        $('#edit-product-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        // Reload the manage product table
                        manageProductTable.ajax.reload(null, false);

                        // Close modal after success
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                $('#editProductModal').modal('hide');
                            });
                        });
                    }
                }
            });   
        }
        return false;
    });
});

// Edit Product Function
function editProduct(productId = null) {
    if(productId) {
        $('#productId').remove();
        // Remove error messages
        $('.text-danger').remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        $.ajax({
            url: 'php_action/fetchSelectedProduct.php',
            type: 'post',
            data: {productId: productId},
            dataType: 'json',
            success:function(response) {
                $("#editProductImage").attr('src', 'stock/'+response.product_image);
                $("#editProductName").val(response.product_name);
                $("#editQuantity").val(response.quantity);
                $("#editRate").val(response.rate);
                $("#editBrandName").val(response.brand_id);
                $("#editCategoryName").val(response.categories_id);
                $("#editProductStatus").val(response.active);
                // Add hidden product id
                $(".editProductFooter").after('<input type="hidden" name="productId" id="productId" value="'+response.product_id+'" />');

                // Show modal
                $('#editProductModal').modal('show');
            }
        });
    }
}

// Remove Product Function
function removeProduct(productId = null) {
    if(productId) {
        $('#removeProductId').remove();
        $.ajax({
            url: 'php_action/fetchSelectedProduct.php',
            type: 'post',
            data: {productId: productId},
            dataType: 'json',
            success:function(response) {
                $('.removeProductFooter').after('<input type="hidden" name="removeProductId" id="removeProductId" value="'+response.product_id+'" />');
                $("#removeProductBtn").unbind('click').bind('click', function() {
                    $.ajax({
                        url: 'php_action/removeProduct.php',
                        type: 'post',
                        data: {productId : productId},
                        dataType: 'json',
                        success:function(response) {
                            if(response.success == true) {
                                $('#removeProductModal').modal('hide');
                                // Reload the manage product table
                                manageProductTable.ajax.reload(null, false);
                                $('.remove-messages').html('<div class="alert alert-success">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                    '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                '</div>');
                            }
                        }
                    });
                });
                $('#removeProductModal').modal('show');
            }
        });
    }
}