<?php
/**
 * MagPro AdSense Integration
 *
 * Google AdSense ad unit management with placement controls.
 * Supports auto ads, manual placement, and in-article injection.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output AdSense auto ads script in head.
 */
function magpro_adsense_auto_ads() {
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
add_action( 'wp_head', 'magpro_adsense_auto_ads', 3 );

/**
 * Display an ad unit.
 *
 * @param string $location Ad location identifier.
 * @param array  $args     Additional arguments.
 * @return void
 */
function magpro_display_ad( $location = 'default', $args = array() ) {
	$publisher_id = magpro_get_option( 'adsense_publisher_id' );

	if ( empty( $publisher_id ) ) {
		return;
	}

	// Check if ads are enabled for this location.
	$enabled = magpro_get_option( 'ad_' . $location . '_enabled', false );
	if ( ! $enabled ) {
		return;
	}

	// Don't show ads on 404 pages.
	if ( is_404() ) {
		return;
	}

	$slot_id   = magpro_get_option( 'ad_' . $location . '_slot' );
	$ad_format = magpro_get_option( 'ad_' . $location . '_format', 'auto' );
	$custom_code = magpro_get_option( 'ad_' . $location . '_custom' );

	$defaults = array(
		'class'  => 'magpro-ad-unit',
		'style'  => 'display:block',
		'format' => $ad_format,
		'responsive' => 'true',
	);
	$args = wp_parse_args( $args, $defaults );

	echo '<div class="magpro-ad-wrapper magpro-ad-' . esc_attr( $location ) . '" aria-label="' . esc_attr__( 'Advertisement', 'magpro' ) . '">';
	echo '<span class="magpro-ad-label">' . esc_html__( 'Advertisement', 'magpro' ) . '</span>';

	if ( ! empty( $custom_code ) ) {
		// Custom ad code.
		echo wp_kses(
			$custom_code,
			array(
				'ins'    => array(
					'class'            => true,
					'style'            => true,
					'data-ad-client'   => true,
					'data-ad-slot'     => true,
					'data-ad-format'   => true,
					'data-full-width-responsive' => true,
				),
				'script' => array(
					'async' => true,
					'src'   => true,
					'crossorigin' => true,
				),
			)
		);
	} elseif ( $slot_id ) {
		?>
		<ins class="adsbygoogle <?php echo esc_attr( $args['class'] ); ?>"
			 style="<?php echo esc_attr( $args['style'] ); ?>"
			 data-ad-client="<?php echo esc_attr( $publisher_id ); ?>"
			 data-ad-slot="<?php echo esc_attr( $slot_id ); ?>"
			 data-ad-format="<?php echo esc_attr( $args['format'] ); ?>"
			 data-full-width-responsive="<?php echo esc_attr( $args['responsive'] ); ?>"></ins>
		<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		<?php
	}

	echo '</div>';
}

/**
 * Inject ad after a specified paragraph in post content.
 *
 * @param string $content Post content.
 * @return string Modified content.
 */
function magpro_inject_in_article_ad( $content ) {
	if ( ! is_singular( 'post' ) || is_admin() || is_feed() ) {
		return $content;
	}

	$publisher_id = magpro_get_option( 'adsense_publisher_id' );
	if ( empty( $publisher_id ) ) {
		return $content;
	}

	$mid_enabled = magpro_get_option( 'ad_mid_article_enabled', false );
	if ( ! $mid_enabled ) {
		return $content;
	}

	$after_paragraph = (int) magpro_get_option( 'ad_mid_article_paragraph', 3 );
	$slot_id         = magpro_get_option( 'ad_mid_article_slot' );

	if ( empty( $slot_id ) ) {
		return $content;
	}

	$ad_code = '<div class="magpro-ad-wrapper magpro-ad-in-article" aria-label="' . esc_attr__( 'Advertisement', 'magpro' ) . '">';
	$ad_code .= '<span class="magpro-ad-label">' . esc_html__( 'Advertisement', 'magpro' ) . '</span>';
	$ad_code .= '<ins class="adsbygoogle" style="display:block; text-align:center;" data-ad-layout="in-article" data-ad-format="fluid" data-ad-client="' . esc_attr( $publisher_id ) . '" data-ad-slot="' . esc_attr( $slot_id ) . '"></ins>';
	$ad_code .= '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
	$ad_code .= '</div>';

	return magpro_insert_after_paragraph( $ad_code, $after_paragraph, $content );
}
add_filter( 'the_content', 'magpro_inject_in_article_ad', 50 );

/**
 * Insert content after a specific paragraph.
 *
 * @param string $insertion Content to insert.
 * @param int    $paragraph Paragraph number.
 * @param string $content   Original content.
 * @return string
 */
function magpro_insert_after_paragraph( $insertion, $paragraph, $content ) {
	$closing_p  = '</p>';
	$paragraphs = explode( $closing_p, $content );

	if ( count( $paragraphs ) <= $paragraph ) {
		return $content;
	}

	foreach ( $paragraphs as $index => $p ) {
		if ( trim( $p ) ) {
			$paragraphs[ $index ] .= $closing_p;
		}
		if ( $index + 1 === $paragraph ) {
			$paragraphs[ $index ] .= $insertion;
		}
	}

	return implode( '', $paragraphs );
}

/**
 * Ad locations configuration.
 *
 * @return array
 */
function magpro_ad_locations() {
	return array(
		'header'          => __( 'Header Banner (728x90)', 'magpro' ),
		'below_header'    => __( 'Below Header', 'magpro' ),
		'sidebar_top'     => __( 'Sidebar Top', 'magpro' ),
		'sidebar_sticky'  => __( 'Sidebar Sticky', 'magpro' ),
		'before_content'  => __( 'Before Article Content', 'magpro' ),
		'mid_article'     => __( 'Mid Article (In-Content)', 'magpro' ),
		'after_content'   => __( 'After Article Content', 'magpro' ),
		'before_comments' => __( 'Before Comments', 'magpro' ),
		'footer'          => __( 'Footer Banner', 'magpro' ),
	);
}
