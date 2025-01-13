var manageCategoriesTable;

$(document).ready(function() {
	// active top navbar categories
	$('#navCategories').addClass('active');	

	manageCategoriesTable = $('#manageCategoriesTable').DataTable({
		'ajax' : 'php_action/fetchCategories.php',
		'order': []
	}); // manage categories Data Table

	// on click on submit categories form modal
	$('#addCategoriesModalBtn').unbind('click').bind('click', function() {
		// reset the form text
		$("#submitCategoriesForm")[0].reset();
		// remove the error text
		$(".text-danger").remove();
		// remove the form error
		$('.form-group').removeClass('has-error').removeClass('has-success');

		// submit categories form function
		$("#submitCategoriesForm").unbind('submit').bind('submit', function() {
			var categoriesName = $("#categoriesName").val();
			var categoriesStatus = $("#categoriesStatus").val();

			if(categoriesName == "") {
				$("#categoriesName").after('<p class="text-danger">Category Name field is required</p>');
				$('#categoriesName').closest('.form-group').addClass('has-error');
			} else {
				$("#categoriesName").find('.text-danger').remove();
				$("#categoriesName").closest('.form-group').addClass('has-success');	  	
			}

			if(categoriesStatus == "") {
				$("#categoriesStatus").after('<p class="text-danger">Status field is required</p>');
				$('#categoriesStatus').closest('.form-group').addClass('has-error');
			} else {
				$("#categoriesStatus").find('.text-danger').remove();
				$("#categoriesStatus").closest('.form-group').addClass('has-success');	  	
			}

			if(categoriesName && categoriesStatus) {
				var form = $(this);
				$("#createCategoriesBtn").button('loading');

				$.ajax({
					url : form.attr('action'),
					type: form.attr('method'),
					data: form.serialize(),
					dataType: 'json',
					success:function(response) {
						$("#createCategoriesBtn").button('reset');

						if(response.success == true) {
							manageCategoriesTable.ajax.reload(null, false);						

							$("#submitCategoriesForm")[0].reset();
							$(".text-danger").remove();
							$('.form-group').removeClass('has-error').removeClass('has-success');
	  	  			
							$('#add-categories-messages').html('<div class="alert alert-success">'+
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
						$("#createCategoriesBtn").button('reset');
						console.error('Error:', error);
						console.error('Status:', status);
						console.error('Response:', xhr.responseText);
						
						$('#add-categories-messages').html('<div class="alert alert-danger">'+
							'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
							'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
						'</div>');
					}
				});	
			}
			return false;
		});
	});
}); 

// edit categories function
function editCategories(categoriesId = null) {
	if(categoriesId) {
		$('#editCategoriesModal').modal('show');
		
		// remove the added categories id 
		$('#editCategoriesId').remove();
		// reset the form text
		$("#editCategoriesForm")[0].reset();
		// reset the form text-error
		$(".text-danger").remove();
		// reset the form group error		
		$('.form-group').removeClass('has-error').removeClass('has-success');

		// edit categories messages
		$("#edit-categories-messages").html("");
		// modal spinner
		$('.modal-loading').removeClass('div-hide');
		// modal result
		$('.edit-categories-result').addClass('div-hide');
		//modal footer
		$(".editCategoriesFooter").addClass('div-hide');		

		$.ajax({
			url: 'php_action/fetchSelectedCategories.php',
			type: 'post',
			data: {categoriesId: categoriesId},
			dataType: 'json',
			success:function(response) {
				// modal spinner
				$('.modal-loading').addClass('div-hide');
				
				if(!response.success) {
					$('.edit-categories-result').html('<div class="alert alert-danger">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
						'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
					'</div>').removeClass('div-hide');
					return;
				}

				// modal result
				$('.edit-categories-result').removeClass('div-hide');
				//modal footer
				$(".editCategoriesFooter").removeClass('div-hide');	

				// set the categories name
				$("#editCategoriesName").val(response.categories_name);
				// set the categories status
				$("#editCategoriesStatus").val(response.categories_active);
				// add the categories id 
				$(".editCategoriesFooter").after('<input type="hidden" name="editCategoriesId" id="editCategoriesId" value="'+response.categories_id+'" />');

				// submit of edit categories form
				$("#editCategoriesForm").unbind('submit').bind('submit', function() {
					var categoriesName = $("#editCategoriesName").val();
					var categoriesStatus = $("#editCategoriesStatus").val();

					if(categoriesName == "") {
						$("#editCategoriesName").after('<p class="text-danger">Category Name field is required</p>');
						$('#editCategoriesName').closest('.form-group').addClass('has-error');
					} else {
						$("#editCategoriesName").find('.text-danger').remove();
						$("#editCategoriesName").closest('.form-group').addClass('has-success');	  	
					}

					if(categoriesStatus == "") {
						$("#editCategoriesStatus").after('<p class="text-danger">Status field is required</p>');
						$('#editCategoriesStatus').closest('.form-group').addClass('has-error');
					} else {
						$("#editCategoriesStatus").find('.text-danger').remove();
						$("#editCategoriesStatus").closest('.form-group').addClass('has-success');	  	
					}

					if(categoriesName && categoriesStatus) {
						var form = $(this);
						$("#editCategoriesBtn").button('loading');

						$.ajax({
							url : form.attr('action'),
							type: form.attr('method'),
							data: form.serialize(),
							dataType: 'json',
							success:function(response) {
								$("#editCategoriesBtn").button('reset');

								if(response.success == true) {
									manageCategoriesTable.ajax.reload(null, false);									  	  			
									
									$(".text-danger").remove();
									$('.form-group').removeClass('has-error').removeClass('has-success');
									$('#editCategoriesModal').modal('hide');
			  	  			
									$('#edit-categories-messages').html('<div class="alert alert-success">'+
										'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
										'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
									'</div>');
							
									$(".alert-success").delay(500).show(10, function() {
										$(this).delay(3000).hide(10, function() {
											$(this).remove();
										});
									});
								} else {
									$('#edit-categories-messages').html('<div class="alert alert-danger">'+
										'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
										'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
									'</div>');
								}
							},
							error: function(xhr, status, error) {
								$("#editCategoriesBtn").button('reset');
								console.error('Error:', error);
								console.error('Status:', status);
								console.error('Response:', xhr.responseText);
								
								$('#edit-categories-messages').html('<div class="alert alert-danger">'+
									'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
									'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
								'</div>');
							}
						});	
					}
					return false;
				});
			},
			error: function(xhr, status, error) {
				$('.modal-loading').addClass('div-hide');
				console.error('Error:', error);
				console.error('Status:', status);
				console.error('Response:', xhr.responseText);
				
				$('.edit-categories-result').html('<div class="alert alert-danger">'+
					'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
					'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred while fetching category data.'+
				'</div>').removeClass('div-hide');
			}
		});
	}
}

function removeCategories(categoriesId = null) {
	if(categoriesId) {
		$('#removeCategoriesModal').modal('show');
		
		$('#removeCategoriesBtn').unbind('click').bind('click', function() {
			$(this).button('loading');

			$.ajax({
				url: 'php_action/removeCategories.php',
				type: 'post',
				data: {
					categoriesId: categoriesId,
					csrf_token: $('input[name="csrf_token"]').val()
				},
				dataType: 'json',
				success:function(response) {
					$('#removeCategoriesBtn').button('reset');
					
					if(response.success == true) {
						$('#removeCategoriesModal').modal('hide');
						manageCategoriesTable.ajax.reload(null, false);
						
						$('.remove-messages').html('<div class="alert alert-success">'+
							'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
							'<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
						'</div>');
						
						$(".alert-success").delay(500).show(10, function() {
							$(this).delay(3000).hide(10, function() {
								$(this).remove();
							});
						});
					} else {
						$('.remove-messages').html('<div class="alert alert-danger">'+
							'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
							'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
						'</div>');
					}
				},
				error: function(xhr, status, error) {
					$('#removeCategoriesBtn').button('reset');
					console.error('Error:', error);
					console.error('Status:', status);
					console.error('Response:', xhr.responseText);
					
					$('.remove-messages').html('<div class="alert alert-danger">'+
						'<button type="button" class="close" data-dismiss="alert">&times;</button>'+
						'<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> An error occurred. Please try again.'+
					'</div>');
				}
			});
		});
	}
}