<?php
/**
 * MagPro Theme Functions
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAGPRO_VERSION', '1.0.0' );
define( 'MAGPRO_DIR', get_template_directory() );
define( 'MAGPRO_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function magpro_setup() {
	// Load text domain.
	load_theme_textdomain( 'magpro', MAGPRO_DIR . '/languages' );

	// Add theme support.
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 300,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff',
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );

	// Custom image sizes for magazine layouts.
	add_image_size( 'magpro-hero', 1200, 630, true );
	add_image_size( 'magpro-featured', 800, 450, true );
	add_image_size( 'magpro-card', 400, 250, true );
	add_image_size( 'magpro-thumbnail-small', 150, 150, true );
	add_image_size( 'magpro-og', 1200, 630, true );

	// Register navigation menus.
	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'magpro' ),
		'secondary' => __( 'Secondary Menu (Top Bar)', 'magpro' ),
		'footer'    => __( 'Footer Menu', 'magpro' ),
		'mobile'    => __( 'Mobile Menu', 'magpro' ),
	) );

	// Set content width.
	if ( ! isset( $content_width ) ) {
		$content_width = 800;
	}
}
add_action( 'after_setup_theme', 'magpro_setup' );

/**
 * Enqueue scripts and styles.
 */
function magpro_scripts() {
	// Main stylesheet (just the theme header).
	wp_enqueue_style( 'magpro-style', get_stylesheet_uri(), array(), MAGPRO_VERSION );

	// Layout CSS (loaded async via performance.php filter).
	wp_enqueue_style( 'magpro-layout', MAGPRO_URI . '/css/layout.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-magazine', MAGPRO_URI . '/css/magazine.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-widgets', MAGPRO_URI . '/css/widgets.css', array(), MAGPRO_VERSION );
	wp_enqueue_style( 'magpro-responsive', MAGPRO_URI . '/css/responsive.css', array(), MAGPRO_VERSION );

	// JavaScript.
	wp_enqueue_script( 'magpro-navigation', MAGPRO_URI . '/js/navigation.js', array(), MAGPRO_VERSION, true );
	wp_enqueue_script( 'magpro-main', MAGPRO_URI . '/js/main.js', array(), MAGPRO_VERSION, true );

	// Pass data to JS.
	wp_localize_script( 'magpro-main', 'magproData', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'magpro_nonce' ),
		'darkMode' => (bool) get_theme_mod( 'magpro_dark_mode', false ),
	) );

	// Comment reply script.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'magpro_scripts' );

/**
 * Include theme modules.
 */
require MAGPRO_DIR . '/inc/helpers.php';
require MAGPRO_DIR . '/inc/performance.php';
require MAGPRO_DIR . '/inc/seo.php';
require MAGPRO_DIR . '/inc/schema.php';
require MAGPRO_DIR . '/inc/adsense.php';
require MAGPRO_DIR . '/inc/amp.php';
require MAGPRO_DIR . '/inc/customizer.php';
require MAGPRO_DIR . '/inc/widgets.php';

/**
 * Add excerpt support for pages.
 */
function magpro_add_excerpts_to_pages() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'magpro_add_excerpts_to_pages' );

/**
 * Modify excerpt length.
 *
 * @param int $length Default excerpt length.
 * @return int
 */
function magpro_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}
	return 25;
}
add_filter( 'excerpt_length', 'magpro_excerpt_length' );

/**
 * Custom excerpt more text.
 *
 * @return string
 */
function magpro_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'magpro_excerpt_more' );

/**
 * Add custom body classes.
 *
 * @param array $classes Body classes.
 * @return array
 */
function magpro_body_classes( $classes ) {
	$sidebar = get_theme_mod( 'magpro_sidebar_position', 'right' );
	$classes[] = 'magpro-sidebar-' . $sidebar;

	if ( get_theme_mod( 'magpro_sticky_header', true ) ) {
		$classes[] = 'has-sticky-header';
	}

	if ( get_theme_mod( 'magpro_dark_mode', false ) ) {
		$classes[] = 'has-dark-mode-toggle';
	}

	if ( is_singular() ) {
		$classes[] = 'magpro-singular';
	}

	return $classes;
}
add_filter( 'body_class', 'magpro_body_classes' );

/**
 * Add pingback URL to head for singular content.
 */
function magpro_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="' . esc_url( get_bloginfo( 'pingback_url' ) ) . '">';
	}
}
add_action( 'wp_head', 'magpro_pingback_header' );

/**
 * Custom image sizes in media library dropdown.
 *
 * @param array $sizes Available sizes.
 * @return array
 */
function magpro_image_size_names( $sizes ) {
	return array_merge( $sizes, array(
		'magpro-hero'     => __( 'MagPro Hero (1200x630)', 'magpro' ),
		'magpro-featured' => __( 'MagPro Featured (800x450)', 'magpro' ),
		'magpro-card'     => __( 'MagPro Card (400x250)', 'magpro' ),
	) );
}
add_filter( 'image_size_names_choose', 'magpro_image_size_names' );
