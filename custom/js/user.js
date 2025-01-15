var manageUserTable;

// remove user function
function removeUser(userid = null) {
    if(userid) {
        // Show modal
        $("#removeUserModal").modal('show');
        
        // remove user button clicked
        $("#removeUserBtn").unbind('click').bind('click', function() {
            // loading remove button
            $("#removeUserBtn").button('loading');
            $.ajax({
                url: 'php_action/removeUser.php',
                type: 'post',
                data: {
                    userid: userid,
                    csrf_token: $("input[name='csrf_token']").val()
                },
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
                        
                        // remove the messages
                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert
                    } else {
                        $(".removeUserMessages").html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $("#removeUserBtn").button('reset');
                    $(".removeUserMessages").html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                    '</div>');
                }
            }); // /ajax function to remove the user
        }); // /remove user btn clicked
    } else {
        alert('error!! Refresh the page');
    }
} // /remove user function

// Bind remove user function to window
window.removeUser = removeUser;

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
            $("#userName").closest('.form-group').addClass('has-success');
        }

        if(upassword == "") {
            $("#upassword").after('<p class="text-danger">Password is required</p>');
            $('#upassword').closest('.form-group').addClass('has-error');
            isValid = false;
        } else if(!isValidPassword(upassword)) {
            $("#upassword").after('<p class="text-danger">Password does not meet requirements</p>');
            $('#upassword').closest('.form-group').addClass('has-error');
            isValid = false;
        } else {
            $("#upassword").closest('.form-group').addClass('has-success');
        }

        if(isValid) {
            // submit loading button
            $("#createUserBtn").button('loading');

            var form = $(this);
            $.ajax({
                url : form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success:function(response) {
                    // submit loading button
                    $("#createUserBtn").button('reset');
                    
                    if(response.success == true) {
                        // reload the manage member table 
                        manageUserTable.ajax.reload(null, false);                  

                        // reset the form text
                        $("#submitUserForm")[0].reset();
                        // remove the error text
                        $(".text-danger").remove();
                        // remove the form error
                        $('.form-group').removeClass('has-error').removeClass('has-success');

                        $("#add-user-messages").html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert
                    } else {
                        $("#add-user-messages").html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $("#createUserBtn").button('reset');
                    $("#add-user-messages").html('<div class="alert alert-danger">'+
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
            // remove text-error 
            $(".text-danger").remove();
            // remove from-group error
            $(".form-group").removeClass('has-error').removeClass('has-success');
            // modal spinner
            $('.div-loading').removeClass('div-hide');
            // modal div
            $('.div-result').addClass('div-hide');

            $.ajax({
                url: 'php_action/fetchSelectedUser.php',
                type: 'post',
                data: {
                    userid: userid,
                    csrf_token: getCsrfToken()
                },
                dataType: 'json',
                success:function(response) {
                    // modal spinner
                    $('.div-loading').addClass('div-hide');
                    // modal div
                    $('.div-result').removeClass('div-hide');

                    // set the user name
                    $("#editUserName").val(response.username);
                    // add hidden userid 
                    $("#editUserId").val(userid);

                    // submit edit user form
                    $("#editUserForm").unbind('submit').bind('submit', function() {
                        // form validation
                        var username = $("#editUserName").val();
                        var password = $("#editPassword").val();

                        if(username == "") {
                            $("#editUserName").after('<p class="text-danger">Username is required</p>');
                            $('#editUserName').closest('.form-group').addClass('has-error');
                        } else {
                            // remove error text field
                            $("#editUserName").find('.text-danger').remove();
                            // success out for form 
                            $("#editUserName").closest('.form-group').addClass('has-success');   
                        }

                        if(password && !isValidPassword(password)) {
                            $("#editPassword").after('<p class="text-danger">Password does not meet requirements</p>');
                            $('#editPassword').closest('.form-group').addClass('has-error');
                            return false;
                        }

                        if(username) {
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
                                        // reload the manage member table 
                                        manageUserTable.ajax.reload(null, false);                  

                                        // remove the error text
                                        $(".text-danger").remove();
                                        // remove the form error
                                        $('.form-group').removeClass('has-error').removeClass('has-success');

                                        $("#edit-user-messages").html('<div class="alert alert-success">'+
                                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                        '</div>');

                                        $(".alert-success").delay(500).show(10, function() {
                                            $(this).delay(3000).hide(10, function() {
                                                $(this).remove();
                                            });
                                        }); // /.alert                          
                                    } else {
                                        $("#edit-user-messages").html('<div class="alert alert-danger">'+
                                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                                        '</div>');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    $('#editUserBtn').button('reset');
                                    $("#edit-user-messages").html('<div class="alert alert-danger">'+
                                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
                                    '</div>');
                                }
                            }); // /ajax
                        } // /if
                        return false;
                    }); // /submit edit user form
                } // /success
            }); // /fetch selected user info
        } else {
            alert("Error : Refresh the page again");
        }
    }

    // Bind edit user function to window
    window.editUser = editUser;

    // Password validation function
    function isValidPassword(password) {
        // Password must be at least 8 characters long and contain:
        // - At least one uppercase letter
        // - At least one lowercase letter
        // - At least one number
        // - At least one special character
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()-_=+{};:,<.>])[A-Za-z\d!@#$%^&*()-_=+{};:,<.>]{8,}$/;
        return passwordRegex.test(password);
    }
}); // /document ready