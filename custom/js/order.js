// Global functions for order management
function addRow() {
    var tableLength = $("#productTable tbody tr").length;
    var tableRow;
    var arrayNumber;
    var count;

    if(tableLength > 0) {		
        tableRow = $("#productTable tbody tr:last").attr('id');
        arrayNumber = $("#productTable tbody tr:last").attr('class');
        count = tableRow.substring(3);	
        count = Number(count) + 1;
        arrayNumber = Number(arrayNumber) + 1;					
    } else {
        // no table row
        count = 1;
        arrayNumber = 0;
    }

    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success:function(response) {
            var tr = '<tr id="row'+count+'" class="'+arrayNumber+'">'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')" >'+
                          '<option value="">~~SELECT~~</option>';
                          // console.log(response);
                          $.each(response, function(index, value) {
                              tr += '<option value="'+value[0]+'">'+value[1]+'</option>';							
                          });
                                                    
            tr += '</select>'+
                    '</div>'+
                  '</td>'+
                  '<td style="padding-left:20px;">'+
                      '<input type="text" name="rate[]" id="rate'+count+'" autocomplete="off" disabled="true" class="form-control" />'+
                      '<input type="hidden" name="rateValue[]" id="rateValue'+count+'" autocomplete="off" class="form-control" />'+
                  '</td style="padding-left:20px;">'+
                  '<td style="padding-left:20px;">'+
                      '<div class="form-group">'+
                      '<input type="number" name="quantity[]" id="quantity'+count+'" onkeyup="getTotal('+count+')" autocomplete="off" class="form-control" min="1" />'+
                      '</div>'+
                  '</td>'+
                  '<td style="padding-left:20px;">'+
                      '<input type="text" name="total[]" id="total'+count+'" autocomplete="off" class="form-control" disabled="true" />'+
                      '<input type="hidden" name="totalValue[]" id="totalValue'+count+'" autocomplete="off" class="form-control" />'+
                  '</td>'+
                  '<td>'+
                      '<button class="btn btn-danger removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
                  '</td>'+
              '</tr>';

            if(tableLength > 0) {							
                $("#productTable tbody tr:last").after(tr);
            } else {				
                $("#productTable tbody").append(tr);
            }		

        } // /success
    });	// get the product data
} // /add row

// Get Product Data
function getProductData(row = null) {
    if(row) {
        var productId = $("#productName"+row).val();		
        
        if(productId == "") {
            $("#rate"+row).val("");
            $("#rateValue"+row).val("");

            $("#quantity"+row).val("");           

            $("#total"+row).val("");
            $("#totalValue"+row).val("");
        } else {
            $.ajax({
                url: 'php_action/fetchSelectedProduct.php',
                type: 'post',
                data: {productId: productId},
                dataType: 'json',
                success:function(response) {
                    // setting the rate value into the rate input field
                    $("#rate"+row).val(response.rate);
                    $("#rateValue"+row).val(response.rate);

                    $("#quantity"+row).val(1);
                    $("#available_quantity"+row).text(response.quantity);

                    var total = Number(response.rate) * 1;
                    total = total.toFixed(2);
                    $("#total"+row).val(total);
                    $("#totalValue"+row).val(total);
                } // /success
            }); // /ajax function to fetch the product data	
        }
    }
} // /select on product data

// Calculate Total
function getTotal(row = null) {
    if(row) {
        var total = Number($("#rate"+row).val()) * Number($("#quantity"+row).val());
        total = total.toFixed(2);
        $("#total"+row).val(total);
        $("#totalValue"+row).val(total);
    } else {
        alert('no row !! please refresh the page');
    }
} // /get total

// Remove Product Row
function removeProductRow(row = null) {
    if(row) {
        $("#row"+row).remove();
    }
} // /remove product row

// Add row to edit order table
function addEditRow() {
    var tableLength = $("#editProductTable tbody tr").length;
    var tableRow;
    var arrayNumber;
    var count;

    if(tableLength > 0) {		
        tableRow = $("#editProductTable tbody tr:last").attr('id');
        arrayNumber = $("#editProductTable tbody tr:last").attr('class');
        count = tableRow.substring(3);	
        count = Number(count) + 1;
        arrayNumber = Number(arrayNumber) + 1;					
    } else {
        // no table row
        count = 1;
        arrayNumber = 0;
    }

    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success:function(response) {
            var tr = '<tr id="row'+count+'" class="'+arrayNumber+'">'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<select class="form-control" name="editProductName[]" id="editProductName'+count+'" onchange="getEditProductData('+count+')" >'+
                          '<option value="">~~SELECT~~</option>';
                          $.each(response, function(index, value) {
                              tr += '<option value="'+value[0]+'">'+value[1]+'</option>';							
                          });
                                                    
            tr += '</select>'+
                    '</div>'+
                  '</td>'+
                  '<td>'+
                      '<input type="text" name="editRate[]" id="editRate'+count+'" autocomplete="off" disabled="true" class="form-control" />'+
                      '<input type="hidden" name="editRateValue[]" id="editRateValue'+count+'" autocomplete="off" class="form-control" />'+
                  '</td>'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<span id="editAvailable'+count+'">0</span>'+
                      '</div>'+
                  '</td>'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<input type="number" name="editQuantity[]" id="editQuantity'+count+'" onchange="getEditTotal('+count+')" class="form-control" min="1" />'+
                      '</div>'+
                  '</td>'+
                  '<td>'+
                      '<input type="text" name="editTotal[]" id="editTotal'+count+'" autocomplete="off" class="form-control" disabled="true" />'+
                      '<input type="hidden" name="editTotalValue[]" id="editTotalValue'+count+'" autocomplete="off" class="form-control" />'+
                  '</td>'+
                  '<td>'+
                      '<button class="btn btn-danger removeEditProductRowBtn" type="button" onclick="removeEditProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
                  '</td>'+
              '</tr>';

            if(tableLength > 0) {							
                $("#editProductTable tbody tr:last").after(tr);
            } else {				
                $("#editProductTable tbody").append(tr);
            }		
        }
    });
}

// Get product data for edit form
function getEditProductData(row) {
    if(row) {
        var productId = $("#editProductName"+row).val();		
        
        if(productId == "") {
            $("#editRate"+row).val("");
            $("#editRateValue"+row).val("");
            $("#editQuantity"+row).val("");
            $("#editAvailable"+row).text("0");
            $("#editTotal"+row).val("");
            $("#editTotalValue"+row).val("");
        } else {
            $.ajax({
                url: 'php_action/fetchSelectedProduct.php',
                type: 'post',
                data: {productId: productId},
                dataType: 'json',
                success:function(response) {
                    $("#editRate"+row).val(response.rate);
                    $("#editRateValue"+row).val(response.rate);
                    $("#editQuantity"+row).val(1);
                    $("#editAvailable"+row).text(response.quantity);

                    var total = Number(response.rate) * 1;
                    total = total.toFixed(2);
                    $("#editTotal"+row).val(total);
                    $("#editTotalValue"+row).val(total);
                    
                    calculateEditTotal();
                }
            });
        }
    }
}

// Calculate total for edit form
function getEditTotal(row) {
    if(row) {
        var total = Number($("#editRate"+row).val()) * Number($("#editQuantity"+row).val());
        total = total.toFixed(2);
        $("#editTotal"+row).val(total);
        $("#editTotalValue"+row).val(total);
        calculateEditTotal();
    } else {
        alert('no row !! please refresh the page');
    }
}

// Calculate grand total for edit form
function calculateEditTotal() {
    var grandTotal = 0;
    $('input[name="editTotal[]"]').each(function() {
        grandTotal += Number($(this).val());
    });
    $("#grandTotal").text(grandTotal.toFixed(2));
}

// Remove product row from edit form
function removeEditProductRow(row) {
    $("#editProductTable tbody tr#row"+row).remove();
    calculateEditTotal();
}

$(document).ready(function() {
    // Initialize the edit modal with proper options
    $('#editOrderModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    // Single handler for edit form submission
    $("#editOrderForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        // Clear previous messages
        $('#edit-order-messages').html('');
        
        // Remove existing error classes
        $('.form-group').removeClass('has-error').removeClass('has-success');
        $('.text-danger').remove();
        
        // Get form values
        var orderDate = $("#editOrderDate").val();
        var clientName = $("#editClientName").val();
        var clientContact = $("#editClientContact").val();
        var orderStatus = $("#editOrderStatus").val();
        var totalAmount = $("#editTotalAmount").val();

        // Validate form fields
        var isValid = true;
        
        if(!orderDate) {
            $("#editOrderDate").after('<p class="text-danger">Order Date is required</p>');
            $("#editOrderDate").closest('.form-group').addClass('has-error');
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
            // Show loading state
            $("#editOrderBtn").button('loading');
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    console.log("Server response:", response);
                    $("#editOrderBtn").button('reset');
                    
                    if(response.success) {
                        // Show success message
                        $('#edit-order-messages').html('<div class="alert alert-success">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');

                        // Reset form
                        $("#editOrderForm")[0].reset();
                        $(".text-danger").remove();
                        $('.form-group').removeClass('has-error').removeClass('has-success');
                        
                        // Reload table
                        manageOrderTable.ajax.reload(null, false);
                        
                        // Close modal after a short delay using requestAnimationFrame
                        var closeModal = function() {
                            $('#editOrderModal').modal('hide');
                        };
                        requestAnimationFrame(function() {
                            requestAnimationFrame(closeModal);
                        });
                    } else {
                        // Show error message
                        $('#edit-order-messages').html('<div class="alert alert-danger">'+
                            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                            '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    $("#editOrderBtn").button('reset');
                    console.error("Error details:", {status: status, error: error, response: xhr.responseText});
                    
                    // Show error message
                    $('#edit-order-messages').html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Failed to save order. Please try again.'+
                    '</div>');
                }
            });
        }
        return false;
    });

    // Edit button click handler
    $("#editOrderBtn").click(function() {
        $("#editOrderForm").submit();
    });

    // Prevent form from auto-submitting
    $("#editOrderForm").submit(function(e) {
        e.preventDefault();
        // Your form submission logic here
    });

    // Edit order button click handler
    $(document).on('click', '.editOrderBtn', function() {
        var orderId = $(this).data('id');
        
        $.ajax({
            url: 'php_action/fetchSelectedOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                // Clear previous data
                $("#editProductTable tbody").empty();
                
                // Set order details
                $("#editOrderId").val(response.order.order_id);
                $("#editOrderDate").val(response.order.order_date);
                $("#editClientName").val(response.order.client_name);
                $("#editClientContact").val(response.order.client_contact);
                $("#editRestockReason").val(response.order.restock_reason);
                $("#editOrderStatus").val(response.order.order_status);

                // Add product rows
                if(response.order_items && response.order_items.length > 0) {
                    response.order_items.forEach(function(item, index) {
                        var count = index + 1;
                        var tr = '<tr id="row'+count+'" class="'+index+'">'+
                            '<td>'+
                                '<div class="form-group">'+
                                '<select class="form-control" name="editProductName[]" id="editProductName'+count+'" onchange="getEditProductData('+count+')" >'+
                                    '<option value="">~~SELECT~~</option>';
                                    response.products.forEach(function(product) {
                                        var selected = (product.product_id == item.product_id) ? 'selected' : '';
                                        tr += '<option value="'+product.product_id+'" '+selected+'>'+product.product_name+'</option>';
                                    });
                        tr += '</select>'+
                                '</div>'+
                            '</td>'+
                            '<td>'+
                                '<input type="text" name="editRate[]" id="editRate'+count+'" value="'+item.rate+'" autocomplete="off" disabled="true" class="form-control" />'+
                                '<input type="hidden" name="editRateValue[]" id="editRateValue'+count+'" value="'+item.rate+'" autocomplete="off" class="form-control" />'+
                            '</td>'+
                            '<td>'+
                                '<div class="form-group">'+
                                '<span id="editAvailable'+count+'">'+item.available_quantity+'</span>'+
                                '</div>'+
                            '</td>'+
                            '<td>'+
                                '<div class="form-group">'+
                                '<input type="number" name="editQuantity[]" id="editQuantity'+count+'" value="'+item.quantity+'" onchange="getEditTotal('+count+')" class="form-control" min="1" />'+
                                '</div>'+
                            '</td>'+
                            '<td>'+
                                '<input type="text" name="editTotal[]" id="editTotal'+count+'" value="'+item.total+'" autocomplete="off" class="form-control" disabled="true" />'+
                                '<input type="hidden" name="editTotalValue[]" id="editTotalValue'+count+'" value="'+item.total+'" autocomplete="off" class="form-control" />'+
                            '</td>'+
                            '<td>'+
                                '<button class="btn btn-danger removeEditProductRowBtn" type="button" onclick="removeEditProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
                            '</td>'+
                        '</tr>';
                        $("#editProductTable tbody").append(tr);
                    });
                }

                // Calculate total
                calculateEditTotal();

                // Show modal
                $("#editOrderModal").modal('show');
            },
            error: function(xhr, status, error) {
                console.error("Error fetching order:", error);
                alert("Error fetching order details. Please try again.");
            }
        });
    });

    // Save edit order changes
    $("#editOrderBtn").click(function() {
        var $btn = $(this);
        $btn.button('loading');
        
        var form = $("#editOrderForm");
        var formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(response) {
                $btn.button('reset');
                if(response.success) {
                    $("#editOrderModal").modal('hide');
                    // Refresh order table
                    $("#manageOrderTable").DataTable().ajax.reload(null, false);
                    // Show success message
                    $(".remove-messages").html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                    '</div>');
                } else {
                    // Show error message
                    $("#edit-order-messages").html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                    '</div>');
                }
            },
            error: function(xhr, status, error) {
                $btn.button('reset');
                console.error("Error saving order:", error);
                $("#edit-order-messages").html('<div class="alert alert-danger">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Error saving order. Please try again.'+
                '</div>');
            }
        });
    });

    // Payment Place change event
    $("#paymentPlace").change(function(){
        $("#subTotal").val($("#grandTotal").text());
        $("#totalAmount").val($("#grandTotal").text());
    });

    // Initialize datepicker for order date
    $('#orderDate').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        startDate: '0d' // Can select today and future dates
    });

    // Set default date to today
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    today = yyyy + '-' + mm + '-' + dd;
    $('#orderDate').val(today);

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

    // Initialize date picker for edit form
    $('#editOrderDate').datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true
    });

    // Edit order button click
    $(document).on('click', '.editOrder', function(e) {
        e.preventDefault();
        var orderId = $(this).data('id');
        console.log("Edit clicked for order ID:", orderId);
        
        // Clear previous messages
        $('#edit-order-messages').html('');
        
        // Reset form fields
        $('#editOrderForm')[0].reset();
        
        $.ajax({
            url: 'php_action/fetchSelectedOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                console.log("Fetched order data:", response);
                
                if(response.success === true) {
                    // Set the order date
                    $('#editOrderDate').datepicker('setDate', response.orderDate);
                    
                    // Set other fields
                    $('#editClientName').val(response.clientName);
                    $('#editClientContact').val(response.clientContact);
                    $('#editOrderStatus').val(response.orderStatus);
                    $('#editTotalAmount').val(response.totalAmount);
                    $('#editOrderId').val(orderId);
                    
                    // Remove error messages
                    $('.text-danger').remove();
                    $('.form-group').removeClass('has-error');
                    
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
        var orderDate = $("#editOrderDate").val();
        var clientName = $("#editClientName").val();
        var clientContact = $("#editClientContact").val();
        var orderStatus = $("#editOrderStatus").val();
        var totalAmount = $("#editTotalAmount").val();

        // Validate form fields
        var isValid = true;
        
        if(!orderDate) {
            $("#editOrderDate").after('<p class="text-danger">Order Date is required</p>');
            $("#editOrderDate").closest('.form-group').addClass('has-error');
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
                        
                        // Reload table
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
