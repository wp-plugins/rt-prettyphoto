<?php
/*
Plugin Name: Royal PrettyPhoto
Plugin URI: http://www.wordpress.org/plugins/rt-prettyphoto
Description: This plugin will automatic add lightbox in wordpress post/page without disturbance.
Author: SM Mehdi Akram
Author URI: http://www.shamokaldarpon.com
Version: 1.3
*/


//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'royal_prettyphoto_plugin_links', 10, 2 );
function royal_prettyphoto_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="http://wordpress.org/plugins/rt-prettyphoto/installation/" target="_blank">' . __( 'Installation', 'rsb' ) . '</a>';
		$links[] = '<a href="http://www.royaltechbd.com/" target="_blank">' . __( 'Royal Technologies', 'rsb' ) . '</a>';
		$links[] = '<a href="http://www.shamokaldarpon.com/" target="_blank">' . __( 'Shamokal Darpon', 'rsb' ) . '</a>';
	}
	return $links;
}


add_filter('the_content', 'royal_prettyphoto_replace', 12);
function royal_prettyphoto_replace ($content)
{   global $post;
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 rel="prettyPhoto['.$post->ID.']"$6>$7</a>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}

/*Some Set-up*/
define('RT_WPP_PLUGIN_PATH', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );


/* Adding Latest jQuery from Wordpress */
function rt_wpp_latest_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'rt_wpp_latest_jquery');



/* Adding plugin javascript active file */
wp_enqueue_script('rt-wpp-plugin-active', RT_WPP_PLUGIN_PATH.'js/jquery.prettyPhoto.js', array('jquery'));
/* Adding plugin javascript active file */
wp_enqueue_script('rt-wpp-plugin-script-active', RT_WPP_PLUGIN_PATH.'js/wpp-active.js', array('jquery'));

/* Adding Plugin custm CSS file */
wp_enqueue_style('rt-wpp-plugin-style', RT_WPP_PLUGIN_PATH.'css/prettyPhoto.css');













/*************** Setting System*******************/





// Add options and populate default values on first load
function royal_prettyphoto_activate_plugin() {

	// populate plugin options array
	$royal_prettyphoto_plugin_options = array(		
		'font_family'      => '"Righteous", cursive',
		'text_color'       => '#ffffff',
		'hide_photo_control' => '0',
		'hide_photo_title' => '0',
		'hide_social_icons' => '0',
		'hide_photo_thumbnial' => '0',
		'hide_photo_nav_arrow' => '0',
		'hide_photo_expand_button' => '0'	
		);

	// create field in WP_options to store all plugin data in one field
	add_option( 'royal_prettyphoto_plugin_options', $royal_prettyphoto_plugin_options );

}


// Fire off hooks depending on if the admin settings page is used or the public website
if ( is_admin() ){ // admin actions and filters

	// Hook for adding admin menu
	add_action( 'admin_menu', 'royal_prettyphoto_admin_menu' );

	// Hook for registering plugin option settings
	add_action( 'admin_init', 'royal_prettyphoto_settings_api_init');

	// Hook to fire farbtastic includes for using built in WordPress color picker functionality
	add_action('admin_enqueue_scripts', 'royal_prettyphoto_farbtastic_script');

	// Display the 'Settings' link in the plugin row on the installed plugins list page
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'royal_prettyphoto_admin_plugin_actions', -10);

} else { // non-admin enqueues, actions, and filters


	// get the current page url
	$royal_prettyphoto_current_page_url 			= royal_prettyphoto_get_full_url();


	// get the tab url from the plugin option variable array
	$royal_prettyphoto_plugin_option_array	= get_option( 'royal_prettyphoto_plugin_options' );


	// compare the page url and the option tab - don't render the tab if the values are the same
	if ( $royal_prettyphoto_tab_url != $royal_prettyphoto_current_page_url ) {

		// hook to get option values and dynamically render css to support the tab classes
		add_action( 'wp_head', 'royal_prettyphoto_custom_css_hook' );

		// hook to get option values and write the div for the Royal PrettyPhoto to display
		add_action( 'wp_footer', 'royal_prettyphoto_body_tag_html' );
	}
}



// get the complete url for the current page
function royal_prettyphoto_get_full_url()
{
	$s 			= empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$sp 		= strtolower($_SERVER["SERVER_PROTOCOL"]);
	$protocol 	= substr($sp, 0, strpos($sp, "/")) . $s;
	$port 		= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}



// Include WordPress color picker functionality
function royal_prettyphoto_farbtastic_script($hook) {

	// only enqueue farbtastic on the plugin settings page
	if( $hook != 'settings_page_royal_pretty_photo' ) 
		return;


	// load the style and script for farbtastic
	wp_enqueue_style( 'farbtastic' );
	wp_enqueue_script( 'farbtastic' );

}



// action function to get option values and write the div for the Royal PrettyPhoto to display
function royal_prettyphoto_body_tag_html() {

	// get plugin option array and store in a variable
	$royal_prettyphoto_plugin_option_array	= get_option( 'royal_prettyphoto_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_prettyphoto_target_blank			= $royal_prettyphoto_plugin_option_array[ 'target_blank' ];

	// set the page target
	if ($royal_prettyphoto_target_blank == '1') {
		$royal_prettyphoto_target_blank = ' target="_blank"';
	}

	// Write HTML to render tab
	echo '<a href="' . esc_url( $royal_prettyphoto_tab_url ) . '"' . $royal_prettyphoto_target_blank . '><div id="royal_prettyphoto_tab" class="royal_prettyphoto_contents royal_prettyphoto_left">' . esc_html( $royal_prettyphoto_text_for_tab ) . '</div></a>';
}



// action function to add a new submenu under Settings
function royal_prettyphoto_admin_menu() {

	// Add a new submenu under Settings
	add_options_page( 'Royal PrettyPhoto Option Settings', 'Royal PrettyPhoto', 'manage_options', 'royal_pretty_photo', 'royal_prettyphoto_options_page' );
}


// Display and fill the form fields for the plugin admin page
function royal_prettyphoto_options_page() {


?>

	<div class="wrap">
	<?php screen_icon( 'plugins' ); ?>
	<h2>Royal PrettyPhoto</h2>
	<p>You can control Font Family, Photo Title with color, Social Icons, Photo Thumbnial, Nav Arrow, Photo Control & Expand Button.</p>
	<strong>NOTE: This plugin requires the WP_footer() hook to be fired from your theme.</strong></p>
	<form method="post" action="options.php">


<?php

	settings_fields( 'royal_prettyphoto_option_group' );
	do_settings_sections( 'royal_pretty_photo' );

	// get plugin option array and store in a variable
	$royal_prettyphoto_plugin_option_array	= get_option( 'royal_prettyphoto_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_prettyphoto_font_family			= $royal_prettyphoto_plugin_option_array[ 'font_family' ];
	$royal_prettyphoto_text_color			= $royal_prettyphoto_plugin_option_array[ 'text_color' ];
	$royal_prettyphoto_hide_photo_title		= $royal_prettyphoto_plugin_option_array[ 'hide_photo_title' ];
	$royal_prettyphoto_hide_photo_control	= $royal_prettyphoto_plugin_option_array[ 'hide_photo_control' ];
	$royal_prettyphoto_hide_social_icons	= $royal_prettyphoto_plugin_option_array[ 'hide_social_icons' ];
	$royal_prettyphoto_hide_photo_thumbnial	= $royal_prettyphoto_plugin_option_array[ 'hide_photo_thumbnial' ];
	$royal_prettyphoto_hide_photo_nav_arrow	= $royal_prettyphoto_plugin_option_array[ 'hide_photo_nav_arrow' ];
	$royal_prettyphoto_hide_photo_expand_button	= $royal_prettyphoto_plugin_option_array[ 'hide_photo_expand_button' ];

?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#colorpicker1').hide();
		jQuery('#colorpicker1').farbtastic("#color1");
		jQuery("#color1").click(function(){jQuery('#colorpicker1').slideToggle()});
	});
</script>


	<table class="widefat">

		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_tab_font">Select Google font for Photo Title</label></th>
		<td>
			<select name="royal_prettyphoto_plugin_options[font_family]">	
				<option value='"Righteous", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Righteous", cursive' );							?>	>Righteous</option>
				<option value='"Titan One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Titan One", cursive' );							?>	>Titan One</option>
				<option value='"Finger Paint", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Finger Paint", cursive' );							?>	>Finger Paint</option>
				<option value='"Londrina Shadow", cursive'						<?php selected( $royal_prettyphoto_font_family, '"Londrina Shadow", cursive' );						?>	>Londrina Shadow</option>
				<option value='"Autour One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Autour One", cursive' );							?>	>Autour One</option>
				<option value='"Meie Script", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Meie Script", cursive' );							?>	>Meie Script</option>
				<option value='"Sonsie One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Sonsie One", cursive' );							?>	>Sonsie One</option>
				<option value='"Kavoon", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Kavoon", cursive' );								?>	>Kavoon</option>
				<option value='"Racing Sans One", cursive'						<?php selected( $royal_prettyphoto_font_family, '"Racing Sans One", cursive' );						?>	>Racing Sans One</option>
				<option value='"Gravitas One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Gravitas One", cursive' );							?>	>Gravitas One</option>
				<option value='"Nosifer", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Nosifer", cursive' );								?>	>Nosifer</option>
				<option value='"Offside", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Offside", cursive' );								?>	>Offside</option>
				<option value='"Audiowide", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Audiowide", cursive' );							?>	>Audiowide</option>
				<option value='"Faster One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Faster One", cursive' );							?>	>Faster One</option>
				<option value='"Germania One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Germania One", cursive' );							?>	>Germania One</option>
				<option value='"Emblema One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Emblema One", cursive' );							?>	>Emblema One</option>
				<option value='"Sansita One", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Sansita One", cursive' );							?>	>Sansita One</option>
				<option value='"Creepster", cursive'							<?php selected( $royal_prettyphoto_font_family, '"Creepster", cursive' );							?>	>Creepster</option>
				<option value='"Delius Unicase", cursive'						<?php selected( $royal_prettyphoto_font_family, '"Delius Unicase", cursive' );						?>	>Delius Unicase</option>
				<option value='"Wallpoet", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Wallpoet", cursive' );								?>	>Wallpoet</option>
				<option value='"Monoton", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Monoton", cursive' );								?>	>Monoton</option>
				<option value='"Kenia", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Kenia", cursive' );								?>	>Kenia</option>
				<option value='"Monofett", cursive'								<?php selected( $royal_prettyphoto_font_family, '"Monofett", cursive' );								?>	>Monofett</option>
				<option value='"Denk One", sans-serif'							<?php selected( $royal_prettyphoto_font_family, '"Denk One", sans-serif' );							?>	>Denk One</option>
				<option value='"Ropa Sans", sans-serif'							<?php selected( $royal_prettyphoto_font_family, '"Ropa Sans", sans-serif' );							?>	>Ropa Sans</option>
				<option value='"Paytone One", sans-serif'						<?php selected( $royal_prettyphoto_font_family, '"Paytone One", sans-serif' );						?>	>Paytone One</option>
				<option value='"Russo One", sans-serif'							<?php selected( $royal_prettyphoto_font_family, '"Russo One", sans-serif' );							?>	>Russo One</option>
				<option value='"Krona One", sans-serif'							<?php selected( $royal_prettyphoto_font_family, '"Krona One", sans-serif' );							?>	>Krona One</option>
				<option value='"Rum Raisin", sans-serif'						<?php selected( $royal_prettyphoto_font_family, '"Rum Raisin", sans-serif' );						?>	>Rum Raisin</option>
				
			</select>
		</td>
		</tr>

		<tr valign="top">
		<th width="33%" scope="row"><label for="royal_prettyphoto_hide_photo_title">Hide Photo Title</label></th>
		<td ><input name="royal_prettyphoto_plugin_options[hide_photo_title]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_photo_title ); ?> /></td>
		</tr>		
		
		<tr valign="top">
			<th scope="row"><label for="royal_prettyphoto_text_color">Text color (Default #FFFFFF)</label></th>
			<td><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_prettyphoto_text_color ); ?>" name="royal_prettyphoto_plugin_options[text_color]" id="color1" />
			<div id="colorpicker1"></div></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_hide_photo_control">Hide Photo Control</label></th>
		<td><input name="royal_prettyphoto_plugin_options[hide_photo_control]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_photo_control); ?> /></td>
		</tr>		
		
		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_hide_photo_expand_button">Hide Photo Expand Button</label></th>
		<td><input name="royal_prettyphoto_plugin_options[hide_photo_expand_button]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_photo_expand_button); ?> /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_hide_social_icons">Hide Social Icons</label></th>
		<td><input name="royal_prettyphoto_plugin_options[hide_social_icons]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_social_icons); ?> /></td>
		</tr>

		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_hide_photo_thumbnial">Hide Photo Thumbnial</label></th>
		<td><input name="royal_prettyphoto_plugin_options[hide_photo_thumbnial]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_photo_thumbnial); ?> /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><label for="royal_prettyphoto_hide_photo_nav_arrow">Hide Photo Nav Arrow</label></th>
		<td><input name="royal_prettyphoto_plugin_options[hide_photo_nav_arrow]" type="checkbox" value="1" <?php checked( '1', $royal_prettyphoto_hide_photo_nav_arrow); ?> /></td>
		</tr>
		
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>



<?php
	echo '</form>';
	echo '</div>';
}



// Use Settings API to whitelist options
function royal_prettyphoto_settings_api_init() {

	register_setting( 'royal_prettyphoto_option_group', 'royal_prettyphoto_plugin_options' );

}



// Build array of links for rendering in installed plugins list
function royal_prettyphoto_admin_plugin_actions($links) {

	$links[] = '<a href="options-general.php?page=royal_pretty_photo">'.__('Settings').'</a>';
	return $links;

}



// This function runs all the css and dynamic css elements for displaying the Royal PrettyPhoto
function royal_prettyphoto_custom_css_hook() {

	// get plugin option array and store in a variable
	$royal_prettyphoto_plugin_option_array	= get_option( 'royal_prettyphoto_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_prettyphoto_font_family				= $royal_prettyphoto_plugin_option_array[ 'font_family' ];	
	$royal_prettyphoto_text_color				= $royal_prettyphoto_plugin_option_array[ 'text_color' ];
	$royal_prettyphoto_hide_photo_title			= $royal_prettyphoto_plugin_option_array[ 'hide_photo_title' ];
	$royal_prettyphoto_hide_photo_control		= $royal_prettyphoto_plugin_option_array[ 'hide_photo_control' ];
	$royal_prettyphoto_hide_social_icons		= $royal_prettyphoto_plugin_option_array[ 'hide_social_icons' ];
	$royal_prettyphoto_hide_photo_thumbnial		= $royal_prettyphoto_plugin_option_array[ 'hide_photo_thumbnial' ];
	$royal_prettyphoto_hide_photo_nav_arrow		= $royal_prettyphoto_plugin_option_array[ 'hide_photo_nav_arrow' ];
	$royal_prettyphoto_hide_photo_expand_button	= $royal_prettyphoto_plugin_option_array[ 'hide_photo_expand_button' ];

?>

<style type='text/css'>
/* Begin Royal PrettyPhoto Styles*/

@import url(https//fonts.googleapis.com/css?family=Autour+One|Meie+Script|Armata|Rum+Raisin|Sonsie+One|Kavoon|Denk+One|Gravitas+One|Racing+Sans+One|Nosifer|Ropa+Sans|Offside|Titan+One|Paytone+One|Audiowide|Righteous|Faster+One|Russo+One|Germania+One|Krona+One|Emblema+One|Creepster|Delius+Unicase|Wallpoet|Sansita+One|Monoton|Kenia|Monofett);

div.ppt {
	<?php
	if ( $royal_prettyphoto_hide_photo_title =='1' ) :
	  echo 'display: none!important;' . "\n";
	else :
	  echo ' display: none;' . "\n";
	endif;
	?>
}


.pp_nav {
	<?php
	if ( $royal_prettyphoto_hide_photo_control =='1' ) :
	  echo ' display: none !important;' . "\n";
	else :
	  
	endif;
	?> 
}


.pp_social {
	<?php
	if ( $royal_prettyphoto_hide_social_icons =='1' ) :
	  echo ' display: none !important;' . "\n";
	else :
	  
	endif;
	?> 
}


.pp_gallery {
	<?php
	if ( $royal_prettyphoto_hide_photo_thumbnial =='1' ) :
	  echo ' display: none !important;' . "\n";
	else :
	  
	endif;
	?> 
}


div.ppt{
	font-family:<?php echo $royal_prettyphoto_font_family; ?>;
	color:<?php echo $royal_prettyphoto_text_color; ?>;
}


a.pp_next, a.pp_previous 
{
	<?php
	if ( $royal_prettyphoto_hide_photo_nav_arrow =='1' ) :
	  echo ' display: none !important;' . "\n";
	else :
	  
	endif;
	?> 
}


div.pp_default .pp_expand, 
.pp_fade a.pp_expand, 
a.pp_expand, 
div.facebook .pp_expand, 
div.light_square .pp_expand, 
div.dark_square .pp_expand, 
div.dark_rounded .pp_expand, 
div.light_rounded .pp_expand
{
	<?php
	if ( $royal_prettyphoto_hide_photo_expand_button =='1' ) :
	  echo 'display: none !important;' . "\n";
	else :
	  
	endif;
	?> 
}


/* End Royal PrettyPhoto Styles*/
</style>

<?php
}
