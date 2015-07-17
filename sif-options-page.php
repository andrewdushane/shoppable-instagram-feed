<div class="wrap">
	
	<div id="icon-options-general" class="icon32"></div>
	<h2>Shoppable Instagram Feed Settings</h2>
	
    <?php if( isset($sif_update_message) && $sif_update_message != '' ) { ?>
        <div class="updated">
            <p><?php echo $sif_update_message; ?></p>
        </div>
    <?php } if ( isset($sif_error_message) && $sif_error_message != '' ) { ?>
        <div class="error">
            <p><?php echo $sif_error_message; ?></p>
        </div>
    <?php } ?>
    
	<div id="poststuff">
	
		<div id="post-body" class="metabox-holder columns-2">
		
			<!-- main content -->
			<div id="post-body-content">
				
				<div class="meta-box-sortables ui-sortable">
                    <?php if ( $sif_show_shortcode ) { ?>
                    <div class="postbox">
                        <h3>Embed Your Feed</h3>
                        <div class="inside">
                            <p>Add this shortcode (including brackets) to the page where you want to display your shoppable feed:</p>
                            <pre><code>[sif_display]</code></pre>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ( isset ( $sif_options_feed_updated ) && is_array( $sif_options_feed_updated ) ) { ?>
                    <div class="postbox">
                        <h3>Instagram Feed</h3>
                        <div class="inside sif-admin-feed">
                            <p>Select which images you want to appear in your feed. For each image, enter the URL for the corresponding item in the store.</p>
                            <form name="sif_feed_settings" method="post" action="">
                                <input type="hidden" name="feed_submitted" value='Y'>
                                <?php sif_admin_feed_display( $sif_options_feed_updated , $sif_pagenum ); //Display images in IG feed with options
                                if ( $sif_page_links ) { //create pagination links for IG feed
                                    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">' . $sif_page_links . '</div></div>';
                                } ?>
                                <p><input class="button-primary" type="submit" name="Submit" value="Update Feed Settings" /></p>
                            </form>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="postbox">
						<?php if( !isset($sif_access_token) || $sif_access_token == '' || $sif_reauthorize == 'Y' ) { //Show authorization form if not authorized ?>
                        <h3>Authorize Instagram Client</h3>
                        <div class="inside">
                            <p>To create your feed, you'll need to set up a Client with Instagram. Follow these steps in order and you'll be all set.</p>
							<ol>
								<li>Log in to your account on <a href="http://instagram.com" target="_blank">instagram.com</a>.
								<li>Visit <a href="http://instagram.com/developer" target="_blank">instagram.com/developer</a>. Register as a developer if you haven't previously. The process is easy and free.
								<li>After registering, click on <a href="https://instagram.com/developer/clients/register/" target="_blank">Register a New Client</a> (this link will also take you to the Client registration page). On Instagram, a Client is really just an app.
								<li>For Application name, enter Shoppable Feed. 
								<li>For description enter "Create shoppable Instagram feed."
								<li>For Website URL, enter the URL of your website (<?php echo site_url(); ?>).
								<li>For Redirect URI, enter <?php echo $redirect_uri; ?> <br>(this must be copied and pasted exactly).
								<li>Enter your email for Support Email.
								<li>After creating your client, you will see a Client ID and Client Secret under Client Info. Copy and paste them into the fields below.
                                <li>Click Authorize, and authorize the app when prompted.
							</ol>
							<form name="sif_client_authorization" method="post" action="">
                                <input type="hidden" name="client_submitted" value='Y'>
								<table class="form-table">
									<tr>
										<td><label for="client_id">Client ID:</label></td>
										<td><input name="client_id" id="client_id" type="text" value="<?php if(isset($client_id)) echo $client_id; ?>" class="regular-text" /></td>
									</tr>
									<tr>
										<td><label for="client_secret">Client Secret:</label></td>
										<td><input name="client_secret" id="client_secret" type="text" value="<?php if(isset($client_secret)) echo $client_secret; ?>" class="regular-text" /></td>
									</tr>
								</table>
								<p><input class="button-primary" type="submit" name="Submit" value="Authorize" /></p>
							</form>
                        </div>
                        <?php } else { ?>
                        <h3>Instagram Client authorized</h3>
                        <div class="inside">
                            <p>Having trouble accessing your feed? Try reauthorizing your Instagram client.</p>
                            <form name="sif_client_reauthorize" method="post" action="">
                                <input type="hidden" name="sif_reauthorize" value='Y'>
                                <p><input class="button-primary" type="submit" name="Reauthorize" value="Reauthorize" /></p>
                            </form>
                        </div><!-- .inside -->
                        <?php } ?>
					</div> <!-- .postbox -->
                    <div class="postbox">
						<h3>Delete Settings</h3>
                        <div class="inside">
                            <p><strong>Warning!</strong> This will delete all settings. You will need to supply Instagram client info and re-authorize after deleting.</p>
                            <form name="delete_sif_settings" method="post" action="">
                                <input type="hidden" name="delete_options_submitted" value='Y'>
								<p><input class="button-primary" type="submit" name="Submit" value="Delete Settings" /></p>
							</form>
                        </div> <!-- .inside -->
					</div> <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables .ui-sortable -->
				
			</div> <!-- post-body-content -->
			
			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				
				<div class="meta-box-sortables">
					<?php if( isset($sif_access_token) && $sif_access_token != '' ) { //Show profile info if authorized ?>
					<div class="postbox">
                        <div class="inside sif-access-user">
                            <h2><?php echo $sif_access_full_name; ?></h2>
							<p><a href="http://instagram.com/<?php echo $sif_access_username; ?>" target="_blank">@<?php echo $sif_access_username; ?></a></p>
                            <img src="<?php echo $sif_access_profile ?>" alt="Instagram Profile Picture">
                            <p>Authorized: <?php echo $sif_access_last_updated; ?></p>
						</div><!-- .inside -->
					</div>  <!-- .postbox -->
                    <?php } ?>
                    <div class="postbox">
						<div class="inside">
                            <h2>Developer Information</h2>
							<p>Author: Andrew Dushane, <a href="http://premierprograming.com" target="_blank">Premier Programing</a></p>
						</div><!-- .inside -->
						
					</div>  <!-- .postbox -->
					
				</div> <!-- .meta-box-sortables -->
				
			</div> <!-- #postbox-container-1 .postbox-container -->
			
		</div> <!-- #post-body .metabox-holder .columns-2 -->
		
		<br class="clear">
	</div> <!-- #poststuff -->
	
</div> <!-- .wrap -->
