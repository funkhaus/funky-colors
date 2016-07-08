jQuery(document).ready(function(){

    // Click on "Detect All Images" buton
    jQuery(document).on('click', '#FIC-detect-colors', function(){

        // grab CRON url
        var imagesUrl = jQuery('#FIC-settings').data('ajaxImages');
        var runUrl = jQuery('#FIC-settings').data('ajaxRun');
        var runData = {target_image: ''};

        // No url for ajax? do nothing
        if ( ! imagesUrl ) return false;

        // Disable button
        jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', 'disabled');

        // AJAX request for list of images
        jQuery.get(imagesUrl).success(function(data){

            // Append error log
            jQuery('#FIC-console').html('<h4>Detecting ' + data.length + ' Images:</h4>');

            var i = data.length;
            data.forEach(function(attachmentID){

                runData.target_image = attachmentID;
                jQuery.get(runUrl, runData).done(function(message){

                    i--;

                    jQuery('#FIC-console h4').after('<li>' + message + '</li>');

                    // was that the last request?
                    if ( i == 0 ){

                        // Enable button
                        jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', false);

                        // Done!
                        jQuery('#FIC-console h4').after('<li>Done!</li>');
                    }

                });

            });

        });
    });

    // click "remove all colors" button
    jQuery(document).on('click', '#FIC-remove-colors', function(e){

        // break link
        e.preventDefault();

        // get ajax url
        var url = jQuery(this).attr('href');

        // Disable button
        jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', 'disabled');

        // send get request
        jQuery.get(url).done(function(message){

            // add resulting message to console
            jQuery('#FIC-console').html('<h4>' + message + '</h4>');

            // Enable button
            jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', false);

        });

    });

});