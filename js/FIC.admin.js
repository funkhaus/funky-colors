jQuery(document).ready(function(){

	// Call tagsInput on tag fields
	jQuery('#fgram_user_list').tagsInput({
	   'height':'80px',
	   'defaultText':'Usernames'
	});
	jQuery('#fgram_tag_list').tagsInput({
	   'height':'80px',
	   'defaultText':'Add Tags'
	});

	// Click on import button
	jQuery(document).on('click', '#fgram_import', function(){

		// grab CRON url
		var url = jQuery('#funkstagram_settings').data('cron');

		// No url for ajax? do nothing
		if ( ! url ) return false;

		// Disable button		
		jQuery(this).attr('disabled', 'disabled');

		// AJAX request
		jQuery.get(url).success(function(data){

			// Append error log
			jQuery('#funkstagram_settings').after('<div id="fgram_errors">' + data + '</div>');

			// Enable button
			jQuery('#fgram_import').attr('disabled', false);

		});
	});
});