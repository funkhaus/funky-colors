<?php

/* Add funkstagram status to attachment pages */
function FIC_att_meta($form_fields, $post) {

	$current_value = get_post_meta($post->ID, "FIC_color", true);

    $second = get_second_image_color($post->ID);

	// build the html for our select box
	$html = '';
	if ( $current_value ){
    	$html .= "<div class='FIC-color-preview' style='background-color: $current_value;'></div>";
    	$html .= "<div class='FIC-color-preview' style='background-color: $second;'></div>";
	}
    $html .= "<input type='text' name='attachments[{$post->ID}][FIC_color]' id='attachments[{$post->ID}][FIC_color]' value='$current_value' />";

    $form_fields["FIC_color"] = array(
        "label" => __("Primary Color"),
        "helps" => __("Color automatically detected by Funky Image Colors"),
        "input" => "html",
        "html"  => $html
    );
    return $form_fields;
}
add_filter("attachment_fields_to_edit", "FIC_att_meta", null, 2);

/* Save custom field value */
function FIC_save_att_meta($post, $attachment) {

    if(isset($attachment['FIC_color'])) {
        update_post_meta($post['ID'], 'FIC_color', $attachment['FIC_color']);
    } else {
        delete_post_meta($post['ID'], 'FIC_color');
    }
    return $post;
}
add_filter("attachment_fields_to_save", "FIC_save_att_meta", null , 2);
