$(document).ready(function() {
    // Initialize date pickers
    $("#startDate").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $("#endDate").datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $("#getOrderReportForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        
        var startDate = $("#startDate").val();
        var endDate = $("#endDate").val();

        if(startDate == "" || endDate == "") {
            if(startDate == "") {
                $("#startDate").closest('.form-group').addClass('has-error');
                $("#startDate").after('<p class="text-danger">The Start Date is required</p>');
            } else {
                $(".form-group").removeClass('has-error');
                $(".text-danger").remove();
            }

            if(endDate == "") {
                $("#endDate").closest('.form-group').addClass('has-error');
                $("#endDate").after('<p class="text-danger">The End Date is required</p>');
            } else {
                $(".form-group").removeClass('has-error');
                $(".text-danger").remove();
            }
        } else {
            $(".form-group").removeClass('has-error');
            $(".text-danger").remove();

            var form = $(this);

            // Show loading state
            $("#generateReportBtn").button('loading');
            $(".result").html("");

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'html',
                success: function(response) {
                    $("#generateReportBtn").button('reset');
                    
                    // Add print button
                    var printBtn = $('<button>', {
                        text: 'Print Report',
                        class: 'btn btn-primary pull-right',
                        style: 'margin-bottom: 10px;',
                        click: function() {
                            var printContents = response;
                            var originalContents = document.body.innerHTML;

                            // Create print window
                            var printWindow = window.open('', '', 'height=600,width=800');
                            printWindow.document.write('<html><head><title>Order Report</title>');
                            printWindow.document.write('<link rel="stylesheet" href="../assests/bootstrap/css/bootstrap.min.css">');
                            printWindow.document.write('</head><body>');
                            printWindow.document.write(printContents);
                            printWindow.document.write('</body></html>');
                            printWindow.document.close();

                            printWindow.onload = function() {
                                printWindow.focus();
                                printWindow.print();
                            };
                        }
                    });
                    
                    // Clear previous results and add new content
                    $(".result")
                        .empty()
                        .append(printBtn)
                        .append(response);
                },
                error: function(xhr, status, error) {
                    $("#generateReportBtn").button('reset');
                    console.error("Error:", error);
                    $(".result").html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> Failed to generate report. Please try again.'+
                    '</div>');
                }
            });
        }
        return false;
    });
});