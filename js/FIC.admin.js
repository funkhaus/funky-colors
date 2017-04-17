var runAllDetects = function(ids, progress, complete){

    var i = 0;

    var runSingleDetect = function(id){

        jQuery.ajax({
            url: jQuery('#FIC-settings').data('ajaxRun'),
            data: {target_image: id}
        })
        .always(function(data){
            var message = typeof data == 'string' ? data : 'error detecting image: ' + ids[i];

            // update progress
            if ( typeof progress == 'function' ) progress(message)

            // inc
            i++;

            // reached the end!
            if ( i < ids.length )
                runSingleDetect(ids[i])
            else if ( typeof complete == 'function' )
                complete(i)

        })

    }

    // kick loop
    runSingleDetect(ids[i]);
}

jQuery(document).ready(function(){

    // Click on "Detect All Images" buton
    jQuery(document).on('click', '#FIC-detect-colors', function(){

        // grab CRON url
        var imagesUrl = jQuery('#FIC-settings').data('ajaxImages');

        // No url for ajax? do nothing
        if ( ! imagesUrl ) return false;

        // Disable button
        jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', 'disabled');

        // AJAX request for list of images
        jQuery.get(imagesUrl).success(function(data){

            // Append error log
            jQuery('#FIC-console').html('<h4>Detecting ' + data.length + ' Images:</h4>');

            // recursive function to handle all detects
            runAllDetects(data, function(message){
                // progress

                jQuery('#FIC-console h4').after('<li>' + message + '</li>');

            }, function(total){
                // complete!

                // un-disable
                jQuery('#FIC-detect-colors, #FIC-remove-colors').attr('disabled', false);

                // Done!
                jQuery('#FIC-console h4').after('<li>Done!</li>');
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