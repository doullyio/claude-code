<?php
/**
 * MagPro Theme Customizer
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function magpro_customize_register( $wp_customize ) {

	// ─── General Settings ───

	$wp_customize->add_panel( 'magpro_panel', array(
		'title'    => __( 'MagPro Settings', 'magpro' ),
		'priority' => 30,
	) );

	// ─── Header Section ───

	$wp_customize->add_section( 'magpro_header', array(
		'title' => __( 'Header', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_sticky_header', array(
		'default'           => true,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( 'magpro_sticky_header', array(
		'label'   => __( 'Sticky Header', 'magpro' ),
		'section' => 'magpro_header',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'magpro_breaking_news', array(
		'default'           => true,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'magpro_breaking_news', array(
		'label'   => __( 'Show Breaking News Ticker', 'magpro' ),
		'section' => 'magpro_header',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'magpro_breaking_news_tag', array(
		'default'           => 'breaking',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_breaking_news_tag', array(
		'label'       => __( 'Breaking News Tag Slug', 'magpro' ),
		'description' => __( 'Posts with this tag will appear in the breaking news ticker.', 'magpro' ),
		'section'     => 'magpro_header',
		'type'        => 'text',
	) );

	// ─── Colors Section ───

	$wp_customize->add_section( 'magpro_colors', array(
		'title' => __( 'Theme Colors', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_accent_color', array(
		'default'           => '#e63946',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'magpro_accent_color', array(
		'label'   => __( 'Accent Color', 'magpro' ),
		'section' => 'magpro_colors',
	) ) );

	$wp_customize->add_setting( 'magpro_secondary_color', array(
		'default'           => '#1d3557',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'magpro_secondary_color', array(
		'label'   => __( 'Secondary Color', 'magpro' ),
		'section' => 'magpro_colors',
	) ) );

	$wp_customize->add_setting( 'magpro_dark_mode', array(
		'default'           => false,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'magpro_dark_mode', array(
		'label'   => __( 'Enable Dark Mode Toggle', 'magpro' ),
		'section' => 'magpro_colors',
		'type'    => 'checkbox',
	) );

	// ─── Layout Section ───

	$wp_customize->add_section( 'magpro_layout', array(
		'title' => __( 'Layout', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_homepage_layout', array(
		'default'           => 'magazine',
		'sanitize_callback' => 'magpro_sanitize_select',
	) );
	$wp_customize->add_control( 'magpro_homepage_layout', array(
		'label'   => __( 'Homepage Layout', 'magpro' ),
		'section' => 'magpro_layout',
		'type'    => 'select',
		'choices' => array(
			'magazine' => __( 'Magazine Grid', 'magpro' ),
			'blog'     => __( 'Classic Blog', 'magpro' ),
			'news'     => __( 'News Portal', 'magpro' ),
		),
	) );

	$wp_customize->add_setting( 'magpro_sidebar_position', array(
		'default'           => 'right',
		'sanitize_callback' => 'magpro_sanitize_select',
	) );
	$wp_customize->add_control( 'magpro_sidebar_position', array(
		'label'   => __( 'Sidebar Position', 'magpro' ),
		'section' => 'magpro_layout',
		'type'    => 'select',
		'choices' => array(
			'right' => __( 'Right', 'magpro' ),
			'left'  => __( 'Left', 'magpro' ),
			'none'  => __( 'No Sidebar', 'magpro' ),
		),
	) );

	$wp_customize->add_setting( 'magpro_posts_per_row', array(
		'default'           => '3',
		'sanitize_callback' => 'magpro_sanitize_select',
	) );
	$wp_customize->add_control( 'magpro_posts_per_row', array(
		'label'   => __( 'Posts Per Row (Grid)', 'magpro' ),
		'section' => 'magpro_layout',
		'type'    => 'select',
		'choices' => array(
			'2' => __( '2 Columns', 'magpro' ),
			'3' => __( '3 Columns', 'magpro' ),
			'4' => __( '4 Columns', 'magpro' ),
		),
	) );

	// ─── SEO Section ───

	$wp_customize->add_section( 'magpro_seo', array(
		'title' => __( 'SEO Settings', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_title_separator', array(
		'default'           => '|',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_title_separator', array(
		'label'   => __( 'Title Separator', 'magpro' ),
		'section' => 'magpro_seo',
		'type'    => 'select',
		'choices' => array(
			'|' => '|',
			'-' => '-',
			'>' => '>',
			'/' => '/',
		),
	) );

	$wp_customize->add_setting( 'magpro_default_og_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'magpro_default_og_image', array(
		'label'       => __( 'Default Open Graph Image', 'magpro' ),
		'description' => __( 'Used when a post has no featured image (1200x630 recommended).', 'magpro' ),
		'section'     => 'magpro_seo',
	) ) );

	$wp_customize->add_setting( 'magpro_twitter_handle', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_twitter_handle', array(
		'label'   => __( 'Twitter/X Handle (without @)', 'magpro' ),
		'section' => 'magpro_seo',
		'type'    => 'text',
	) );

	// ─── AdSense Section ───

	$wp_customize->add_section( 'magpro_adsense', array(
		'title' => __( 'AdSense / Ads', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_adsense_publisher_id', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_adsense_publisher_id', array(
		'label'       => __( 'AdSense Publisher ID', 'magpro' ),
		'description' => __( 'e.g. ca-pub-1234567890123456', 'magpro' ),
		'section'     => 'magpro_adsense',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'magpro_adsense_auto_ads', array(
		'default'           => false,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'magpro_adsense_auto_ads', array(
		'label'   => __( 'Enable Auto Ads', 'magpro' ),
		'section' => 'magpro_adsense',
		'type'    => 'checkbox',
	) );

	// Ad placement slots.
	$ad_locations = magpro_ad_locations();
	foreach ( $ad_locations as $key => $label ) {
		$wp_customize->add_setting( 'magpro_ad_' . $key . '_enabled', array(
			'default'           => false,
			'sanitize_callback' => 'magpro_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'magpro_ad_' . $key . '_enabled', array(
			'label'   => sprintf(
				/* translators: %s: ad location name */
				__( 'Enable %s Ad', 'magpro' ),
				$label
			),
			'section' => 'magpro_adsense',
			'type'    => 'checkbox',
		) );

		$wp_customize->add_setting( 'magpro_ad_' . $key . '_slot', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( 'magpro_ad_' . $key . '_slot', array(
			'label'   => sprintf(
				/* translators: %s: ad location name */
				__( '%s Ad Slot ID', 'magpro' ),
				$label
			),
			'section' => 'magpro_adsense',
			'type'    => 'text',
		) );
	}

	$wp_customize->add_setting( 'magpro_ad_mid_article_paragraph', array(
		'default'           => 3,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'magpro_ad_mid_article_paragraph', array(
		'label'       => __( 'Insert Mid-Article Ad After Paragraph #', 'magpro' ),
		'section'     => 'magpro_adsense',
		'type'        => 'number',
		'input_attrs' => array(
			'min' => 1,
			'max' => 20,
		),
	) );

	// ─── Contact Section ───

	$wp_customize->add_section( 'magpro_contact', array(
		'title' => __( 'Contact Information', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_contact_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'magpro_contact_email', array(
		'label'   => __( 'Contact Email', 'magpro' ),
		'section' => 'magpro_contact',
		'type'    => 'email',
	) );

	$wp_customize->add_setting( 'magpro_contact_phone', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_contact_phone', array(
		'label'   => __( 'Contact Phone', 'magpro' ),
		'section' => 'magpro_contact',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'magpro_contact_address', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'magpro_contact_address', array(
		'label'       => __( 'Contact Address', 'magpro' ),
		'description' => __( 'You can use line breaks.', 'magpro' ),
		'section'     => 'magpro_contact',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'magpro_contact_form_shortcode', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'magpro_contact_form_shortcode', array(
		'label'       => __( 'Contact Form 7 Shortcode', 'magpro' ),
		'description' => __( 'e.g. [contact-form-7 id="abc123"]', 'magpro' ),
		'section'     => 'magpro_contact',
		'type'        => 'textarea',
	) );

	// ─── Social Media Section ───

	$wp_customize->add_section( 'magpro_social', array(
		'title' => __( 'Social Media', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$social_networks = array(
		'facebook_url'  => __( 'Facebook URL', 'magpro' ),
		'twitter_url'   => __( 'Twitter/X URL', 'magpro' ),
		'instagram_url' => __( 'Instagram URL', 'magpro' ),
		'youtube_url'   => __( 'YouTube URL', 'magpro' ),
		'linkedin_url'  => __( 'LinkedIn URL', 'magpro' ),
		'tiktok_url'    => __( 'TikTok URL', 'magpro' ),
		'telegram_url'  => __( 'Telegram URL', 'magpro' ),
	);

	foreach ( $social_networks as $key => $label ) {
		$wp_customize->add_setting( 'magpro_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( 'magpro_' . $key, array(
			'label'   => $label,
			'section' => 'magpro_social',
			'type'    => 'url',
		) );
	}

	// ─── Footer Section ───

	$wp_customize->add_section( 'magpro_footer', array(
		'title' => __( 'Footer', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_footer_copyright', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'magpro_footer_copyright', array(
		'label'       => __( 'Copyright Text', 'magpro' ),
		'description' => __( 'Use {year} for dynamic year and {site} for site name.', 'magpro' ),
		'section'     => 'magpro_footer',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'magpro_back_to_top', array(
		'default'           => true,
		'sanitize_callback' => 'magpro_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'magpro_back_to_top', array(
		'label'   => __( 'Show Back to Top Button', 'magpro' ),
		'section' => 'magpro_footer',
		'type'    => 'checkbox',
	) );

	// ─── Typography Section ───

	$wp_customize->add_section( 'magpro_typography', array(
		'title' => __( 'Typography', 'magpro' ),
		'panel' => 'magpro_panel',
	) );

	$wp_customize->add_setting( 'magpro_body_font', array(
		'default'           => 'system',
		'sanitize_callback' => 'magpro_sanitize_select',
	) );
	$wp_customize->add_control( 'magpro_body_font', array(
		'label'   => __( 'Body Font', 'magpro' ),
		'section' => 'magpro_typography',
		'type'    => 'select',
		'choices' => array(
			'system'     => __( 'System Font Stack', 'magpro' ),
			'inter'      => 'Inter',
			'roboto'     => 'Roboto',
			'open-sans'  => 'Open Sans',
			'lato'       => 'Lato',
			'merriweather' => 'Merriweather',
		),
	) );

	$wp_customize->add_setting( 'magpro_heading_font', array(
		'default'           => 'system',
		'sanitize_callback' => 'magpro_sanitize_select',
	) );
	$wp_customize->add_control( 'magpro_heading_font', array(
		'label'   => __( 'Heading Font', 'magpro' ),
		'section' => 'magpro_typography',
		'type'    => 'select',
		'choices' => array(
			'system'     => __( 'System Font Stack', 'magpro' ),
			'inter'      => 'Inter',
			'roboto'     => 'Roboto',
			'playfair'   => 'Playfair Display',
			'montserrat' => 'Montserrat',
			'oswald'     => 'Oswald',
		),
	) );
}
add_action( 'customize_register', 'magpro_customize_register' );

/**
 * Output custom CSS from customizer settings.
 */
function magpro_customizer_css() {
	$accent    = get_theme_mod( 'magpro_accent_color', '#e63946' );
	$secondary = get_theme_mod( 'magpro_secondary_color', '#1d3557' );

	$css = ':root{';
	$css .= '--magpro-accent:' . esc_attr( $accent ) . ';';
	$css .= '--magpro-secondary:' . esc_attr( $secondary ) . ';';
	$css .= '}';

	echo '<style id="magpro-customizer-css">' . $css . '</style>';
}
add_action( 'wp_head', 'magpro_customizer_css', 2 );

/**
 * Enqueue Google Fonts based on customizer selections.
 */
function magpro_google_fonts() {
	$body_font    = get_theme_mod( 'magpro_body_font', 'system' );
	$heading_font = get_theme_mod( 'magpro_heading_font', 'system' );

	$font_map = array(
		'inter'        => 'Inter:wght@400;500;600;700',
		'roboto'       => 'Roboto:wght@400;500;700',
		'open-sans'    => 'Open+Sans:wght@400;600;700',
		'lato'         => 'Lato:wght@400;700;900',
		'merriweather' => 'Merriweather:wght@400;700',
		'playfair'     => 'Playfair+Display:wght@400;700;900',
		'montserrat'   => 'Montserrat:wght@400;600;700;800',
		'oswald'       => 'Oswald:wght@400;500;600;700',
	);

	$families = array();

	if ( isset( $font_map[ $body_font ] ) ) {
		$families[] = $font_map[ $body_font ];
	}
	if ( isset( $font_map[ $heading_font ] ) && $heading_font !== $body_font ) {
		$families[] = $font_map[ $heading_font ];
	}

	if ( ! empty( $families ) ) {
		$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';
		wp_enqueue_style( 'magpro-google-fonts', $url, array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'magpro_google_fonts' );

/**
 * Sanitize checkbox value.
 *
 * @param bool $checked Value.
 * @return bool
 */
function magpro_sanitize_checkbox( $checked ) {
	return (bool) $checked;
}

/**
 * Sanitize select/radio value.
 *
 * @param string               $input   Selected value.
 * @param WP_Customize_Setting $setting Setting object.
 * @return string
 */
function magpro_sanitize_select( $input, $setting ) {
	$choices = $setting->manager->get_control( $setting->id )->choices;
	return array_key_exists( $input, $choices ) ? $input : $setting->default;
}
