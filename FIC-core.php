<?php

/*
 * load ColorExtractor library
 */

    require_once 'ColorExtractor/Color.php' ;
    require_once 'ColorExtractor/ColorExtractor.php' ;
    require_once 'ColorExtractor/Palette.php' ;

    use League\ColorExtractor\Color;
    use League\ColorExtractor\ColorExtractor;
    use League\ColorExtractor\Palette;

    // helpter to transform int to hex
    function fromIntToHex($int){
        return Color::fromIntToHex($int);
    }

    // given a URL and a target number of palette colors, generate array palette
    function getColors($url, $number = 5){

        $palette = Palette::fromFilename($url);
        $extractor = new ColorExtractor($palette);

        $colorArrayInt = $extractor->extract($number);
        $colorArray = array_map('fromIntToHex', $colorArrayInt);

        return array_values($colorArray);
    }

/*
 * Define a few core functions
 */

    // helper function to convert rgb array to hex
/*
    function rgb2hex($rgb) {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex;
    }
*/

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
    // $image can be either a server file path or an attachment ID
    function FIC_detect_single_image($attachment_id, $file_path = null){

        $imageURL = $file_path;

        // if no path provided, attempt to create it
        if ( !$imageURL ){

            // get data from image
            $image_data = wp_get_attachment_metadata( $attachment_id );

            // no image data came back?
            if ( !$image_data ){
                $path = get_attached_file($attachment_id);

                // attempt to create new attachment data, return false on failure
                if ( ! $image_data = wp_generate_attachment_metadata($attachment_id, $path) ){
                    return false;
                }
            }

            // make server file path from data
            $thumb_path = path_join( dirname($image_data['file']), $image_data['sizes']['thumbnail']['file'] );

            // get wp upload directory
            $upload_dir = wp_upload_dir();

            if ( $upload_dir['basedir'] )
                $imageURL = trailingslashit($upload_dir['basedir']) . $thumb_path;
        }

        if ( !$imageURL || !file_exists($imageURL) ) return false;

        try {
            $colorArray = getColors($imageURL, 5);
            $primary = isset($colorArray[0]) ? $colorArray[0] : false;

            // save palette & primary as meta
            update_post_meta($attachment_id, 'FIC_palette', $colorArray);
            update_post_meta($attachment_id, 'FIC_color', $primary);
            $output = $colorArray;

        } catch (Exception $e) {
            $output = false;
        }

        // return true/false
        return $output;
    }

    // Primary function to loop all images and set colors
    function FIC_detect_all_images(){

        // get all attachments without a color
        $attachment_ids = FIC_get_all_wp_attachments();

        // loop and set a color for each
        foreach ($attachment_ids as $attachment_id)
            FIC_detect_single_image($attachment_id);

        return;
    }

?>
