var manageUserTable;

$(document).ready(function() {
    // top nav bar 
    $('#topNavUser').addClass('active');
    // manage product data table
    manageUserTable = $('#manageUserTable').DataTable({
        'ajax': 'php_action/fetchUser.php',
        'order': []
    });

    // add product modal btn clicked
    $("#addUserModalBtn").unbind('click').bind('click', function() {
        // reset form
        $("#submitUserForm")[0].reset();        
        // remove text-error 
        $(".text-danger").remove();
        $(".alert").remove();
        // remove from-group error
        $(".form-group").removeClass('has-error').removeClass('has-success');

        // Add password requirements help text
        $("#upassword").after('<small class="form-text text-muted password-requirements">Password must contain at least:<br>' +
            '- 8 characters<br>' +
            '- One uppercase letter<br>' +
            '- One lowercase letter<br>' +
            '- One number<br>' +
            '- One special character (!@#$%^&*()-_=+{};:,<.>)</small>');
    });   

    // submit form
    $("#submitUserForm").unbind('submit').bind('submit', function() {
        // form validation
        var userName = $("#userName").val();
        var upassword = $("#upassword").val();
        var isValid = true;

        // Clear previous error messages
        $(".text-danger").remove();
        $(".alert").remove();
        $(".form-group").removeClass('has-error').removeClass('has-success');

        if(userName == "") {
            $("#userName").after('<p class="text-danger">Username is required</p>');
            $('#userName').closest('.form-group').addClass('has-error');
            isValid = false;
        } else {
            $('#userName').closest('.form-group').addClass('has-success');      
        }

        if(upassword == "") {
            $("#upassword").after('<p class="text-danger">Password is required</p>');
            $('#upassword').closest('.form-group').addClass('has-error');
            isValid = false;
        } else {
            $('#upassword').closest('.form-group').addClass('has-success');      
        }

        if(isValid) {
            // submit loading button
            $("#createUserBtn").button('loading');

            var form = $(this);
            var formData = new FormData(this);

            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    // Always reset the button first
                    $("#createUserBtn").button('reset');
                    
                    if(response.success == true) {
                        // reset form
                        $("#submitUserForm")[0].reset();
                        // remove error styles
                        $(".text-danger").remove();
                        $(".form-group").removeClass('has-error').removeClass('has-success');
                        
                        // success message
                        $('#add-user-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        // reload the manage member table
                        manageUserTable.ajax.reload(null, true);

                        // Remove password requirements
                        $(".password-requirements").remove();
                    } else {
                        // error message
                        $('#add-user-messages').html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    // Reset button state
                    $("#createUserBtn").button('reset');
                    
                    // Show error message
                    $('#add-user-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                    '</div>');
                }
            }); // /ajax
        } else {
            return false;
        }

        return false;
    }); // /submit form
}); // /document ready

function editUser(userid = null) {
    if(userid) {
        // remove hidden user id text
        $('#userId').remove();

        // remove the error 
        $('.text-danger').remove();
        // remove the form-error
        $('.form-group').removeClass('has-error').removeClass('has-success');

        // modal loading
        $('.modal-loading').removeClass('div-hide');
        // modal result
        $('.edit-user-result').addClass('div-hide');
        // modal footer
        $('.modal-footer').addClass('div-hide');

        $.ajax({
            url: 'php_action/fetchSelectedUser.php',
            type: 'post',
            data: {userid : userid},
            dataType: 'json',
            success:function(response) {
                // modal loading
                $('.modal-loading').addClass('div-hide');
                // modal result
                $('.edit-user-result').removeClass('div-hide');
                // modal footer
                $('.modal-footer').removeClass('div-hide');

                // setting the user name value 
                $('#editUserName').val(response.username);
                // setting the user email value      
                $('#editUemail').val(response.email);
                // user id 
                $(".editUserFooter").after('<input type="hidden" name="userId" id="userId" value="'+response.user_id+'" />');

                // update user form 
                $('#editUserForm').unbind('submit').bind('submit', function() {

                    // remove the error text
                    $(".text-danger").remove();
                    // remove the form error
                    $('.form-group').removeClass('has-error').removeClass('has-success');

                    var userName = $('#editUserName').val();
                    var uemail = $('#editUemail').val();

                    if(userName == "") {
                        $("#editUserName").after('<p class="text-danger">User Name field is required</p>');
                        $('#editUserName').closest('.form-group').addClass('has-error');
                    } else {
                        // remove error text field
                        $("#editUserName").find('.text-danger').remove();
                        // success out for form 
                        $("#editUserName").closest('.form-group').addClass('has-success');   
                    }

                    if(uemail == "") {
                        $("#editUemail").after('<p class="text-danger">Email field is required</p>');
                        $('#editUemail').closest('.form-group').addClass('has-error');
                    } else {
                        // remove error text field
                        $("#editUemail").find('.text-danger').remove();
                        // success out for form 
                        $("#editUemail").closest('.form-group').addClass('has-success');   
                    }

                    if(userName && uemail) {
                        var form = $(this);

                        // submit btn
                        $('#editUserBtn').button('loading');

                        $.ajax({
                            url: form.attr('action'),
                            type: form.attr('method'),
                            data: form.serialize(),
                            dataType: 'json',
                            success:function(response) {

                                if(response.success == true) {
                                    console.log(response);
                                    // submit btn
                                    $('#editUserBtn').button('reset');

                                    // remove the error text
                                    $(".text-danger").remove();
                                    // remove the form error
                                    $('.form-group').removeClass('has-error').removeClass('has-success');

                                    $('#edit-user-messages').html('<div class="alert alert-success">'+
                                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                    '</div>');
                                   
                                    $(".alert-success").delay(500).show(10, function() {
                                        $(this).delay(3000).hide(10, function() {
                                            $(this).remove();
                                        });
                                    }); // /.alert

                                    manageUserTable.ajax.reload(null, true);

                                } else {
                                    console.log(response);
                                    // submit btn
                                    $('#editUserBtn').button('reset'); 
                                }
                            } // /success
                        }); // /ajax
                    } // /if

                    return false;
                }); // /update user form

            } // /success
        }); // /fetch selected user info

    } else {
        alert('error!! Refresh the page');
    }
} // /edit user function

function removeUser(userid = null) {
    if(userid) {
        // remove user button clicked
        $("#removeUserBtn").unbind('click').bind('click', function() {
            // loading remove button
            $("#removeUserBtn").button('loading');
            $.ajax({
                url: 'php_action/removeUser.php',
                type: 'post',
                data: {userid: userid},
                dataType: 'json',
                success:function(response) {
                    // loading remove button
                    $("#removeUserBtn").button('reset');
                    if(response.success == true) {
                        // remove user modal
                        $("#removeUserModal").modal('hide');

                        // update the user table
                        manageUserTable.ajax.reload(null, false);

                        // remove success messages
                        $(".remove-messages").html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                        
                        // remove the mesages
                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert
                    } else {

                    } // /else
                } // /response messages
            }); // /ajax function to remove the user
        }); // /remove user btn clicked
    } else {
        alert('error!! Refresh the page');
    }
} // /remove user function