<?php
/**
 * MagPro Advanced Ad Management
 *
 * Complete AdSense integration with multiple placement options.
 * Admins configure ads from Customizer, admins manage placements.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ad placement locations with descriptions.
 */
function magpro_ad_placements() {
	return array(
		// Above the fold
		'header_banner' => array(
			'label'       => __( '📌 Header Banner (728x90 or 970x90)', 'magpro' ),
			'description' => __( 'Displays below the main navigation bar', 'magpro' ),
			'location'    => 'header',
		),

		// Magazine homepage
		'homepage_featured' => array(
			'label'       => __( '📌 Homepage Featured Ad (336x280)', 'magpro' ),
			'description' => __( 'Below the featured article', 'magpro' ),
			'location'    => 'homepage',
		),

		'homepage_grid' => array(
			'label'       => __( '📌 Homepage Grid Ad (300x250)', 'magpro' ),
			'description' => __( 'In the middle of posts grid', 'magpro' ),
			'location'    => 'homepage_grid',
		),

		// Sidebar
		'sidebar_top' => array(
			'label'       => __( '📌 Sidebar Top (300x250)', 'magpro' ),
			'description' => __( 'At the top of the sidebar', 'magpro' ),
			'location'    => 'sidebar',
		),

		'sidebar_middle' => array(
			'label'       => __( '📌 Sidebar Middle (300x250)', 'magpro' ),
			'description' => __( 'In the middle of the sidebar', 'magpro' ),
			'location'    => 'sidebar',
		),

		'sidebar_sticky' => array(
			'label'       => __( '📌 Sidebar Sticky (300x250)', 'magpro' ),
			'description' => __( 'Sticky ad that follows scroll', 'magpro' ),
			'location'    => 'sidebar',
		),

		// Article content
		'before_content' => array(
			'label'       => __( '📌 Before Article Content (728x90)', 'magpro' ),
			'description' => __( 'Displays above the article title', 'magpro' ),
			'location'    => 'content',
		),

		'mid_article' => array(
			'label'       => __( '📌 Mid Article In-Content (Auto)', 'magpro' ),
			'description' => __( 'Injected after paragraph', 'magpro' ),
			'location'    => 'content',
		),

		'after_content' => array(
			'label'       => __( '📌 After Article Content (336x280)', 'magpro' ),
			'description' => __( 'Below the article, before related posts', 'magpro' ),
			'location'    => 'content',
		),

		// Footer
		'footer_banner' => array(
			'label'       => __( '📌 Footer Banner (728x90)', 'magpro' ),
			'description' => __( 'Above the footer section', 'magpro' ),
			'location'    => 'footer',
		),

		'footer_sticky' => array(
			'label'       => __( '📌 Footer Sticky Ad (320x50)', 'magpro' ),
			'description' => __( 'Mobile sticky footer bar', 'magpro' ),
			'location'    => 'footer',
		),
	);
}

/**
 * Register AdSense settings in customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function magpro_register_adsense_customizer( $wp_customize ) {
	$placements = magpro_ad_placements();

	// Publisher ID
	$wp_customize->add_setting( 'magpro_adsense_publisher_id', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_adsense_publisher_id', array(
		'label'       => __( 'Google AdSense Publisher ID', 'magpro' ),
		'description' => __( 'Example: ca-pub-1234567890123456', 'magpro' ),
		'section'     => 'magpro_adsense',
		'type'        => 'text',
		'priority'    => 1,
	) );

	// Auto Ads
	$wp_customize->add_setting( 'magpro_adsense_auto_ads', array(
		'default'           => false,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'magpro_adsense_auto_ads', array(
		'label'       => __( '🤖 Enable AdSense Auto Ads', 'magpro' ),
		'description' => __( 'Let Google automatically place ads (recommended)', 'magpro' ),
		'section'     => 'magpro_adsense',
		'type'        => 'checkbox',
		'priority'    => 2,
	) );

	// Ad placements
	$priority = 10;
	foreach ( $placements as $key => $placement ) {
		// Enable toggle
		$wp_customize->add_setting( 'magpro_ad_' . $key . '_enabled', array(
			'default'           => false,
			'sanitize_callback' => 'magpro_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'magpro_ad_' . $key . '_enabled', array(
			'label'       => $placement['label'],
			'description' => $placement['description'],
			'section'     => 'magpro_adsense',
			'type'        => 'checkbox',
			'priority'    => $priority++,
		) );

		// Slot ID
		$wp_customize->add_setting( 'magpro_ad_' . $key . '_slot', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'magpro_ad_' . $key . '_slot', array(
			'label'       => sprintf( __( 'Ad Slot ID for %s', 'magpro' ), $placement['label'] ),
			'description' => __( 'The ad slot ID from your AdSense account', 'magpro' ),
			'section'     => 'magpro_adsense',
			'type'        => 'text',
			'priority'    => $priority++,
		) );
	}

	// Mid-article settings
	$wp_customize->add_setting( 'magpro_ad_mid_article_paragraph', array(
		'default'           => '3',
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'magpro_ad_mid_article_paragraph', array(
		'label'       => __( 'Insert Mid-Article Ad After Paragraph #', 'magpro' ),
		'description' => __( 'Example: 3 means after the 3rd paragraph', 'magpro' ),
		'section'     => 'magpro_adsense',
		'type'        => 'number',
		'priority'    => 100,
		'input_attrs' => array(
			'min' => 1,
			'max' => 20,
		),
	) );
}
add_action( 'customize_register', 'magpro_register_adsense_customizer' );

/**
 * Get all ad placements and their settings.
 *
 * @return array Ad placements with settings.
 */
function magpro_get_ad_placements_with_settings() {
	$placements = magpro_ad_placements();
	$publisher_id = magpro_get_option( 'adsense_publisher_id' );

	foreach ( $placements as $key => &$placement ) {
		$placement['enabled'] = magpro_get_option( 'ad_' . $key . '_enabled', false );
		$placement['slot_id'] = magpro_get_option( 'ad_' . $key . '_slot' );
		$placement['can_display'] = $placement['enabled'] && ! empty( $slot_id ) && ! empty( $publisher_id );
	}

	return $placements;
}

/**
 * Display AdSense ad at specified location.
 *
 * @param string $location Ad location key.
 * @param array  $args     Additional arguments.
 * @return void
 */
function magpro_display_ad_unit( $location = '', $args = array() ) {
	$placements = magpro_get_ad_placements_with_settings();

	if ( ! isset( $placements[ $location ] ) ) {
		return;
	}

	$placement = $placements[ $location ];

	// Check if ad is enabled
	if ( ! $placement['can_display'] ) {
		return;
	}

	$publisher_id = magpro_get_option( 'adsense_publisher_id' );

	$defaults = array(
		'class'      => 'magpro-ad-unit',
		'style'      => 'display:block',
		'format'     => 'auto',
		'responsive' => 'true',
	);
	$args = wp_parse_args( $args, $defaults );

	?>
	<div class="magpro-ad-wrapper magpro-ad-<?php echo esc_attr( $location ); ?>" aria-label="<?php esc_attr_e( 'Advertisement', 'magpro' ); ?>">
		<span class="magpro-ad-label"><?php esc_html_e( 'Advertisement', 'magpro' ); ?></span>
		<ins class="adsbygoogle <?php echo esc_attr( $args['class'] ); ?>"
			 style="<?php echo esc_attr( $args['style'] ); ?>"
			 data-ad-client="<?php echo esc_attr( $publisher_id ); ?>"
			 data-ad-slot="<?php echo esc_attr( $placement['slot_id'] ); ?>"
			 data-ad-format="<?php echo esc_attr( $args['format'] ); ?>"
			 data-full-width-responsive="<?php echo esc_attr( $args['responsive'] ); ?>"></ins>
		<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
	</div>
	<?php
}

/**
 * Output AdSense script in head.
 */
function magpro_adsense_script() {
	$publisher_id = magpro_get_option( 'adsense_publisher_id' );

	if ( empty( $publisher_id ) || is_admin() ) {
		return;
	}

	$auto_ads = magpro_get_option( 'adsense_auto_ads', false );
	if ( ! $auto_ads ) {
		return;
	}
	?>
	<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo esc_attr( $publisher_id ); ?>" crossorigin="anonymous"></script>
	<?php
}
add_action( 'wp_head', 'magpro_adsense_script', 3 );
