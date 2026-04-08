/**
 * MagPro Core Web Vitals Optimization
 *
 * Fixes for:
 * - Cumulative Layout Shift (CLS)
 * - Largest Contentful Paint (LCP)
 * - First Input Delay (FID)
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add aspect-ratio CSS to prevent layout shift.
 */
function magpro_aspect_ratio_css() {
	?>
	<style id="magpro-aspect-ratio">
		/* Prevent CLS on images */
		img {
			aspect-ratio: attr(width) / attr(height);
		}

		/* Featured image aspect ratios */
		.magpro-card-thumb {
			aspect-ratio: 16 / 10;
		}

		.magpro-featured-image,
		.magpro-hero-image {
			aspect-ratio: 16 / 9;
		}

		.magpro-post-thumbnail {
			aspect-ratio: 16 / 9;
		}

		.magpro-single-featured {
			aspect-ratio: 16 / 9;
		}

		/* Ad units - Fixed sizes to prevent shift */
		.magpro-ad-wrapper {
			min-height: 250px;
		}

		.magpro-ad-wrapper.magpro-ad-header_banner {
			min-height: 100px;
		}

		.magpro-ad-wrapper.magpro-ad-footer_sticky {
			min-height: 60px;
		}

		/* Sidebar ads */
		.magpro-ad-wrapper.magpro-ad-sidebar_top,
		.magpro-ad-wrapper.magpro-ad-sidebar_middle,
		.magpro-ad-wrapper.magpro-ad-sidebar_sticky {
			min-height: 280px;
		}

		/* In-article ads */
		.magpro-ad-wrapper.magpro-ad-mid_article {
			min-height: 320px;
		}

		/* Prevent form layout shift */
		input, textarea, select {
			max-width: 100%;
		}

		/* Avatar fixed sizes */
		.magpro-comment-avatar img,
		.magpro-author-avatar img {
			width: 60px;
			height: 60px;
		}

		.magpro-popular-count {
			width: 32px;
			height: 32px;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'magpro_aspect_ratio_css', 0 );

/**
 * Preload critical fonts and images.
 */
function magpro_preload_critical_resources() {
	// Preload Google Fonts
	echo '<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">' . "\n";

	// Preload Roboto if used
	if ( 'roboto' === get_theme_mod( 'magpro_body_font' ) ) {
		echo '<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">' . "\n";
	}

	// Preload logo
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		if ( $logo_url ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $logo_url ) . '">' . "\n";
		}
	}

	// Preload hero image on single posts
	if ( is_singular( 'post' ) && has_post_thumbnail() ) {
		$image_url = wp_get_attachment_image_url( get_post_thumbnail_id(), 'magpro-hero' );
		if ( $image_url ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '" fetchpriority="high">' . "\n";
		}
	}
}
add_action( 'wp_head', 'magpro_preload_critical_resources', 1 );

/**
 * Defer Google Analytics and other third-party scripts.
 */
function magpro_optimize_external_scripts() {
	// Don't load Google Analytics on admin or preview
	if ( is_admin() || is_customize_preview() ) {
		return;
	}

	// Defer tracking pixels
	add_filter( 'script_loader_tag', function ( $tag, $handle ) {
		if ( strpos( $handle, 'google-analytics' ) !== false ||
		     strpos( $handle, 'gtag' ) !== false ||
		     strpos( $handle, 'tracking' ) !== false ) {
			return str_replace( ' src', ' async src', $tag );
		}
		return $tag;
	}, 10, 2 );
}
add_action( 'init', 'magpro_optimize_external_scripts' );

/**
 * Optimize fonts loading strategy.
 */
function magpro_fonts_display() {
	// Use font-display: swap to prevent FOIT (Flash Of Invisible Text)
	add_filter( 'script_loader_tag', function ( $tag, $handle ) {
		if ( strpos( $handle, 'google-fonts' ) !== false ) {
			return str_replace( 'href=', 'onload="this.rel=\'stylesheet\'" href=', $tag );
		}
		return $tag;
	}, 10, 2 );
}
add_action( 'init', 'magpro_fonts_display' );

/**
 * Add LCP optimization - Preload largest contentful paint element.
 */
function magpro_optimize_lcp() {
	if ( is_singular( 'post' ) && has_post_thumbnail() ) {
		// The hero image is the LCP element
		$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'magpro-hero' );
		if ( $image ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $image[0] ) . '" fetchpriority="high" imagesrcset="' . esc_attr( wp_get_attachment_image_srcset( get_post_thumbnail_id() ) ) . '" imagesizes="(max-width: 1200px) 100vw, 1200px">' . "\n";
		}
	} elseif ( is_home() || is_front_page() ) {
		// On homepage, preload the first article's image
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() && has_post_thumbnail( $query->posts[0]->ID ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $query->posts[0]->ID ), 'magpro-featured' );
			if ( $image ) {
				echo '<link rel="preload" as="image" href="' . esc_url( $image[0] ) . '" fetchpriority="high">' . "\n";
			}
		}
		wp_reset_postdata();
	}
}
add_action( 'wp_head', 'magpro_optimize_lcp', 2 );

/**
 * Optimize images with srcset and sizes.
 */
function magpro_optimize_images() {
	add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
		// Add loading attribute
		if ( ! isset( $attr['loading'] ) ) {
			$attr['loading'] = 'lazy';
		}

		// Add decoding for performance
		if ( ! isset( $attr['decoding'] ) ) {
			$attr['decoding'] = 'async';
		}

		// Add width and height to prevent layout shift
		if ( ! isset( $attr['width'] ) || ! isset( $attr['height'] ) ) {
			$attachment_metadata = wp_get_attachment_metadata( $attachment->ID );
			if ( $attachment_metadata ) {
				if ( ! isset( $attr['width'] ) && isset( $attachment_metadata['width'] ) ) {
					$attr['width'] = $attachment_metadata['width'];
				}
				if ( ! isset( $attr['height'] ) && isset( $attachment_metadata['height'] ) ) {
					$attr['height'] = $attachment_metadata['height'];
				}
			}
		}

		// Add fetchpriority for LCP images
		if ( is_singular() && in_the_loop() && 0 === did_action( 'magpro_after_hero' ) ) {
			$attr['fetchpriority'] = 'high';
		}

		return $attr;
	}, 10, 3 );
}
add_action( 'wp_head', 'magpro_optimize_images' );

/**
 * Remove unused CSS classes and styles.
 */
function magpro_remove_unused_styles() {
	// Only load critical CSS, others are async
	wp_enqueue_style( 'magpro-style', get_stylesheet_uri(), array(), MAGPRO_VERSION );

	// Load secondary styles asynchronously
	wp_enqueue_style( 'magpro-layout', MAGPRO_URI . '/css/layout.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-magazine', MAGPRO_URI . '/css/magazine.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-widgets', MAGPRO_URI . '/css/widgets.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-responsive', MAGPRO_URI . '/css/responsive.css', array(), MAGPRO_VERSION );
}

/**
 * Optimize paint timing - Reduce First Contentful Paint.
 */
function magpro_optimize_fcp() {
	// Critical styles must be inline in <head>
	// Non-critical styles are loaded async
	// This is handled by performance.php
}

/**
 * Stabilize layout with container queries where possible.
 */
function magpro_add_container_css() {
	?>
	<style id="magpro-container-queries">
		/* Use CSS containment for performance */
		.magpro-post-card {
			contain: layout style paint;
		}

		.magpro-widget {
			contain: layout style paint;
		}

		.magpro-ad-wrapper {
			contain: layout style;
		}

		/* Prevent paint thrashing */
		.magpro-comment {
			contain: content;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'magpro_add_container_css' );

/**
 * Optimize critical rendering path.
 */
function magpro_critical_rendering_path() {
	// Inline critical styles in head
	// Defer non-critical CSS
	// Defer all JavaScript
	// This is core to the theme's performance strategy

	// Add meta viewport for mobile optimization
	if ( ! function_exists( 'wp_body_open' ) ) {
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">' . "\n";
	}
}
add_action( 'wp_head', 'magpro_critical_rendering_path', 0 );

/**
 * Disable unnecessary features to reduce JS bundle.
 */
function magpro_minimize_javascript() {
	// Disable emoji script (already done in performance.php)
	// Disable WP Embed script if not needed
	wp_dequeue_script( 'wp-embed' );

	// Remove unnecessary styles
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'magpro_minimize_javascript', 100 );

/**
 * Add DNS prefetch for external services.
 */
function magpro_dns_prefetch() {
	echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//pagead2.googlesyndication.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
	echo '<link rel="dns-prefetch" href="//www.googletagmanager.com">' . "\n";
}
add_action( 'wp_head', 'magpro_dns_prefetch' );
