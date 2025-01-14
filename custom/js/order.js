// Order management module
var OrderManager = (function() {
    var initialized = false;
    var loadingProducts = null;

    function initializeProducts() {
        // If we're already loading products, return the existing promise
        if (loadingProducts) {
            console.log("Already loading products, reusing promise...");
            return loadingProducts;
        }

        console.log("Loading initial products...");
        loadingProducts = $.ajax({
            url: 'php_action/fetchProductData.php',
            type: 'post',
            dataType: 'json'
        }).then(function(response) {
            console.log("Initial products load:", response);
            if(Array.isArray(response) && response.length > 0) {
                var select = $("#productName1");
                $.each(response, function(index, value) {
                    select.append('<option value="'+value[0]+'">'+value[1]+'</option>');							
                });
            }
            return response;
        }).fail(function(xhr, status, error) {
            console.error("Error loading initial products:", error);
            throw error;
        });

        return loadingProducts;
    }

    function bindEvents() {
        // Add row button click
        $("#addRowBtn").off('click').on('click', addRow);

        // Product selection change
        $(document).off('change', 'select[name="productName[]"]').on('change', 'select[name="productName[]"]', function() {
            var row = $(this).closest('tr').attr('data-row');
            if(row) {
                getProductData(row);
            }
        });

        // Quantity change
        $(document).off('change keyup', 'input[name="quantity[]"]').on('change keyup', 'input[name="quantity[]"]', function() {
            var row = $(this).closest('tr').attr('data-row');
            if(row) {
                updateTotal(row);
            }
        });

        // Remove row button click
        $(document).off('click', '.removeProductRowBtn').on('click', '.removeProductRowBtn', function() {
            var row = $(this).attr('data-row');
            if(row) {
                removeProductRow(row);
            }
        });

        // Handle form submissions
        $("#submitOrderForm").unbind('submit').bind('submit', function() {
            // Add your form submission logic here
        });

        $("#editOrderForm").unbind('submit').bind('submit', function() {
            // Add your edit form submission logic here
        });
    }

    // Initialize the module
    function init() {
        if (initialized) {
            console.log("OrderManager already initialized");
            return Promise.resolve();
        }

        console.log("Initializing OrderManager...");
        initialized = true;
        bindEvents();
        return initializeProducts();
    }

    return {
        init: init
    };
})();

// Wait for document ready
$(document).ready(function() {
    OrderManager.init();
});

// Get Product Data
function getProductData(row = null) {
    if(row) {
        var productId = $("#productName"+row).val();		
        console.log("Row:", row, "Selected Product ID:", productId); // Debug log
        
        if(!productId || productId === "") {
            $("#rate"+row).val("");
            $("#quantity"+row).val("");           
            $("#total"+row).val("");
            return; // Exit early if no product selected
        }
        
        $.ajax({
            url: 'php_action/fetchSelectedProduct.php',
            type: 'post',
            data: {productId: productId},
            dataType: 'json',
            success: function(response) {
                console.log("Product Response:", response); // Debug log
                
                if(response && response.success === true) {
                    // setting the rate value into the rate input field
                    $("#rate"+row).val(response.rate || 0);
                    
                    // Set default quantity to 1
                    $("#quantity"+row).val(1);
                    
                    // Calculate total
                    var total = Number(response.rate || 0) * 1;
                    total = total.toFixed(2);
                    $("#total"+row).val(total);
                    
                    // Update grand total
                    calculateGrandTotal();
                } else {
                    console.error("Invalid response:", response);
                    alert("Error fetching product details: " + (response.messages || "Unknown error"));
                    // Reset fields
                    $("#rate"+row).val("");
                    $("#quantity"+row).val("");
                    $("#total"+row).val("");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {xhr: xhr, status: status, error: error});
                alert("Error fetching product details. Please try again.");
                // Reset fields
                $("#rate"+row).val("");
                $("#quantity"+row).val("");
                $("#total"+row).val("");
            }
        });
    }
}

// Add row to product table
function addRow() {
    var tableLength = $("#productTable tbody tr").length;
    var nextRow = tableLength + 1;

    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success:function(response) {
            console.log("Products loaded:", response); // Debug log
            
            var tr = '<tr id="row'+nextRow+'" data-row="'+nextRow+'">'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<select class="form-control" name="productName[]" id="productName'+nextRow+'" required>'+
                          '<option value="">~~SELECT~~</option>';
                          // Only add options if we have products
                          if(Array.isArray(response) && response.length > 0) {
                              $.each(response, function(index, value) {
                                  tr += '<option value="'+value[0]+'">'+value[1]+'</option>';							
                              });
                          }
                                                    
            tr += '</select>'+
                    '</div>'+
                  '</td>'+
                  '<td>'+
                      '<input type="text" name="rate[]" id="rate'+nextRow+'" class="form-control" readonly />'+
                  '</td>'+
                  '<td>'+
                      '<div class="form-group">'+
                      '<input type="number" name="quantity[]" id="quantity'+nextRow+'" class="form-control" min="1" required />'+
                      '</div>'+
                  '</td>'+
                  '<td>'+
                      '<input type="text" name="total[]" id="total'+nextRow+'" class="form-control" readonly />'+
                  '</td>'+
                  '<td>'+
                      '<button class="btn btn-danger removeProductRowBtn" type="button" data-row="'+nextRow+'"><i class="glyphicon glyphicon-trash"></i></button>'+
                  '</td>'+
              '</tr>';
            
            if(tableLength > 0) {							
                $("#productTable tbody tr:last").after(tr);
            } else {				
                $("#productTable tbody").append(tr);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading products:", error);
            alert("Error loading products. Please refresh the page.");
        }
    });
}

// Update total when quantity changes
function updateTotal(row) {
    if(row) {
        var rate = Number($("#rate"+row).val() || 0);
        var quantity = Number($("#quantity"+row).val() || 0);
        
        var total = rate * quantity;
        total = total.toFixed(2);
        $("#total"+row).val(total);
        
        // Update grand total
        calculateGrandTotal();
    }
}

// Calculate grand total
function calculateGrandTotal() {
    var grandTotal = 0;
    $('input[name="total[]"]').each(function() {
        grandTotal += Number($(this).val() || 0);
    });
    $("#grandTotal").text(grandTotal.toFixed(2));
}

// Remove Product Row
function removeProductRow(row) {
    $("#row"+row).remove();
    calculateGrandTotal();
}

// DataTable initialization
var manageOrderTable;
$(document).ready(function() {
    manageOrderTable = $("#manageOrderTable").DataTable({
        'ajax': 'php_action/fetchOrder.php',
        'order': []
    });
});
