<?php

/* Add funkstagram status to attachment pages */
function funkstagram_att_meta($form_fields, $post) {

	$current_value = get_post_meta($post->ID, "fgram_status", true);
	
	// select options: you could code these manually or get it from a database
	$select_options = array(
		"draft" => "Draft",
		"pending_review" => "Pending Review",
		"published" => "Published",
	);
	
	// build the html for our select box
	$html = "<select name='attachments[{$post->ID}][fgram_status]' id='attachments[{$post->ID}][fgram_status]'>";
	foreach($select_options as $value => $text){

		// if status has not been set, set to default
		if ( empty($current_value) ) $current_value = esc_attr(get_option('fgram_default_status'));

		// if this value is the current_value we'll mark it selected
		$selected = ($current_value == $value) ? ' selected ' : '';

		// escape value	for single quotes so they won't break the html
		$value = addcslashes( $value, "'");

		$html .= "<option value='{$value}' {$selected}>{$text}</option>";
	}
	$html .= "</select>";

    $form_fields["fgram_status"] = array(
        "label" => __("Funkstagram Status"),
        "helps" => __("Only Published images will appear"),
        "input" => "html",
        "html"  => $html
    );
    return $form_fields;
}
add_filter("attachment_fields_to_edit", "funkstagram_att_meta", null, 2);

/* Save custom field value */
function funkstagram_save_att_meta($post, $attachment) {

    if(isset($attachment['fgram_status'])) {
        update_post_meta($post['ID'], 'fgram_status', $attachment['fgram_status']);
    } else {
        delete_post_meta($post['ID'], 'fgram_status');
    }
    return $post;
}
add_filter("attachment_fields_to_save", "funkstagram_save_att_meta", null , 2);
