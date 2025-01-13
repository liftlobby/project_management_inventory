var manageBrandTable;

$(document).ready(function() {
    // top bar active
    $('#navBrand').addClass('active');
    
    // manage brand table
    manageBrandTable = $("#manageBrandTable").DataTable({
        'ajax': 'php_action/fetchBrand.php',
        'order': []        
    });

    // submit brand form function
    $("#submitBrandForm").unbind('submit').bind('submit', function() {
        // remove the error text
        $(".text-danger").remove();
        // remove the form error
        $('.form-group').removeClass('has-error').removeClass('has-success');            

        var brandName = $("#brandName").val();
        var brandStatus = $("#brandStatus").val();

        if(brandName == "") {
            $("#brandName").after('<p class="text-danger">Brand Name field is required</p>');
            $('#brandName').closest('.form-group').addClass('has-error');
        } else {
            $("#brandName").find('.text-danger').remove();
            $("#brandName").closest('.form-group').addClass('has-success');      
        }

        if(brandStatus == "") {
            $("#brandStatus").after('<p class="text-danger">Status field is required</p>');
            $('#brandStatus').closest('.form-group').addClass('has-error');
        } else {
            $("#brandStatus").find('.text-danger').remove();
            $("#brandStatus").closest('.form-group').addClass('has-success');      
        }

        if(brandName && brandStatus) {
            var form = $(this);
            $("#createBrandBtn").button('loading');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $("#createBrandBtn").button('reset');
                    if(response.success == true) {
                        manageBrandTable.ajax.reload(null, false);                        

                        $("#submitBrandForm")[0].reset();
                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        $("#add-brand-messages").html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert
                    }
                },
                error: function(xhr, status, error) {
                    $("#createBrandBtn").button('reset');
                    $("#add-brand-messages").html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                    '</div>');
                }
            });    
        }
        return false;
    });
});

// edit brands function
function editBrands(brandId = null) {
    if(brandId) {
        $('#editBrandModal').modal('show');
        
        // Get CSRF token from the edit form
        var csrfToken = $('#editBrandForm input[name="csrf_token"]').val();
        
        // Clear previous data
        $('#brandId').remove();
        $('.text-danger').remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        // Show loading state
        $('.modal-loading').removeClass('div-hide');
        $('.edit-brand-result').addClass('div-hide');
        $('.editBrandFooter').addClass('div-hide');

        $.ajax({
            url: 'php_action/fetchSelectedBrand.php',
            type: 'post',
            data: {
                brandId: brandId
            },
            dataType: 'json',
            success: function(response) {
                // Hide loading state
                $('.modal-loading').addClass('div-hide');
                
                if(!response.success) {
                    $('.edit-brand-result').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                    '</div>').removeClass('div-hide');
                    return;
                }

                // Show edit form
                $('.edit-brand-result').removeClass('div-hide');
                $('.editBrandFooter').removeClass('div-hide');

                // Populate form with data
                $('#editBrandName').val(response.brand_name);
                $('#editBrandStatus').val(response.brand_active);
                
                // Add brandId to the form
                $(".editBrandFooter").after('<input type="hidden" name="brandId" id="brandId" value="'+response.brand_id+'" />');

                // Handle form submission
                $('#editBrandForm').unbind('submit').bind('submit', function() {
                    var form = $(this);
                    $(".text-danger").remove();
                    $('.form-group').removeClass('has-error').removeClass('has-success');

                    var brandName = $('#editBrandName').val();
                    var brandStatus = $('#editBrandStatus').val();

                    if(brandName == "") {
                        $("#editBrandName").after('<p class="text-danger">Brand Name field is required</p>');
                        $('#editBrandName').closest('.form-group').addClass('has-error');
                    } else {
                        $("#editBrandName").find('.text-danger').remove();
                        $("#editBrandName").closest('.form-group').addClass('has-success');      
                    }

                    if(brandStatus == "") {
                        $("#editBrandStatus").after('<p class="text-danger">Status field is required</p>');
                        $('#editBrandStatus').closest('.form-group').addClass('has-error');
                    } else {
                        $("#editBrandStatus").find('.text-danger').remove();
                        $("#editBrandStatus").closest('.form-group').addClass('has-success');      
                    }

                    if(brandName && brandStatus) {
                        $('#editBrandBtn').button('loading');

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            dataType: 'json',
                            success: function(response) {
                                $('#editBrandBtn').button('reset');
                                
                                if(response.success == true) {
                                    manageBrandTable.ajax.reload(null, false);                                                                              
                                    $(".text-danger").remove();
                                    $('.form-group').removeClass('has-error').removeClass('has-success');
                                    $('#editBrandModal').modal('hide');
                                    
                                    $('.edit-brand-messages').html('<div class="alert alert-success">'+
                                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                    '</div>');

                                    $(".alert-success").delay(500).show(10, function() {
                                        $(this).delay(3000).hide(10, function() {
                                            $(this).remove();
                                        });
                                    });
                                } else {
                                    $('.edit-brand-messages').html('<div class="alert alert-danger">'+
                                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                                    '</div>');
                                }
                            },
                            error: function(xhr, status, error) {
                                $('#editBrandBtn').button('reset');
                                console.error('Error:', error);
                                console.error('Status:', status);
                                console.error('Response:', xhr.responseText);
                                
                                $('.edit-brand-messages').html('<div class="alert alert-danger">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                                '</div>');
                            }
                        });    
                    }
                    return false;
                });
            },
            error: function(xhr, status, error) {
                $('.modal-loading').addClass('div-hide');
                console.error('Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                $('.edit-brand-result').html('<div class="alert alert-danger">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred while fetching brand data.'+
                '</div>').removeClass('div-hide');
            }
        });
    }
}

function removeBrands(brandId = null) {
    if(brandId) {
        $('#removeMemberModal').modal('show');
        
        $('#removeBrandBtn').unbind('click').bind('click', function() {
            $(this).button('loading');

            $.ajax({
                url: 'php_action/removeBrand.php',
                type: 'post',
                data: {
                    brandId: brandId,
                    csrf_token: $('input[name="csrf_token"]').val()
                },
                dataType: 'json',
                success: function(response) {
                    $('#removeBrandBtn').button('reset');
                    
                    if(response.success == true) {
                        $('#removeMemberModal').modal('hide');
                        manageBrandTable.ajax.reload(null, false);
                        
                        $('.remove-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        });
                    } else {
                        $('.remove-messages').html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#removeBrandBtn').button('reset');
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    $('.remove-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                    '</div>');
                }
            });
        });
    }
}