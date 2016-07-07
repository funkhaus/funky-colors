<?php

/*
 *
 *	Plugin Name: Funky Image Colors
 *	Plugin URI: http://funkhaus.us
 *	Description: A wordpress tool for finding the primary color of an image
 *	Author: John Robson, Funkhaus
 *	Version: 1.0
 *	Author URI: http://Funkhaus.us
 *	Requires at least: 3.8
 *
 */

 	// get FIC core and settings
//	require_once('FIC-class.php');
    require_once('FIC-settings.php');

	// add metadata to attachments
    require_once('FIC-meta.php');

    // helper function to get all image attachments in WP that don't have a color set
    function FIC_get_all_wp_attachments(){

        $args = array(
    		'posts_per_page'    => -1,
            'meta_query'        => array(
                array(
                    'key'       => '_FIC_color',
                    'compare'   => 'NOT EXISTS'
                )
            ),
    		'post_type'         => 'attachment',
    		'post_mime_type'    => 'image',
    		'post_status'       => 'any',
    		'fields'            => 'ids'
    	);

    	return get_posts($args);
    }

    // Helper function to detect one single image
    function FIC_detect_single_image($attachment){
        $attachment = get_post($attachment);

        // do stuff here

        return;
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

	// Set ten minute interval for cron
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

    // Hook import function to cron hook
    add_action( 'FIC_cron', 'FIC_detect_all_images' );


    // Link function to admin-ajax
    add_action( 'wp_ajax_FIC_run', 'FIC_detect_all_images' );
    add_action( 'wp_ajax_nopriv_FIC_run', 'FIC_detect_all_images' );


    function FIC_get_all_attachments_ajax() {

        // get all attachments without a color
        $attachment_ids = FIC_get_all_wp_attachments();

        header('Content-Type: application/json');
        echo json_encode($attachment_ids);
        exit;
    }

    // Link instagram redirect to
    add_action( 'wp_ajax_FIC_get_images', 'FIC_get_all_attachments_ajax' );


	// Helper function to get this directory
	if ( ! function_exists( 'pp' ) ) {
	    function pp() {
	        return plugin_dir_url( __FILE__ );
	    }
	}

?>