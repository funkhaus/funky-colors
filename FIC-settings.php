<?php

    /*
     * admin Scripts and styles for plugin
     */
    function FIC_admin_style() {
        wp_register_style( 'FIC_css', pp() . '/css/FIC.admin.css' );
        wp_register_script( 'FIC_js', pp() . '/js/FIC.admin.js' );

        if ( is_admin() ) {
            wp_enqueue_style( 'FIC_css');
            wp_enqueue_script( 'FIC_js');
        }
    }
    add_action( 'admin_init', 'FIC_admin_style' );


    /* Call Settings Page */
    function FIC_settings_page() {

    ?>

        <div class="wrap">
            <h2>Funky Image Color Options</h2>
        </div><!-- END Wrap -->

        <?php
    }

    /* Save Takeover Settings */
    function FIC_settings_init(){
        // register_setting('funkstagram_settings', 'fgram_api_key');
    }
    add_action('admin_init', 'FIC_settings_init');

	function FIC_add_settings() {
		add_submenu_page( 'tools.php', 'Funky Colors', 'Funky Colors', 'manage_options', 'FIC_settings', 'FIC_settings_page' );
	}

	add_action('admin_menu','FIC_add_settings');


	/* Add settings help menu dropdown */
	function funkstagram_plugin_help($contextual_help, $screen_id, $screen) {

		if ($screen_id == 'tools_page_funkstagram_settings') {

			$contextual_help = wp_remote_get( trailingslashit( pp() ) . 'funkstagram-help.php' );

		}

		if ( wp_remote_retrieve_response_code( $contextual_help ) == 200 ) {
			return wp_remote_retrieve_body( $contextual_help );
		}

	}

	add_filter('contextual_help', 'funkstagram_plugin_help', 10, 3);

?>