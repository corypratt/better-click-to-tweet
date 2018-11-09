<?php
/*
Plugin Name: MC's Better Click To Tweet
Description: Based on Ben Meredith's 'Better Click To Tweet'. Add's facebook functionality and modifies some of the look/feel
Version: 5.6.0
Author: Ben Meredith
Author URI: https://www.betterclicktotweet.com
Plugin URI: https://wordpress.org/plugins/better-click-to-tweet/
License: GPL2
Text Domain: better-click-to-tweet
*/

defined( 'ABSPATH' ) or die( "No soup for you. You leave now." );

include 'i18n-module.php';
include 'bctt_options.php';
include 'bctt-i18n.php';
include 'admin-nags.php';

/*
*  	Strips the html, shortens the text (after checking for mb_internal_encoding compatibility)
*	and adds an ellipsis if the text has been shortened
*
* 	@param string $input raw text string from the shortcode
* 	@param int $length length for truncation
* 	@param bool $ellipsis boolean for whether the text has been truncated
* 	@param bool $strip_html ensures that html is stripped from text string
*/


function bctt_shorten( $input, $length, $ellipsis = true, $strip_html = true ) {

	if ( $strip_html ) {
		$input = strip_tags( $input );
	}

	/*
	* 	Checks to see if the mbstring php extension is loaded, for optimal truncation.
	*	If it's not, it bails and counts the characters based on utf-8.
	*	What this means for users is that non-Roman characters will only be counted
	*	correctly if that extension is loaded. Contact your server admin to enable the extension.
	*/

	if ( function_exists( 'mb_internal_encoding' ) ) {
		if ( mb_strlen( $input ) <= $length ) {
			return $input;
		}

		$last_space   = mb_strrpos( mb_substr( $input, 0, $length ), ' ' );
		$trimmed_text = mb_substr( $input, 0, $last_space );

		if ( $ellipsis ) {
			$trimmed_text .= "…";
		}

		return $trimmed_text;

	} else {

		if ( strlen( $input ) <= $length ) {
			return $input;
		}

		$last_space   = strrpos( substr( $input, 0, $length ), ' ' );
		$trimmed_text = substr( $input, 0, $last_space );

		if ( $ellipsis ) {
			$trimmed_text .= "…";
		}

		return $trimmed_text;
	}
}

;

/*
* 	Creates the bctt shortcode
*
* 	@since 0.1
* 	@param array $atts an array of shortcode attributes
*
*/

function bctt_shortcode( $atts ) {

	$atts = shortcode_atts( apply_filters( 'bctt_atts', array(
		'tweet'    => '',
		'via'      => 'yes',
		'username' => 'not-a-real-user',
		'url'      => 'yes',
		'nofollow' => 'no',
		'prompt'   => sprintf( _x( 'Click To Tweet', 'Text for the box on the reader-facing box', 'better-click-to-tweet' ) )
	) ), $atts, 'bctt' );

	//since 4.7: adds option to add in a per-box username to the tweet
	if ( $atts['username'] != 'not-a-real-user' ) {

		$handle = $atts['username'];

	} else {

		$handle = get_option( 'bctt-twitter-handle' );

	}

	if ( function_exists( 'mb_internal_encoding' ) ) {

		$handle_length = ( 6 + mb_strlen( $handle ) );

	} else {

		$handle_length = ( 6 + strlen( $handle ) );

	}

	if ( ! empty( $handle ) && $atts['via'] != 'no' ) {

		$via = $handle;
		$related = $handle;
	} else {

		$via = null;
		$related = '';

	}

	if ( $atts['via'] != 'yes' ) {
		$via = null;
		$handle_length = 0;

	}

	$text = $atts['tweet'];

	if ( filter_var( $atts['url'], FILTER_VALIDATE_URL ) ) {

		$bcttURL = $atts['url'];

	} elseif ( $atts['url'] != 'no' ) {

		if ( get_option( 'bctt-short-url' ) != false ) {

			$bcttURL  = wp_get_shortlink();

		} else {

			$bcttURL = get_permalink();

		}

	} else {

		$bcttURL = null;

	}

	if ( $atts['url'] != 'no' ) {

		$short = bctt_shorten( $text, ( 253 - ( $handle_length ) ) );

	} else {

		$short = bctt_shorten( $text, ( 280 - ( $handle_length ) ) );

	}

	if ( $atts['nofollow'] != 'no' ) {

		$rel = 'rel="noopener noreferrer nofollow"';

	} else {

		$rel = 'rel="noopener noreferrer"';

	}

	$bctt_span_class        = apply_filters( 'bctt_span_class', 'bctt-click-to-tweet' );
	$bctt_text_span_class   = apply_filters( 'bctt_text_span_class', 'bctt-ctt-text' );
	$bctt_button_span_class = apply_filters( 'bctt_button_span_class', 'bctt-ctt-btn' );


	$href  = add_query_arg( array(
		'url'     => $bcttURL,
		'text'    => rawurlencode( html_entity_decode( $short ) ),
		'via'     => $via,
		'related' => $related,
	), 'https://twitter.com/intent/tweet' );

	$uniqueID = mt_rand(10,100);
	if ( ! is_feed() ) {

		$output = "<div class='" . esc_attr( $bctt_span_class ) . "'><span class='" . esc_attr( $bctt_text_span_class ) . "'>" . esc_html( $short ) . "</span><div class='share-buttons__group'><a href='" . esc_url( $href ) . "' target='_blank' class='tweet-button'" . $rel . "><svg id='twitter_logo' data-name='twitter logo' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 250 203.14'><defs><style>.cls-1{fill:#1da1f2;}</style></defs><title>twitter</title><path class='cls-1' d='M153.62,301.59c94.34,0,145.94-78.16,145.94-145.94,0-2.22,0-4.43-.15-6.63A104.36,104.36,0,0,0,325,122.47a102.24,102.24,0,0,1-29.46,8.07,51.46,51.46,0,0,0,22.55-28.37,102.76,102.76,0,0,1-32.57,12.45,51.34,51.34,0,0,0-87.41,46.78A145.63,145.63,0,0,1,92.4,107.81a51.33,51.33,0,0,0,15.88,68.47A50.84,50.84,0,0,1,85,169.86v.65a51.31,51.31,0,0,0,41.15,50.28,51.2,51.2,0,0,1-23.16.88,51.36,51.36,0,0,0,47.92,35.62A102.9,102.9,0,0,1,75,278.55a145.22,145.22,0,0,0,78.62,23' transform='translate(-75 -98.45)'/></svg></a><div id='fb_share_" . $uniqueID . "' class='share-button'" . $rel . "><svg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 58 58'><defs><style>.cls-1{fill:#898f9c;}.cls-2{fill:#fff;}</style></defs><path class='cls-1' d='M54.8,0H3.2A3.2,3.2,0,0,0,0,3.2V54.8A3.2,3.2,0,0,0,3.2,58H31V35.57H23.45V26.79H31V20.33c0-7.49,4.58-11.57,11.26-11.57A64.2,64.2,0,0,1,49,9.1v7.83h-4.6c-3.64,0-4.35,1.72-4.35,4.26v5.59h8.7l-1.13,8.78H40V58H54.8A3.2,3.2,0,0,0,58,54.8V3.2A3.2,3.2,0,0,0,54.8,0Z'/><path id='f' class='cls-2' d='M40,58V35.57h7.57l1.13-8.78H40V21.2c0-2.54.71-4.26,4.35-4.26H49V9.1a64.2,64.2,0,0,0-6.75-.34C35.56,8.76,31,12.84,31,20.33v6.46H23.45v8.78H31V58Z'/></svg></div></div></div><script>document.getElementById('fb_share_" . $uniqueID . "').onclick = function() { FB.ui({ method: 'share', display: 'popup', href: '" . $bcttURL . "', quote: '" . $text . "' }, function(response){}); }</script>";
	} else {

		$output = "<hr /><p><em>" . esc_html( $short ) . "</em><br /><a href='" . esc_url( $href ) . "' target='_blank' " . $rel . " >" . esc_html( $atts['prompt'] ) . "</a><br /><hr />";

	}
	return apply_filters( 'bctt_output', $output, $short, $bctt_button_span_class, $bctt_span_class, $bctt_text_span_class, $href, $rel, $atts );
}

add_shortcode( 'bctt', 'bctt_shortcode' );

/*
 * Load the stylesheet to style the output.
 *
 * As of v4.1, defaults to a custom stylesheet
 * located in the root of the uploads folder at wp-content/uploads/bcttstyle.css and falls
 * back to the stylesheet bundled with the plugin if the custom sheet is not present.
 *
 * @since 0.1
 *
*/

function bctt_scripts() {

	if ( bctt_is_default_styles_dequeued() ) {
		foreach ( wp_load_alloptions() as $option => $value ) {
			if ( strpos( $option, 'bcct_' ) === 0 ) {
				delete_option( $option );
			}
		}

		return;
	}

	$custom = bctt_is_custom_stylesheet();

	$tag      = $custom ? 'bcct_custom_style' : 'bcct_style';
	$location = bctt_get_stylesheet_url();

	$version = $custom ? '1.0' : '3.0';

	wp_register_style( $tag, $location, false, $version, 'all' );

	wp_enqueue_style( $tag );
}


add_action( 'wp_enqueue_scripts', 'bctt_scripts', 10 );

function bctt_fb_sdk() {
?>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '<?php echo esc_attr( get_option( 'bctt-fb-app-id' ) ); ?>',
      xfbml      : true,
      version    : 'v3.2'
    });
    FB.AppEvents.logPageView();
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
<?php
}
add_action( 'wp_head', 'bctt_fb_sdk' );

/**
 * Check if default stylesheet must not be enqueued
 *
 * @return bool
 */
function bctt_is_default_styles_dequeued() {
	return (bool) get_option( 'bctt_disable_css' );
}


/**
 * Check if there's a custo stylesheet that will be enqueued
 */
function bctt_is_custom_stylesheet() {
	return file_exists( bctt_get_custom_styles_path() );
}

/**
 * Return the BCTT stylesheet URL
 *
 * Return custom styles URL if the file exists or the default one otherwise
 *
 * @return string
 */
function bctt_get_stylesheet_url() {
	return bctt_is_custom_stylesheet() ? bctt_get_custom_styles_url() : bctt_get_styles_url();
}


/**
 * Return the custom stylesheet path
 *
 * @return string
 */
function bctt_get_custom_styles_path() {
	$dir = wp_upload_dir();
	return $dir['basedir'] . '/bcttstyle.css';
}

/**
 * Return the custom stylesheet URL
 *
 * @return string
 */
function bctt_get_custom_styles_url() {
	$dir = wp_upload_dir();
	return $dir['baseurl'] . '/bcttstyle.css';
}

/**
 * Return the default stylesheet path
 *
 * @return string
 */
function bctt_get_styles_url() {
	return plugins_url( 'assets/css/styles.css', __FILE__ );
}

/*
 * Delete options and shortcode on uninstall
 *
 * @since 0.1
*/

function bctt_on_uninstall() {

	delete_option( 'bctt-twitter-handle' );

	delete_option( 'bctt-short-url' );

	delete_option( 'bctt_disable_css' );

	delete_option( 'bctt_style_enqueued' );

	remove_shortcode( 'bctt' );

	delete_metadata( 'user', 0, 'bctt_has_dismissed_nag', '', true );


}



register_uninstall_hook( __FILE__, 'bctt_on_uninstall' );

function bctt_options_link( $links ) {

	$settingsText = sprintf( _x( 'Settings', 'text for the link on the plugins page', 'better-click-to-tweet' ) );

	$settings_link = '<a href="admin.php?page=better-click-to-tweet">' . esc_html( $settingsText ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;

}

$bcttlink = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$bcttlink", 'bctt_options_link' );

/**
 * Register Block
 */
add_action( 'plugins_loaded', function () {
	if ( function_exists( 'register_block_type' ) ) {
		require_once( plugin_dir_path( __FILE__ ) . 'assets/block/init.php' );
	}
} );