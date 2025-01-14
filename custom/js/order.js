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

// Print order function
function printOrder(orderId) {
    if(orderId) {
        $.ajax({
            url: 'php_action/printOrder.php',
            type: 'POST',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Create print window content
                    var printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Order Details</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .print-header { text-align: center; margin-bottom: 20px; }
                                .print-details { margin-bottom: 20px; }
                                .print-details p { margin: 5px 0; }
                                .print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                                .print-table th, .print-table td { 
                                    border: 1px solid #ddd; 
                                    padding: 8px; 
                                    text-align: left; 
                                }
                                .print-table th { background-color: #f5f5f5; }
                                .print-total { font-weight: bold; }
                                @media print {
                                    .print-header { margin-top: 0; }
                                    .no-print { display: none; }
                                    body { margin: 0; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="print-header">
                                <h2>Order Details</h2>
                            </div>
                            <div class="print-details">
                                <p><strong>Order Date:</strong> ${response.orderInfo.orderDate}</p>
                                <p><strong>Staff Name:</strong> ${response.orderInfo.clientName}</p>
                                <p><strong>Contact:</strong> ${response.orderInfo.clientContact}</p>
                                <p><strong>Restock Reason:</strong> ${response.orderInfo.restockReason || 'N/A'}</p>
                            </div>
                            <table class="print-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Rate</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    
                    response.orderItems.forEach(function(item) {
                        printContent += `
                            <tr>
                                <td>${item.productName}</td>
                                <td>$${parseFloat(item.rate).toFixed(2)}</td>
                                <td>${item.quantity}</td>
                                <td>$${parseFloat(item.total).toFixed(2)}</td>
                            </tr>`;
                    });
                    
                    printContent += `
                                </tbody>
                                <tfoot>
                                    <tr class="print-total">
                                        <td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>
                                        <td>$${response.orderTotal}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="no-print" style="text-align: center; margin-top: 20px;">
                                <button onclick="window.print();" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; margin-right: 10px; cursor: pointer;">Print</button>
                                <button onclick="window.close();" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                            </div>
                        </body>
                        </html>`;
                
                    // Open new window and write content
                    var printWindow = window.open('', '_blank', 'height=600,width=800');
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                } else {
                    alert('Error: ' + response.messages);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred while printing the order. Please try again.');
            }
        });
    }
}

function editOrder(orderId) {
    $("#editOrderForm")[0].reset();
    $('.form-group').removeClass('has-error').removeClass('has-success');
    $('.text-danger').remove();
    
    $.ajax({
        url: 'php_action/fetchSelectedOrder.php',
        type: 'post',
        data: {orderId: orderId},
        dataType: 'json',
        success:function(response) {
            console.log('Response:', response); // Debug log
            if(response.success == true) {            
                // modal loading
                $('.modal-loading').addClass('div-hide');
                // modal result
                $('.edit-order-result').removeClass('div-hide');
                // modal footer
                $('.editOrderFooter').removeClass('div-hide');

                $('#editOrderForm .form-group').removeClass('has-error').removeClass('has-success');

                // Get the first order since it's in an array
                var orderData = response.order[0];
                
                // set the order date
                $("#editOrderDate").val(orderData.order_date);
                // set the client name
                $("#editClientName").val(orderData.client_name);
                // set the client contact
                $("#editClientContact").val(orderData.client_contact);
                // set restock reason
                $("#editRestockReason").val(orderData.restock_reason);

                // array of product objects
                var orderItems = response.orderItems;
                var productTable = $("#editProductTable");
                
                // clear the table except the header row
                productTable.find("tr:gt(0)").remove();

                // populate the product table
                orderItems.forEach(function(item) {
                    var row = $("<tr>");
                    
                    // Product dropdown
                    var productCell = $("<td>");
                    var productSelect = $("<select>").addClass("form-control").attr("name", "editProductName[]");
                    
                    // Add options from available products
                    response.products.forEach(function(product) {
                        productSelect.append(
                            $("<option>")
                                .val(product.product_id)
                                .text(product.product_name)
                                .prop("selected", product.product_id == item.product_id)
                        );
                    });
                    
                    productCell.append(productSelect);
                    row.append(productCell);
                    
                    // Quantity input
                    row.append($("<td>").append(
                        $("<input>")
                            .addClass("form-control")
                            .attr({
                                type: "number",
                                name: "editQuantity[]",
                                value: item.quantity,
                                min: "1"
                            })
                    ));
                    
                    // Rate display
                    row.append($("<td>").append(
                        $("<input>")
                            .addClass("form-control")
                            .attr({
                                type: "text",
                                name: "editRate[]",
                                value: item.rate,
                                readonly: true
                            })
                    ));
                    
                    // Remove button
                    row.append($("<td>").append(
                        $("<button>")
                            .addClass("btn btn-danger removeProductRowBtn")
                            .attr("type", "button")
                            .append($("<i>").addClass("glyphicon glyphicon-trash"))
                    ));
                    
                    productTable.append(row);
                });

                // add order id to form
                $("#orderId").val(orderId);

                // show modal
                $("#editOrderModal").modal('show');
            } else {
                alert('Error: ' + (response.messages || 'Failed to fetch order details'));
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error); // Debug log
            alert('Error: ' + error);
        }
    });
}

function printOrder(orderId) {
    $.ajax({
        url: 'php_action/printOrder.php',
        type: 'POST',
        data: {orderId: orderId},
        dataType: 'json',
        success: function(response) {
            if(response.success == true) {
                var printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Order Details</title>');
                printWindow.document.write('<style>');
                printWindow.document.write('body { font-family: Arial, sans-serif; }');
                printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
                printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }');
                printWindow.document.write('th { background-color: #f5f5f5; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<h2>Order Details</h2>');
                printWindow.document.write('<p><strong>Order Date:</strong> ' + response.order.order_date + '</p>');
                printWindow.document.write('<p><strong>Staff Name:</strong> ' + response.order.client_name + '</p>');
                printWindow.document.write('<p><strong>Contact:</strong> ' + response.order.client_contact + '</p>');
                if(response.order.restock_reason) {
                    printWindow.document.write('<p><strong>Restock Reason:</strong> ' + response.order.restock_reason + '</p>');
                }
                printWindow.document.write('<table>');
                printWindow.document.write('<thead><tr><th>Product</th><th>Quantity</th><th>Rate</th><th>Total</th></tr></thead>');
                printWindow.document.write('<tbody>');
                
                var grandTotal = 0;
                response.items.forEach(function(item) {
                    var total = parseFloat(item.rate) * parseInt(item.quantity);
                    grandTotal += total;
                    printWindow.document.write('<tr>');
                    printWindow.document.write('<td>' + item.product_name + '</td>');
                    printWindow.document.write('<td>' + item.quantity + '</td>');
                    printWindow.document.write('<td>$' + parseFloat(item.rate).toFixed(2) + '</td>');
                    printWindow.document.write('<td>$' + total.toFixed(2) + '</td>');
                    printWindow.document.write('</tr>');
                });
                
                printWindow.document.write('</tbody>');
                printWindow.document.write('<tfoot><tr><th colspan="3">Grand Total</th><th>$' + grandTotal.toFixed(2) + '</th></tr></tfoot>');
                printWindow.document.write('</table>');
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            } else {
                alert('Error: ' + response.messages);
            }
        },
        error: function(xhr, status, error) {
            alert('Error: ' + error);
        }
    });
}

function removeOrder(orderId) {
    $("#removeOrderBtn").unbind('click').bind('click', function() {
        $.ajax({
            url: 'php_action/removeOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success:function(response) {
                if(response.success == true) {
                    $("#removeOrderModal").modal('hide');
                    
                    // refresh the table
                    manageOrderTable.ajax.reload(null, false);
                    
                    $('.remove-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                    '</div>');
                } else {
                    $('.removeOrderMessages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                    '</div>');
                }
            }
        });
    });
}

var manageOrderTable;

$(document).ready(function() {
    // Initialize DataTable
    manageOrderTable = $("#manageOrderTable").DataTable({
        'ajax': 'php_action/fetchOrder.php',
        'order': [],
        'columns': [
            { 
                "data": 0,  // order_id
                "visible": false
            },
            { 
                "data": 1,  // order_date
                "orderable": true
            },
            { 
                "data": 2,  // client_name (staff name)
                "orderable": true
            },
            { 
                "data": 3,  // client_contact
                "orderable": true
            },
            { 
                "data": 4,  // total_items with tooltip
                "orderable": true,
                "render": function(data) {
                    return data; // Data already contains HTML for tooltip
                }
            },
            { 
                "data": 5,  // action buttons
                "orderable": false,
                "searchable": false
            }
        ]
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Add row button click
    $("#addRowBtn").on('click', function() {
        addNewRow();
    });

    // Edit add row button click
    $("#editAddRowBtn").on('click', function() {
        addNewEditRow();
    });

    // Product selection change
    $(document).on('change', 'select[name="productName[]"]', function() {
        var row = $(this).closest('tr');
        var productId = $(this).val();
        updateProductDetails(row, productId);
    });

    // Edit product selection change
    $(document).on('change', 'select[name="editProductName[]"]', function() {
        var row = $(this).closest('tr');
        var productId = $(this).val();
        updateProductDetails(row, productId, true);
    });

    // Quantity change
    $(document).on('input', 'input[name="quantity[]"], input[name="editQuantity[]"]', function() {
        var row = $(this).closest('tr');
        updateRowTotal(row);
    });

    // Remove row button click
    $(document).on('click', '.removeProductRowBtn', function() {
        $(this).closest('tr').remove();
    });

    // Handle form submissions
    $("#submitOrderForm").on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $("#addOrderModal").modal('hide');
                    $("#submitOrderForm")[0].reset();
                    manageOrderTable.ajax.reload(null, false);
                    $('.add-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                } else {
                    $('.add-messages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                }
            }
        });
    });

    $("#editOrderForm").on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $("#editOrderModal").modal('hide');
                    manageOrderTable.ajax.reload(null, false);
                    $('.edit-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                } else {
                    $('.edit-messages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                }
            }
        });
    });
});

// Add new row to product table
function addNewRow() {
    var tableLength = $("#productTable tbody tr").length;
    var tableRow = tableLength + 1;
    
    var tr = '<tr id="row'+tableRow+'" data-id="'+tableRow+'">' +
             '<td>' +
             '<select class="form-control" name="productName[]" id="productName'+tableRow+'" required>' +
             '<option value="">~~SELECT~~</option>' +
             '</select>' +
             '</td>' +
             '<td><input type="text" name="rate[]" id="rate'+tableRow+'" class="form-control" readonly /></td>' +
             '<td><input type="number" name="quantity[]" id="quantity'+tableRow+'" class="form-control" min="1" required /></td>' +
             '<td><input type="text" name="total[]" id="total'+tableRow+'" class="form-control" readonly /></td>' +
             '<td><button type="button" class="btn btn-danger removeProductRowBtn" data-id="'+tableRow+'"><i class="glyphicon glyphicon-trash"></i></button></td>' +
             '</tr>';
    
    $("#productTable tbody").append(tr);

    // Fetch and populate product options
    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success: function(response) {
            var select = $("#productName"+tableRow);
            response.forEach(function(product) {
                select.append('<option value="'+product.product_id+'">'+product.product_name+'</option>');
            });
        }
    });
}

// Add new row to edit product table
function addNewEditRow() {
    var tableLength = $("#editProductTable tbody tr").length;
    var tableRow = tableLength + 1;
    
    var tr = '<tr id="editRow'+tableRow+'" data-id="'+tableRow+'">' +
             '<td>' +
             '<select class="form-control" name="editProductName[]" id="editProductName'+tableRow+'" required>' +
             '<option value="">~~SELECT~~</option>' +
             '</select>' +
             '</td>' +
             '<td><input type="text" name="editRate[]" id="editRate'+tableRow+'" class="form-control" readonly /></td>' +
             '<td><input type="number" name="editQuantity[]" id="editQuantity'+tableRow+'" class="form-control" min="1" required /></td>' +
             '<td><input type="text" name="editTotal[]" id="editTotal'+tableRow+'" class="form-control" readonly /></td>' +
             '<td><button type="button" class="btn btn-danger removeProductRowBtn" data-id="'+tableRow+'"><i class="glyphicon glyphicon-trash"></i></button></td>' +
             '</tr>';
    
    $("#editProductTable tbody").append(tr);

    // Fetch and populate product options
    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success: function(response) {
            var select = $("#editProductName"+tableRow);
            response.forEach(function(product) {
                select.append('<option value="'+product.product_id+'">'+product.product_name+'</option>');
            });
        }
    });
}

// Update product details when selected
function updateProductDetails(row, productId, isEdit = false) {
    if(!productId) return;

    var prefix = isEdit ? 'edit' : '';
    
    $.ajax({
        url: 'php_action/fetchSelectedProduct.php',
        type: 'post',
        data: {productId: productId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                row.find('input[name="'+prefix+'rate[]"]').val(response.product.rate);
                var quantity = row.find('input[name="'+prefix+'quantity[]"]').val() || 1;
                updateRowTotal(row);
            }
        }
    });
}

// Update row total when quantity changes
function updateRowTotal(row) {
    var rate = parseFloat(row.find('input[name$="rate[]"]').val()) || 0;
    var quantity = parseInt(row.find('input[name$="quantity[]"]').val()) || 0;
    var total = rate * quantity;
    row.find('input[name$="total[]"]').val(total.toFixed(2));
}

// Edit order
function editOrder(orderId) {
    $("#editOrderForm")[0].reset();
    $('.edit-messages').html('');
    
    $.ajax({
        url: 'php_action/fetchSelectedOrder.php',
        type: 'post',
        data: {orderId: orderId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                // Set order details
                $("#orderId").val(orderId);
                $("#editOrderDate").val(response.order[0].order_date);
                $("#editClientName").val(response.order[0].client_name);
                $("#editClientContact").val(response.order[0].client_contact);
                $("#editRestockReason").val(response.order[0].restock_reason);

                // Clear existing rows
                $("#editProductTable tbody").empty();

                // Add rows for each order item
                response.orderItems.forEach(function(item, index) {
                    var tableRow = index + 1;
                    
                    var tr = '<tr id="editRow'+tableRow+'" data-id="'+tableRow+'">' +
                            '<td>' +
                            '<select class="form-control" name="editProductName[]" id="editProductName'+tableRow+'" required>' +
                            '<option value="">~~SELECT~~</option>' +
                            '</select>' +
                            '</td>' +
                            '<td><input type="text" name="editRate[]" id="editRate'+tableRow+'" class="form-control" readonly value="'+item.rate+'" /></td>' +
                            '<td><input type="number" name="editQuantity[]" id="editQuantity'+tableRow+'" class="form-control" min="1" required value="'+item.quantity+'" /></td>' +
                            '<td><input type="text" name="editTotal[]" id="editTotal'+tableRow+'" class="form-control" readonly value="'+(item.rate * item.quantity).toFixed(2)+'" /></td>' +
                            '<td><button type="button" class="btn btn-danger removeProductRowBtn" data-id="'+tableRow+'"><i class="glyphicon glyphicon-trash"></i></button></td>' +
                            '</tr>';
                    
                    $("#editProductTable tbody").append(tr);
                    
                    // Populate product dropdown
                    var select = $("#editProductName"+tableRow);
                    response.products.forEach(function(product) {
                        var selected = (product.product_id == item.product_id) ? 'selected' : '';
                        select.append('<option value="'+product.product_id+'" '+selected+'>'+product.product_name+'</option>');
                    });
                });

                $("#editOrderModal").modal('show');
            } else {
                alert('Error fetching order details');
            }
        }
    });
}

// Print order
function printOrder(orderId) {
    if(!orderId) return;

    $.ajax({
        url: 'php_action/printOrder.php',
        type: 'post',
        data: {orderId: orderId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                var printWindow = window.open('', '_blank');
                printWindow.document.write(response.html);
                printWindow.document.close();
                printWindow.print();
            } else {
                alert('Error printing order: ' + response.messages);
            }
        }
    });
}

// Remove order
function removeOrder(orderId) {
    if(!orderId) return;

    $("#removeOrderBtn").unbind('click').bind('click', function() {
        $.ajax({
            url: 'php_action/removeOrder.php',
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $("#removeOrderModal").modal('hide');
                    manageOrderTable.ajax.reload(null, false);
                    $('.remove-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                } else {
                    $('.removeOrderMessages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                }
            }
        });
    });
}
