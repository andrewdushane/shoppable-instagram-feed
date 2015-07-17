jQuery(document).ready(function() {
    var track_click = 0; //track number of clicks on load more button
    var data = {
		'action': 'sif_feed',
        'slice': track_click
	};
    jQuery('#sif-feed').load(ajax_object.ajax_url, data, function() {track_click++;}); //initial data to load

    if(ajax_object.total_slices <= 1) { //hide load more button if there are not multiple slices
        jQuery(".sif-load-more").hide();
    }
    
    jQuery(".sif-load-more").click(function (e) { //user clicks on button
    
        jQuery(this).hide(); //hide load more button on click
        jQuery('.sif-ajax-loading').show(); //show loading image

        if(track_click <= ajax_object.total_slices) //number of clicks is less than total slices
        {
            var moredata = {
                'action': 'sif_feed',
                'slice': track_click
	       };
            //post page number and load returned data into result element
            jQuery.post(ajax_object.ajax_url, moredata, function(response) {
            
                jQuery(".sif-load-more").show(); //show load more button
                
                jQuery("#sif-feed").append(response); //append data received from server
                
                //scroll page smoothly to button id
                jQuery("html, body").animate({scrollTop: jQuery("#sif-load-more-button").offset().top}, 500);
                
                //hide loading image
                jQuery('.sif-ajax-loading').hide(); //hide loading image once data is received
    
                track_click++; //track number of clicks
            
            }).fail(function(xhr, ajaxOptions, thrownError) { //any errors?
                alert(thrownError); //alert with HTTP error
                jQuery(".sif-load-more").show(); //bring back load more button
                jQuery('.sif-ajax-loading').hide(); //hide loading image once data is received
            });
            
            
            if(track_click >= ajax_object.total_slices-1) //compare number of clicks with page number
            {
                //show end of feed when all slices have been loaded
                jQuery(".sif-load-more").attr("disabled", "disabled").text("End of Feed");
            }
         }
          
    });
});
