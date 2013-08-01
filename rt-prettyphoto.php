<?php
/*
Plugin Name: Royal PrettyPhoto
Plugin URI: http://wordpress.org/plugins/wp-prettyphoto
Description: This plugin will automatic add lightbox in wordpress post/page without disturbance.
Author: Mehdi Akram
Author URI: http://shamokaldarpon.com
Version: 1.0
*/

add_filter('the_content', 'royal_prettyphoto_replace', 12);
add_filter('get_comment_text', 'royal_prettyphoto_replace');
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



?>