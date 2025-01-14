$(document).ready(function() {
	// top nav bar 
	$('#importbrand').addClass('active');
	
	// Initialize file input with proper options
	$("#brandfile").fileinput({
		overwriteInitial: true,
		maxFileSize: 2500,
		showClose: false,
		showCaption: true,
		showPreview: false,  // Disable preview to avoid blob URL issues
		browseLabel: 'Browse',
		removeLabel: 'Remove',
		browseIcon: '<i class="glyphicon glyphicon-folder-open"></i> ',
		removeIcon: '<i class="glyphicon glyphicon-remove"></i> ',
		removeTitle: 'Cancel or reset changes',
		elErrorContainer: '#kv-avatar-errors-1',
		msgErrorClass: 'alert alert-block alert-danger',
		defaultPreviewContent: '<img src="assests/images/photo_default.png" alt="Profile Image" style="width:100%;">',
		layoutTemplates: {
			main1: '{preview}\n' +
				'<div class="input-group {class}">\n' +
				'   {caption}\n' +
				'   <div class="input-group-btn">\n' +
				'       {browse}\n' +
				'       {remove}\n' +
				'   </div>\n' +
				'</div>',
			main2: '{preview}\n{remove}\n{browse}\n'
		},
		allowedFileExtensions: ["csv", "xls", "xlsx"],
		uploadUrl: null, // Disable AJAX upload
		uploadAsync: false,
		showUpload: false
	});

	// Clear any existing error/success states
	$(".text-danger").remove();
	$(".form-group").removeClass('has-error').removeClass('has-success');
		  
	// Submit brand import form
	$("#submitImportForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
		
		// Clear previous messages
		$(".text-danger").remove();
		$(".form-group").removeClass('has-error').removeClass('has-success');
		$("#add-product-messages").empty();
		
		// Debug log
		console.log("Form submitted");
		console.log("CSRF Token:", $('input[name="csrf_token"]').val());
		
		// Validate file input
		var brandfile = $("#brandfile").val();
		if(brandfile == "") {
			$("#brandfile").closest('.center-block').after('<p class="text-danger">Please select a file to import</p>');
			$('#brandfile').closest('.form-group').addClass('has-error');
			return false;
		}

		// Check file extension
		var extension = brandfile.split('.').pop().toLowerCase();
		if($.inArray(extension, ['csv', 'xls', 'xlsx']) == -1) {
			$("#brandfile").closest('.center-block').after('<p class="text-danger">Invalid file type. Only CSV, XLS, and XLSX files are allowed.</p>');
			$('#brandfile').closest('.form-group').addClass('has-error');
			return false;
		}

		// Set loading state
		$("#importBrandBtn").button('loading');

		// Prepare form data
		var form = $(this);
		var formData = new FormData(this);

		// Ensure CSRF token is included
		var csrfToken = $('input[name="csrf_token"]').val();
		if (!csrfToken) {
			console.error("CSRF token not found");
			$('#add-product-messages').html('<div class="alert alert-danger">'+
				'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
				'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> CSRF token is missing. Please refresh the page.'+
			'</div>');
			$("#importBrandBtn").button('reset');
			return false;
		}
		formData.append('csrf_token', csrfToken);

		// Debug log
		console.log("Submitting to:", form.attr('action'));
		
		// Submit form via AJAX
		$.ajax({
			url: form.attr('action'),
			type: form.attr('method'),
			data: formData,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			success: function(response) {
				// Reset button state
				$("#importBrandBtn").button('reset');

				if(response.success) {
					// Reset form
					$("#submitImportForm")[0].reset();
					$("#brandfile").fileinput('clear');

					// Scroll to top
					$("html, body").animate({scrollTop: '0'}, 100);
													
					// Show success message
					$('#add-product-messages').html('<div class="alert alert-success">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
						'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
					'</div>');

					// Auto-hide success message
					$(".alert-success").delay(500).show(10, function() {
						$(this).delay(3000).hide(10, function() {
							$(this).remove();
						});
					});

					// Reset form state
					$(".text-danger").remove();
					$(".form-group").removeClass('has-error').removeClass('has-success');

				} else {
					// Show error message
					$('#add-product-messages').html('<div class="alert alert-danger">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
						'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
					'</div>');
				}
			},
			error: function(xhr, status, error) {
				// Reset button state
				$("#importBrandBtn").button('reset');
				
				// Show error message with more details
				var errorMessage = 'An error occurred while importing. ';
				if (xhr.status === 403) {
					errorMessage += 'Access forbidden. Please check your session and try again.';
				} else {
					errorMessage += 'Please try again.';
				}
				
				$('#add-product-messages').html('<div class="alert alert-danger">'+
					'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
					'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ errorMessage +
				'</div>');

				// Log error for debugging
				console.error("Import error:", {
					status: xhr.status,
					statusText: xhr.statusText,
					responseText: xhr.responseText,
					error: error
				});
			}
		});
	});
});
