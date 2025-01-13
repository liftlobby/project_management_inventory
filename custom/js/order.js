var manageOrderTable;

$(document).ready(function() {
    $("#paymentPlace").change(function(){
        if($("#paymentPlace").val() == 2) {
            $(".gst").text("IGST 18%");
        } else {
            $(".gst").text("GST 18%");    
        }
    });

    // Initialize order table only if it hasn't been initialized yet
    if (!$.fn.DataTable.isDataTable('#manageOrderTable')) {
        manageOrderTable = $("#manageOrderTable").DataTable({
            'ajax': 'php_action/fetchOrder.php',
            'order': [],
            'columnDefs': [{
                "targets": [0, 1, 2, 3, 4],
                "orderable": false
            }]
        });
    } else {
        manageOrderTable = $('#manageOrderTable').DataTable();
    }

    // Edit order button click
    $(document).on('click', '.editOrder', function(e) {
        e.preventDefault();
        var orderId = $(this).data('id');
        console.log("Edit clicked for order ID:", orderId);
        
        // Clear previous messages
        $('#edit-order-messages').html('');
        
        // Reset form fields
        $('#editOrderForm')[0].reset();
        
        // Fetch order data
        $.ajax({
            url: 'php_action/fetchSelectedOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                console.log("Fetched order data:", response);
                
                if(response.success === true) {
                    // Fill form with order data
                    $('#editOrderId').val(response.orderId);
                    $('#orderDate').val(response.orderDate);
                    $('#editClientName').val(response.clientName);
                    $('#editClientContact').val(response.clientContact);
                    $('#editOrderStatus').val(response.orderStatus);
                    $('#editTotalAmount').val(response.totalAmount);
                    
                    // Show modal
                    $('#editOrderModal').modal('show');
                } else {
                    // Show error message
                    $('#edit-order-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        response.messages+
                    '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error details:");
                console.error("Status:", status);
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                
                // Show error message
                $('#edit-order-messages').html('<div class="alert alert-danger">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    'Failed to fetch order details. Please try again.'+
                '</div>');
            }
        });
    });

    // Edit order form submit
    $("#editOrderForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        // Clear previous messages
        $('#edit-order-messages').html('');
        
        // Remove existing error classes
        $('.form-group').removeClass('has-error').removeClass('has-success');
        $('.text-danger').remove();
        
        // Get form values
        var orderDate = $("#orderDate").val();
        var clientName = $("#editClientName").val();
        var clientContact = $("#editClientContact").val();
        var orderStatus = $("#editOrderStatus").val();
        var totalAmount = $("#editTotalAmount").val();

        // Validate form fields
        var isValid = true;
        
        if(!orderDate) {
            $("#orderDate").after('<p class="text-danger">Order Date is required</p>');
            $("#orderDate").closest('.form-group').addClass('has-error');
            isValid = false;
        }
        
        if(!clientName) {
            $("#editClientName").after('<p class="text-danger">Client Name is required</p>');
            $("#editClientName").closest('.form-group').addClass('has-error');
            isValid = false;
        }
        
        if(!clientContact) {
            $("#editClientContact").after('<p class="text-danger">Client Contact is required</p>');
            $("#editClientContact").closest('.form-group').addClass('has-error');
            isValid = false;
        }
        
        if(!orderStatus) {
            $("#editOrderStatus").after('<p class="text-danger">Order Status is required</p>');
            $("#editOrderStatus").closest('.form-group').addClass('has-error');
            isValid = false;
        }
        
        if(!totalAmount) {
            $("#editTotalAmount").after('<p class="text-danger">Total Amount is required</p>');
            $("#editTotalAmount").closest('.form-group').addClass('has-error');
            isValid = false;
        }

        if(isValid) {
            // Get CSRF token
            var csrf_token = $('input[name="csrf_token"]').val();
            console.log("CSRF Token:", csrf_token);
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize() + "&csrf_token=" + csrf_token,
                dataType: 'json',
                success: function(response) {
                    console.log("Server response:", response);
                    
                    if(response.success) {
                        // Show success message
                        $("#edit-order-messages").html(
                            '<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                            '</div>'
                        );
                        
                        // Reset form
                        $("#editOrderForm")[0].reset();
                        
                        // Close modal
                        $("#editOrderModal").modal('hide');
                        
                        // Refresh table
                        manageOrderTable.ajax.reload(null, false);
                        
                    } else {
                        $("#edit-order-messages").html(
                            '<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Ajax error:", error);
                    $("#edit-order-messages").html(
                        '<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Error updating order. Please try again.'+
                        '</div>'
                    );
                }
            });
        }
        
        return false;
    });

    // Print order button click handler
    $(document).on('click', '.printOrder', function(e) {
        e.preventDefault();
        var orderId = $(this).data('id');
        console.log("Print clicked for order ID:", orderId);
        
        $.ajax({
            url: 'php_action/printOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                console.log("Print response:", response);
                
                if(response.success === true) {
                    // Create a new window for printing
                    var printWindow = window.open('', '_blank');
                    printWindow.document.write('<!DOCTYPE html><html><head>');
                    printWindow.document.write('<title>Order Summary</title>');
                    printWindow.document.write('</head><body>');
                    printWindow.document.write(response.html);
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                    
                    // Wait for content to load then print
                    printWindow.onload = function() {
                        printWindow.print();
                        // Close the window after printing (optional)
                        //printWindow.close();
                    };
                } else {
                    alert('Error printing order: ' + response.messages);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error details:");
                console.error("Status:", status);
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                alert("Error printing order. Please try again.");
            }
        });
    });

    // Remove order function
    window.removeOrder = function(orderId) {
        if(orderId) {
            $("#removeOrderBtn").unbind('click').bind('click', function() {
                // Get CSRF token
                var csrf_token = $('input[name="csrf_token"]').val();
                console.log("CSRF Token:", csrf_token);
                
                $.ajax({
                    url: 'php_action/removeOrder.php',
                    type: 'post',
                    data: {
                        orderId: orderId,
                        csrf_token: csrf_token
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            $("#removeOrderModal").modal('hide');
                            manageOrderTable.ajax.reload(null, false);
                            $('.remove-messages').html(
                                '<div class="alert alert-success">'+
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                '</div>'
                            );
                        }
                    }
                });
            });
        }
    };
});
