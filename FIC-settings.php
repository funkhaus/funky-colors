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

        <div class="wrap regenthumbs">
            <h2>Funky Image Colors</h2>

            <form method="post" action="" id="FIC-settings" data-ajax-images="<?php echo get_admin_url(null, '/admin-ajax.php?action=FIC_get_images'); ?>" data-ajax-run="<?php echo get_admin_url(null, '/admin-ajax.php?action=FIC_detect_image'); ?>">
                <?php //settings_fields('FIC_settings'); ?>

                <p>Use this tool to detect the primary color of each image in the media library and save its hex value to the attachment as metadata.</p>

                <p>Alternatively, you can also run the detect on a single image by finding the image in the media library and clicking the "detect color" link.</p>

                <p>To begin the process on all images, click the button below.</p>

                <p>
                    <a href="<?php echo get_admin_url(null, '/admin-ajax.php?action=FIC_remove_colors'); ?>" id="FIC-remove-colors" class="button hide-if-no-js">Remove All Colors</a>
                    <input type="submit" class="button button-primary hide-if-no-js" name="FIC-detect-colors" id="FIC-detect-colors" value="Detect Colors On All Images">
                </p>

                <noscript>&lt;p&gt;&lt;em&gt;You must enable Javascript in order to proceed!&lt;/em&gt;&lt;/p&gt;</noscript>

        	</form>

            <ul ID="FIC-console">
                <?php if ( isset($_REQUEST['target_image']) && $_REQUEST['target_image'] ): ?>
                    <?php if (FIC_detect_single_image($_REQUEST['target_image'])): ?>
                        <li>Detected color for image: <?php echo $_REQUEST['target_image']; ?></li>
                    <?php else: ?>
                        <li>Error detecting color for image: <?php echo $_REQUEST['target_image']; ?></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div><!-- END Wrap -->

        <?php
    }

    /* Save Takeover Settings */
    function FIC_settings_init(){
        // register_setting('FIC_settings', 'fgram_api_key');
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

	// add_filter('contextual_help', 'funkstagram_plugin_help', 10, 3);

?>