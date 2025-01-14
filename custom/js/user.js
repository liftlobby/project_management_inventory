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

    // Function to get CSRF token
    function getCsrfToken() {
        return $("input[name='csrf_token']").val();
    }

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

        if(isValid === true) {
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if(response.success == true) {
                        manageUserTable.ajax.reload(null, false);                  
                        $("#submitUserForm")[0].reset();
                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');
                        $('#add-user-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                            '</div>');

                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    $('#add-user-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                        '</div>');
                }
            });
        }
        return false;
    });

    // edit user function
    function editUser(userid = null) {
        if(userid) {
            // remove error messages
            $(".text-danger").remove();
            $(".alert").remove();
            // remove from-group error
            $(".form-group").removeClass('has-error').removeClass('has-success');

            $.ajax({
                url: 'php_action/fetchSelectedUser.php',
                type: 'post',
                data: {
                    userid: userid,
                    csrf_token: getCsrfToken()
                },
                dataType: 'json',
                success: function(response) {
                    console.log("User data:", response);
                    if(response.success === true) {
                        $("#editUserName").val(response.username);
                        $("#editUemail").val(response.email);
                        
                        // Store user ID for update
                        $(".editUserFooter").after('<input type="hidden" name="userId" id="userId" value="'+response.user_id+'" />');

                        // show modal
                        $("#editUserModal").modal('show');
                    } else {
                        $('#edit-user-messages').html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                            '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching user:", error);
                    console.error("Response:", xhr.responseText);
                    $('#edit-user-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Failed to fetch user data'+
                        '</div>');
                }
            });
        }
    }

    // Bind edit user function to window
    window.editUser = editUser;

    // edit user form submit
    $("#editUserForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success === true) {
                    manageUserTable.ajax.reload(null, false);
                    $("#editUserModal").modal('hide');
                    $('#edit-user-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                } else {
                    $('#edit-user-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error updating user:", error);
                $('#edit-user-messages').html('<div class="alert alert-danger">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Failed to update user'+
                    '</div>');
            }
        });
        return false;
    });

    // remove user function
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
}); // /document ready