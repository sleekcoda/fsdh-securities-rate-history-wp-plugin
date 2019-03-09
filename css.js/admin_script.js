jQuery(document).ready(function($) {
    $("#datepicker").datepicker({ dateFormat: 'yy-mm-dd' });
	$('#rc_rate_history_form').validate();
	
	$('#rc_rate_history_form').submit(function(e){
		e.preventDefault();
		var formData = 'action=rc_rate_history&'+$('#rc_rate_history_form').serialize();
		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: formData
		}).done(function(response){
			$('#rc_rate_history_form').append('<div class="notice notice-success is-dismissible"><p>Data updated successfully</p></div>');
			$('.notice').fadeOut(3000,null);
		}).fail(function(response){
			$('#rc_rate_history_form').append('<div class="notice notice-error"><p>Error: '+response+'</p></div>');
			$('.notice').fadeOut(3000,null);
		});
		
	})

	$('#rc_rate_history_bulk_import').submit(function(e){
		e.preventDefault();
		$('#rc_rate_history_bulk_import').append('<div class="notice notice-error"><p>Error: this form can\'t process your request at the moment.</p></div>');
			$('.notice').fadeOut(6000,null);
	});
});