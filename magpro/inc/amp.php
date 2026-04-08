<?php
/**
 * MagPro AMP Compatibility
 *
 * Provides AMP-compatible output when the AMP plugin is active.
 * Ensures AdSense ads and images work in AMP mode.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if current request is AMP.
 *
 * @return bool
 */
function magpro_is_amp() {
	return function_exists( 'amp_is_request' ) && amp_is_request();
}

/**
 * Add AMP-compatible ad support.
 *
 * @param string $content Post content.
 * @return string Modified content.
 */
function magpro_amp_ad_support( $content ) {
	if ( ! magpro_is_amp() ) {
		return $content;
	}

	// Convert AdSense ins tags to amp-ad.
	$publisher_id = magpro_get_option( 'adsense_publisher_id' );
	if ( empty( $publisher_id ) ) {
		return $content;
	}

	$content = preg_replace(
		'/<ins class="adsbygoogle"[^>]*data-ad-slot="([^"]*)"[^>]*><\/ins>\s*<script>[^<]*<\/script>/i',
		'<amp-ad width="100vw" height="320" type="adsense" data-ad-client="' . esc_attr( $publisher_id ) . '" data-ad-slot="$1" data-auto-format="rspv" data-full-width=""><div overflow=""></div></amp-ad>',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'magpro_amp_ad_support', 100 );

/**
 * Add AMP-compatible styles.
 */
function magpro_amp_styles() {
	if ( ! magpro_is_amp() ) {
		return;
	}

	// Output AMP-safe custom styles.
	echo '<style amp-custom>';
	echo '.magpro-ad-wrapper{margin:1.5rem 0;text-align:center}';
	echo '.magpro-ad-label{display:block;font-size:0.75rem;color:#999;margin-bottom:0.25rem}';
	echo '</style>';
}
add_action( 'amp_post_template_css', 'magpro_amp_styles' );

/**
 * Remove non-AMP scripts when in AMP mode.
 */
function magpro_amp_remove_scripts() {
	if ( ! magpro_is_amp() ) {
		return;
	}

	wp_dequeue_script( 'magpro-main' );
	wp_dequeue_script( 'magpro-navigation' );
}
add_action( 'wp_enqueue_scripts', 'magpro_amp_remove_scripts', 200 );

/**
 * Add AMP boilerplate to head when AMP plugin is active.
 */
function magpro_amp_boilerplate() {
	if ( ! magpro_is_amp() ) {
		return;
	}

	echo '<link rel="amphtml" href="' . esc_url( get_permalink() ) . '?amp">' . "\n";
}
add_action( 'wp_head', 'magpro_amp_boilerplate', 0 );

/**
 * Ensure images are AMP-compatible in content.
 *
 * @param string $content Post content.
 * @return string
 */
function magpro_amp_images( $content ) {
	if ( ! magpro_is_amp() ) {
		return $content;
	}

	// Convert img tags to amp-img.
	$content = preg_replace_callback(
		'/<img\s([^>]*)>/i',
		function ( $matches ) {
			$attrs = $matches[1];

			// Extract width and height.
			preg_match( '/width=["\'](\d+)["\']/', $attrs, $w );
			preg_match( '/height=["\'](\d+)["\']/', $attrs, $h );

			$width  = ! empty( $w[1] ) ? $w[1] : '800';
			$height = ! empty( $h[1] ) ? $h[1] : '450';

			// Extract src.
			preg_match( '/src=["\']([^"\']+)["\']/', $attrs, $s );
			$src = ! empty( $s[1] ) ? $s[1] : '';

			// Extract alt.
			preg_match( '/alt=["\']([^"\']*)["\']/', $attrs, $a );
			$alt = ! empty( $a[1] ) ? $a[1] : '';

			return '<amp-img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" layout="responsive"></amp-img>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'magpro_amp_images', 200 );
