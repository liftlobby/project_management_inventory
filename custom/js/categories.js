var manageCategoriesTable;

$(document).ready(function() {
    // Initialize DataTable
    manageCategoriesTable = $("#manageCategoriesTable").DataTable({
        'ajax': 'php_action/fetchCategories.php',
        'order': []
    });

    // Initialize edit modal with proper options
    $('#editCategoriesModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    // Add categories form
    $("#submitCategoriesForm").unbind('submit').bind('submit', function() {
        // Remove error messages
        $(".text-danger").remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        var categoriesName = $("#categoriesName").val();
        var categoriesStatus = $("#categoriesStatus").val();

        if(categoriesName == "") {
            $("#categoriesName").after('<p class="text-danger">Category Name field is required</p>');
            $('#categoriesName').closest('.form-group').addClass('has-error');
        } else {
            $('#categoriesName').closest('.form-group').addClass('has-success');
        }

        if(categoriesStatus == "") {
            $("#categoriesStatus").after('<p class="text-danger">Category Status field is required</p>');
            $('#categoriesStatus').closest('.form-group').addClass('has-error');
        } else {
            $('#categoriesStatus').closest('.form-group').addClass('has-success');
        }

        if(categoriesName && categoriesStatus) {
            var form = $(this);
            // Button loading state
            $("#createCategoriesBtn").button('loading');

            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success:function(response) {
                    // Button loading state
                    $("#createCategoriesBtn").button('reset');

                    if(response.success == true) {
                        // Reset the form
                        $("#submitCategoriesForm")[0].reset();
                        $("html, body, div.modal, div.modal-content, div.modal-body").animate({scrollTop: '0'}, 100);
                        
                        // Display success message
                        $('#add-categories-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        // Reload the manage categories table
                        manageCategoriesTable.ajax.reload(null, false);

                        // Close modal after success
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                $('#addCategoriesModal').modal('hide');
                            });
                        });
                    }
                }
            });   
        }
        return false;
    });

    // Edit categories form
    $("#editCategoriesForm").unbind('submit').bind('submit', function() {
        // Remove error messages
        $(".text-danger").remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        var categoriesName = $("#editCategoriesName").val();
        var categoriesStatus = $("#editCategoriesStatus").val();

        if(categoriesName == "") {
            $("#editCategoriesName").after('<p class="text-danger">Category Name field is required</p>');
            $('#editCategoriesName').closest('.form-group').addClass('has-error');
        } else {
            $('#editCategoriesName').closest('.form-group').addClass('has-success');
        }

        if(categoriesStatus == "") {
            $("#editCategoriesStatus").after('<p class="text-danger">Category Status field is required</p>');
            $('#editCategoriesStatus').closest('.form-group').addClass('has-error');
        } else {
            $('#editCategoriesStatus').closest('.form-group').addClass('has-success');
        }

        if(categoriesName && categoriesStatus) {
            var form = $(this);
            // Button loading state
            $("#editCategoriesBtn").button('loading');

            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success:function(response) {
                    // Button loading state
                    $("#editCategoriesBtn").button('reset');

                    if(response.success == true) {
                        // Display success message
                        $('#edit-categories-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        // Reload the manage categories table
                        manageCategoriesTable.ajax.reload(null, false);

                        // Close modal after success
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                $('#editCategoriesModal').modal('hide');
                            });
                        });
                    }
                }
            });   
        }
        return false;
    });
});

// Edit categories function
function editCategories(categoriesId = null) {
    if(categoriesId) {
        $('#categoriesId').remove();
        // Remove error messages
        $('.text-danger').remove();
        $('.form-group').removeClass('has-error').removeClass('has-success');

        $.ajax({
            url: 'php_action/fetchSelectedCategories.php',
            type: 'post',
            data: {categoriesId: categoriesId},
            dataType: 'json',
            success:function(response) {
                $("#editCategoriesName").val(response.categories_name);
                $("#editCategoriesStatus").val(response.categories_active);
                // Add hidden categories id
                $(".editCategoriesFooter").after('<input type="hidden" name="categoriesId" id="categoriesId" value="'+response.categories_id+'" />');

                // Show modal
                $('#editCategoriesModal').modal('show');
            }
        });
    }
}

// Remove categories function
function removeCategories(categoriesId = null) {
    if(categoriesId) {
        $('#removeCategoriesId').remove();
        $.ajax({
            url: 'php_action/fetchSelectedCategories.php',
            type: 'post',
            data: {categoriesId: categoriesId},
            dataType: 'json',
            success:function(response) {
                $('.removeCategoriesFooter').after('<input type="hidden" name="removeCategoriesId" id="removeCategoriesId" value="'+response.categories_id+'" />');
                $("#removeCategoriesBtn").unbind('click').bind('click', function() {
                    $.ajax({
                        url: 'php_action/removeCategories.php',
                        type: 'post',
                        data: {categoriesId : categoriesId},
                        dataType: 'json',
                        success:function(response) {
                            if(response.success == true) {
                                $('#removeCategoriesModal').modal('hide');
                                // Reload the manage categories table
                                manageCategoriesTable.ajax.reload(null, false);
                                $('.remove-messages').html('<div class="alert alert-success">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                    '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                '</div>');
                            }
                        }
                    });
                });
                $('#removeCategoriesModal').modal('show');
            }
        });
    }
}