var manageProductTable;

$(document).ready(function() {
    // top nav bar 
    $('#navProduct').addClass('active');
    
    // manage product data table
    manageProductTable = $('#manageProductTable').DataTable({
        'ajax': 'php_action/fetchProduct.php',
        'order': [],
        'error': function(xhr, error, thrown) {
            console.error('DataTables error:', error);
            $('#manageProductTable').html('<div class="alert alert-danger">Error loading product data. Please try refreshing the page.</div>');
        }
    });

    // add product modal btn clicked
    $("#addProductModalBtn").unbind('click').bind('click', function() {
        resetProductForm();
        showAddProductModal();
    });

    // submit product form
    $("#submitProductForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        
        if(validateProductForm()) {
            submitProductForm($(this));
        }
        return false;
    });
}); 

function resetProductForm() {
    $("#submitProductForm")[0].reset();
    removeFormErrors();
    initializeFileInput();
}

function removeFormErrors() {
    $(".text-danger").remove();
    $(".form-group").removeClass('has-error').removeClass('has-success');
}

function initializeFileInput() {
    $("#productImage").fileinput({
        overwriteInitial: true,
        maxFileSize: 2500,
        showClose: false,
        showCaption: false,
        browseLabel: '',
        removeLabel: '',
        browseIcon: '<i class="glyphicon glyphicon-folder-open"></i>',
        removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
        removeTitle: 'Cancel or reset changes',
        elErrorContainer: '#kv-avatar-errors-1',
        msgErrorClass: 'alert alert-block alert-danger',
        defaultPreviewContent: '<img src="assests/images/photo_default.png" alt="Profile Image" style="width:100%;">',
        layoutTemplates: {main2: '{preview} {remove} {browse}'},                                   
        allowedFileExtensions: ["jpg", "png", "gif", "JPG", "PNG", "GIF"]
    });
}

function validateProductForm() {
    var isValid = true;
    var fields = {
        'productImage': 'Product Image',
        'productName': 'Product Name',
        'quantity': 'Quantity',
        'rate': 'Rate',
        'brandName': 'Brand Name',
        'categoryName': 'Category Name',
        'productStatus': 'Product Status'
    };

    for(var field in fields) {
        var value = $("#" + field).val();
        if(!value) {
            displayFieldError(field, fields[field] + ' field is required');
            isValid = false;
        } else {
            removeFieldError(field);
        }
    }

    return isValid;
}

function displayFieldError(field, message) {
    $("#" + field).after('<p class="text-danger">' + message + '</p>');
    $('#' + field).closest('.form-group').addClass('has-error');
}

function removeFieldError(field) {
    $("#" + field).find('.text-danger').remove();
    $("#" + field).closest('.form-group').removeClass('has-error').addClass('has-success');
}

function submitProductForm($form) {
    $("#createProductBtn").button('loading');
    
    var formData = new FormData($form[0]);
    
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: formData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        success: function(response) {
            handleProductSubmitResponse(response);
        },
        error: function(xhr, status, error) {
            console.error('Product submission error:', error);
            displayProductError('An error occurred while saving the product. Please try again.');
            $("#createProductBtn").button('reset');
        }
    });
}

function handleProductSubmitResponse(response) {
    $("#createProductBtn").button('reset');
    
    if(response.success) {
        resetProductForm();
        scrollToTop();
        displayProductSuccess(response.messages);
        manageProductTable.ajax.reload(null, true);
    } else {
        displayProductError(response.messages);
    }
}

function displayProductSuccess(message) {
    $('#add-product-messages').html(
        '<div class="alert alert-success">'+
        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ message +
        '</div>'
    );
    
    $(".alert-success").delay(500).show(10, function() {
        $(this).delay(3000).hide(10, function() {
            $(this).remove();
        });
    });
}

function displayProductError(message) {
    $('#add-product-messages').html(
        '<div class="alert alert-danger">'+
        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ message +
        '</div>'
    );
}

function scrollToTop() {
    $("html, body, div.modal, div.modal-content, div.modal-body").animate({scrollTop: '0'}, 100);
}

function editProduct(productId) {
    if(!productId) return;

    resetEditForm();
    showLoadingState();
    
    $.ajax({
        url: 'php_action/fetchSelectedProduct.php',
        type: 'post',
        data: { 
            productId: productId,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            hideLoadingState();
            if(response.success) {
                populateEditForm(response);
            } else {
                displayEditError(response.messages || 'Error loading product data');
            }
        },
        error: function(xhr, status, error) {
            hideLoadingState();
            console.error('Error fetching product:', error);
            displayEditError('Failed to load product data. Please try again.');
        }
    });
}

function populateEditForm(data) {
    // Add hidden product ID
    $("#editProductForm").append('<input type="hidden" name="productId" id="productId" value="'+data.product_id+'" />');
    
    // Populate form fields
    $("#editProductName").val(data.product_name);
    $("#editQuantity").val(data.quantity);
    $("#editRate").val(data.rate);
    $("#editBrandName").val(data.brand_id);
    $("#editCategoryName").val(data.categories_id);
    $("#editProductStatus").val(data.active);
    
    // Update product image with error handling
    if (data.product_image) {
        // Remove any '../' or 'stock/' prefix and ensure proper path
        var imagePath = data.product_image.replace(/^(\.\.\/|stock\/)/, '');
        console.log('Image path before:', data.product_image);
        console.log('Image path after:', imagePath);
        
        $("#getProductImage")
            .attr('src', imagePath)
            .on('error', function() {
                console.warn('Failed to load product image:', imagePath);
                $(this).attr('src', 'assests/images/photo_default.png')
                    .off('error'); // Remove error handler after fallback
            });
    } else {
        $("#getProductImage").attr('src', 'assests/images/photo_default.png');
    }
}

function resetEditForm() {
    $("#editProductForm")[0].reset();
    removeFormErrors();
    $("#productId").remove();
}

function showLoadingState() {
    $('.div-loading').removeClass('div-hide');
    $('.div-result').addClass('div-hide');
}

function hideLoadingState() {
    $('.div-loading').addClass('div-hide');
    $('.div-result').removeClass('div-hide');
}

function displayEditError(message) {
    $("#edit-product-messages").html(
        '<div class="alert alert-danger">'+
        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ message +
        '</div>'
    );
}

function removeProduct(productId) {
    if(!productId) return;

    $("#removeProductBtn").unbind('click').bind('click', function() {
        $.ajax({
            url: 'php_action/removeProduct.php',
            type: 'post',
            data: {
                productId: productId,
                csrf_token: $('input[name="csrf_token"]').val()
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $("#removeProductModal").modal('hide');
                    manageProductTable.ajax.reload(null, true);
                    $('.remove-messages').html(
                        '<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>'
                    );
                } else {
                    $('.removeProductMessages').html(
                        '<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Error removing product:', error);
                $('.removeProductMessages').html(
                    '<div class="alert alert-danger">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Failed to remove product. Please try again.'+
                    '</div>'
                );
            }
        });
    });
}

function clearForm(oForm) {
    // var frm_elements = oForm.elements;　　　　　　　　　　　　　　　
    // console.log(frm_elements);
    // 	for(i=0;i<frm_elements.length;i++) {
    // 		field_type = frm_elements[i].type.toLowerCase();　　　　　　　　　　　　　　　
    // 		switch (field_type) {
    // 	    case "text":
    // 	    case "password":
    // 	    case "textarea":
    // 	    case "hidden":
    // 	    case "select-one":　　    
    // 	      frm_elements[i].value = "";
    // 	      break;
    // 	    case "radio":
    // 	    case "checkbox":　　    
    // 	      if (frm_elements[i].checked)
    // 	      {
    // 	          frm_elements[i].checked = false;
    // 	      }
    // 	      break;
    // 	    case "file": 
    // 	    	if(frm_elements[i].options) {
    // 	    		frm_elements[i].options= false;
    // 	    	}
    // 	    default:
    // 	        break;
    //     } // /switch
    // 	} // for
}

function showAddProductModal() {
    // code to show add product modal
}

function showEditProductModal() {
    // code to show edit product modal
}

function showRemoveProductModal() {
    // code to show remove product modal
}