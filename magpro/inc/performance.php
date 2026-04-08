<?php
/**
 * MagPro Performance Optimizations
 *
 * Targets 90+ PageSpeed score on mobile and desktop.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add resource hints for faster loading.
 *
 * @param array  $urls          URLs to hint.
 * @param string $relation_type Hint type.
 * @return array
 */
function magpro_resource_hints( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls[] = '//fonts.googleapis.com';
		$urls[] = '//fonts.gstatic.com';
		$urls[] = '//pagead2.googlesyndication.com';
		$urls[] = '//www.googletagmanager.com';
	}

	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
			'crossorigin',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'magpro_resource_hints', 10, 2 );

/**
 * Output critical CSS inline in the head.
 */
function magpro_critical_css() {
	$critical_css_file = get_template_directory() . '/css/base.css';
	if ( file_exists( $critical_css_file ) ) {
		echo '<style id="magpro-critical-css">';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		echo magpro_minify_css( file_get_contents( $critical_css_file ) );
		echo '</style>';
	}
}
add_action( 'wp_head', 'magpro_critical_css', 1 );

/**
 * Minify CSS string.
 *
 * @param string $css CSS content.
 * @return string Minified CSS.
 */
function magpro_minify_css( $css ) {
	$css = preg_replace( '/\/\*(?!!)[\s\S]*?\*\//', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([\{\};:,>~+])\s*/', '$1', $css );
	$css = trim( $css );
	return $css;
}

/**
 * Load non-critical CSS asynchronously.
 *
 * @param string $tag    Script/link tag.
 * @param string $handle Handle name.
 * @return string Modified tag.
 */
function magpro_async_css( $tag, $handle ) {
	$async_handles = array(
		'magpro-layout',
		'magpro-magazine',
		'magpro-widgets',
		'magpro-responsive',
	);

	if ( in_array( $handle, $async_handles, true ) ) {
		$tag = str_replace(
			"media='all'",
			"media='print' onload=\"this.media='all'\"",
			$tag
		);
		// Add noscript fallback.
		$noscript = str_replace( "media='print' onload=\"this.media='all'\"", "media='all'", $tag );
		$tag     .= '<noscript>' . $noscript . '</noscript>';
	}

	return $tag;
}
add_filter( 'style_loader_tag', 'magpro_async_css', 10, 2 );

/**
 * Add defer/async to scripts.
 *
 * @param string $tag    Script tag.
 * @param string $handle Handle name.
 * @return string Modified tag.
 */
function magpro_defer_scripts( $tag, $handle ) {
	$defer_handles = array(
		'magpro-main',
		'magpro-navigation',
		'comment-reply',
	);

	if ( in_array( $handle, $defer_handles, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'magpro_defer_scripts', 10, 2 );

/**
 * Add native lazy loading to all content images.
 *
 * @param string $content Post content.
 * @return string Modified content.
 */
function magpro_lazy_load_content_images( $content ) {
	if ( is_admin() || is_feed() || wp_doing_ajax() ) {
		return $content;
	}

	// Add loading="lazy" and decoding="async" to images that don't have them.
	$content = preg_replace(
		'/<img(?![^>]*loading=)([^>]*)>/i',
		'<img loading="lazy"$1>',
		$content
	);

	$content = preg_replace(
		'/<img(?![^>]*decoding=)([^>]*)>/i',
		'<img decoding="async"$1>',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'magpro_lazy_load_content_images', 99 );

/**
 * Add lazy loading to iframes (YouTube, etc.).
 *
 * @param string $content Post content.
 * @return string Modified content.
 */
function magpro_lazy_load_iframes( $content ) {
	if ( is_admin() || is_feed() ) {
		return $content;
	}

	$content = preg_replace(
		'/<iframe(?![^>]*loading=)([^>]*)>/i',
		'<iframe loading="lazy"$1>',
		$content
	);

	return $content;
}
add_filter( 'the_content', 'magpro_lazy_load_iframes', 99 );

/**
 * Remove unnecessary WordPress head bloat.
 */
function magpro_cleanup_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Remove emoji scripts.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'magpro_cleanup_head' );

/**
 * Disable the global styles inline CSS if not using block editor.
 */
function magpro_dequeue_global_styles() {
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_enqueue_scripts', 'magpro_dequeue_global_styles', 100 );

/**
 * Add preload for hero images on single posts.
 */
function magpro_preload_hero_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'magpro-hero' );
		if ( $image ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $image[0] ) . '" fetchpriority="high">' . "\n";
		}
	}
}
add_action( 'wp_head', 'magpro_preload_hero_image', 2 );

/**
 * Set fetchpriority="high" on above-the-fold images.
 *
 * @param array $attr       Image attributes.
 * @param WP_Post $attachment Attachment post.
 * @param string $size       Image size.
 * @return array
 */
function magpro_fetchpriority_images( $attr, $attachment, $size ) {
	if ( is_singular() && in_the_loop() && 0 === did_action( 'magpro_after_hero' ) ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = 'eager';
		unset( $attr['loading'] );
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'magpro_fetchpriority_images', 10, 3 );

/**
 * Disable self-pingbacks.
 *
 * @param array $links Ping links.
 */
function magpro_disable_self_pingbacks( &$links ) {
	$home = home_url();
	foreach ( $links as $l => $link ) {
		if ( 0 === strpos( $link, $home ) ) {
			unset( $links[ $l ] );
		}
	}
}
add_action( 'pre_ping', 'magpro_disable_self_pingbacks' );

/**
 * Add WebP support detection via accept header.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function magpro_webp_upload_support( $mimes ) {
	$mimes['webp'] = 'image/webp';
	$mimes['avif'] = 'image/avif';
	return $mimes;
}
add_filter( 'upload_mimes', 'magpro_webp_upload_support' );

/**
 * Limit post revisions to reduce database bloat.
 *
 * @param int $num  Number of revisions.
 * @return int
 */
function magpro_limit_revisions( $num ) {
	return 5;
}
add_filter( 'wp_revisions_to_keep', 'magpro_limit_revisions' );

/**
 * Optimize heartbeat API interval.
 *
 * @param array $settings Heartbeat settings.
 * @return array
 */
function magpro_heartbeat_settings( $settings ) {
	$settings['interval'] = 60;
	return $settings;
}
add_filter( 'heartbeat_settings', 'magpro_heartbeat_settings' );

/**
 * Disable heartbeat on frontend.
 */
function magpro_disable_heartbeat_frontend() {
	if ( ! is_admin() ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'init', 'magpro_disable_heartbeat_frontend', 1 );
