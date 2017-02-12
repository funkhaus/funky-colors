<?php

/*
 * autoload colorthief library
 */

    $files = glob(__DIR__ . '/ColorThief/*.php');
    $files = array_merge($files, glob(__DIR__ . '/ColorThief/Image/*.php'));
    $files = array_merge($files, glob(__DIR__ . '/ColorThief/Image/Adapter/*.php'));
    foreach ($files as $file) {
        require_once $file;
    }

    use ColorThief\ColorThief;

/*
 * Define a few core functions
 */

    // helper function to convert rgb array to hex
    function rgb2hex($rgb) {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex;
    }

    // helper function to get all image attachments in WP that don't have a color set
    function FIC_get_all_wp_attachments($only_get_unset_images = true){

        $meta_query = array();
        if ( $only_get_unset_images ){
            $meta_query = array(
                array(
                    'key'       => 'FIC_color',
                    'compare'   => 'NOT EXISTS'
                )
            );
        }

        $accepted_mimes = array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp' );
        $args = array(
    		'posts_per_page'    => -1,
    		'orderby'           => 'date',
    		'order'             => 'DESC',
            'meta_query'        => $meta_query,
    		'post_type'         => 'attachment',
    		'post_mime_type'    => $accepted_mimes,
    		'post_status'       => 'any',
    		'fields'            => 'ids'
    	);

    	return get_posts($args);
    }

    // Helper function to detect one single image
    function FIC_detect_single_image($attachment_id){

        // do stuff here
        $imageURL = get_attached_file( $attachment_id );

        if ( !$imageURL ) return false;

        $colorArray = ColorThief::getColor($imageURL, 5);
        $hex = rgb2hex($colorArray);

        // URL, color count, quality
        $palette_colors = ColorThief::getPalette($imageURL, 3, 7);

        // save palette as meta
        update_post_meta($attachment_id, 'FIC_palette', $palette_colors);

        // return true/false
        return update_post_meta($attachment_id, 'FIC_color', $hex);
    }

    // Primary function to loop all images and set colors
    function FIC_detect_all_images(){

        // get all attachments without a color
        $attachment_ids = FIC_get_all_wp_attachments();

        // loop and set a color for each
        foreach ($attachment_ids as $attachment)
            FIC_detect_single_image($attachment);

        return;
    }

?>
