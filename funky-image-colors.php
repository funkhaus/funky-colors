<?php

/*
 *
 *	Plugin Name: Funky Image Colors
 *	Plugin URI: http://funkhaus.us
 *	Description: A wordpress tool for finding the primary color of an image
 *	Author: John Robson, Funkhaus
 *	Version: 1.1
 *	Author URI: http://Funkhaus.us
 *	Requires at least: 3.8
 *
 */

	// get FIC core and settings
    require_once('FIC-core.php');
    require_once('FIC-settings.php');

    // add metadata to attachments
    require_once('FIC-meta.php');

/*
 * Set convenince functions for theme developer
 */

    // get primary image color
    if ( !function_exists('get_primary_image_color') ){

        function get_primary_image_color( $attachment_id ){
            $output = '';

            // if the image has a primary color, set as output
            if ( $color = get_post_meta($attachment_id, 'FIC_color', true) ){
                $output = $color;
            }

            return $output;
        }

    }

    // get secondary image color from palette
    if ( !function_exists('get_second_image_color') ){

        function get_second_image_color( $attachment_id ){
            $output = '';

            if( ($secondary = get_post_meta($attachment_id, 'FIC_secondary_color', true) ) ){
                $output = $secondary;
            }

            // if attachment has a color palette...
            else if ( $color_palette = get_post_meta($attachment_id, 'FIC_palette', true) ){

                // get the second color in the palette
                if ( isset($color_palette[1]) )
                    $output = $color_palette[1];

            }

            return $output;
        }

    }

/*
 * Set up 10 minute cron schedule
 */
	function FIC_set_interval( $schedules ) {
		$schedules['ten_minutes'] = array(
			'interval' => 600,
			'display' => __('Every ten minutes')
		);
		return $schedules;
	}
	add_filter( 'cron_schedules', 'FIC_set_interval' );

    // set 10 minute cron
	if ( ! wp_next_scheduled( 'FIC_cron' ) ) {
		wp_schedule_event( time(), 'ten_minutes', 'FIC_cron' );
	}

    // Hook main run function to cron hook
    add_action( 'FIC_cron', 'FIC_detect_all_images' );


/*
 * Define ajax functions
 */
    function FIC_get_all_attachments_ajax() {

        // get all attachments without a color
        $attachment_ids = FIC_get_all_wp_attachments();

        header('Content-Type: application/json');
        echo json_encode($attachment_ids);
        exit;

    }

    // ajax function to get an array of all images that need to be detected
    add_action( 'wp_ajax_FIC_get_images', 'FIC_get_all_attachments_ajax' );

    function FIC_detect_single_image_ajax() {

        $output = 'Unknown server error';
        if ( isset($_REQUEST['target_image']) && $_REQUEST['target_image'] ){
            $success = FIC_detect_single_image($_REQUEST['target_image']);

            if ( $success )
                $output = 'Detected color for image: ' . $_REQUEST['target_image'];
            else
                $output = 'Error detecting colors for image: ' . $_REQUEST['target_image'];
        }

        // output
        header('Content-Type: text');
        echo $output;
        exit;
    }

    // ajax function to detect color for a single image
    add_action( 'wp_ajax_FIC_detect_image', 'FIC_detect_single_image_ajax' );

    // remove detected color for all images
    function FIC_remove_detected_colors() {

        // get all attachments, start counter
        $all_attachment_ids = FIC_get_all_wp_attachments(false);
        $count = 0;

        // loop attachments...
        foreach ( $all_attachment_ids as $attachment_id ){

            // delete palette from image
            delete_post_meta($attachment_id, 'FIC_palette');

            // delete secondary color from image
            delete_post_meta($attachment_id, 'FIC_secondary_color');

            // remove meta on this attachment
            if ( delete_post_meta($attachment_id, 'FIC_color') )
                $count++;

        }

        // output
        header('Content-Type: text');
        echo 'Color meta erased on ' . $count . ' attachments total.';
        exit;
    }

    // ajax function to detect color for a single image
    add_action( 'wp_ajax_FIC_remove_colors', 'FIC_remove_detected_colors' );

	// Helper function to get this directory
	if ( ! function_exists( 'FICpp' ) ) {
	    function FICpp() {
	        return plugin_dir_url( __FILE__ );
	    }
	}

/*
 * Hook into "new attachment" event and detect colors for the incoming image
 */
    function FIC_detect_color_for_new_attachment( $image_data, $attachment_id ) {

        // make server file path from data
        $thumb_path = path_join( dirname($image_data['file']), $image_data['sizes']['thumbnail']['file'] );

        // get wp upload directory
        $upload_dir = wp_upload_dir();

        // run detection
        FIC_detect_single_image($attachment_id, trailingslashit($upload_dir['basedir']) . $thumb_path);
        return $image_data;
    }
    add_filter( 'wp_generate_attachment_metadata', 'FIC_detect_color_for_new_attachment', 20, 2);

?>
