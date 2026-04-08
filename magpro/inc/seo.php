<?php
/**
 * MagPro SEO Module
 *
 * Dynamic meta tags, Open Graph, Twitter Cards, canonical URLs.
 * Designed to work standalone or alongside Yoast/RankMath.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if an SEO plugin is active.
 *
 * @return bool
 */
function magpro_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Output SEO meta tags in the head.
 */
function magpro_seo_meta_tags() {
	// Skip if an SEO plugin handles this.
	if ( magpro_seo_plugin_active() ) {
		return;
	}

	$meta = magpro_get_seo_meta();

	// Meta description.
	if ( ! empty( $meta['description'] ) ) {
		echo '<meta name="description" content="' . esc_attr( $meta['description'] ) . '">' . "\n";
	}

	// Robots.
	if ( ! empty( $meta['robots'] ) ) {
		echo '<meta name="robots" content="' . esc_attr( $meta['robots'] ) . '">' . "\n";
	}

	// Canonical URL.
	if ( ! empty( $meta['canonical'] ) ) {
		echo '<link rel="canonical" href="' . esc_url( $meta['canonical'] ) . '">' . "\n";
	}

	// Open Graph.
	magpro_output_og_tags( $meta );

	// Twitter Card.
	magpro_output_twitter_tags( $meta );
}
add_action( 'wp_head', 'magpro_seo_meta_tags', 1 );

/**
 * Gather SEO meta data for the current page.
 *
 * @return array
 */
function magpro_get_seo_meta() {
	$meta = array(
		'title'       => '',
		'description' => '',
		'canonical'   => '',
		'robots'      => 'index, follow',
		'og_type'     => 'website',
		'og_image'    => '',
		'og_url'      => '',
	);

	if ( is_singular() ) {
		$post = get_queried_object();

		$meta['title']       = get_the_title( $post );
		$meta['description'] = magpro_generate_meta_description( $post );
		$meta['canonical']   = get_permalink( $post );
		$meta['og_type']     = 'article';
		$meta['og_url']      = get_permalink( $post );

		if ( has_post_thumbnail( $post ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'magpro-og' );
			if ( $image ) {
				$meta['og_image'] = $image[0];
			}
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();

		$meta['title']       = $term->name;
		$meta['description'] = ! empty( $term->description ) ? wp_trim_words( $term->description, 25 ) : '';
		$meta['canonical']   = get_term_link( $term );
		$meta['og_url']      = get_term_link( $term );
	} elseif ( is_author() ) {
		$author = get_queried_object();

		$meta['title']       = $author->display_name;
		$meta['description'] = get_the_author_meta( 'description', $author->ID );
		$meta['canonical']   = get_author_posts_url( $author->ID );
		$meta['og_url']      = get_author_posts_url( $author->ID );
		$meta['og_image']    = get_avatar_url( $author->ID, array( 'size' => 600 ) );
	} elseif ( is_home() || is_front_page() ) {
		$meta['title']       = get_bloginfo( 'name' );
		$meta['description'] = get_bloginfo( 'description' );
		$meta['canonical']   = home_url( '/' );
		$meta['og_url']      = home_url( '/' );
	} elseif ( is_search() ) {
		$meta['title']       = sprintf(
			/* translators: %s: search query */
			__( 'Search Results for: %s', 'magpro' ),
			get_search_query()
		);
		$meta['robots'] = 'noindex, follow';
	} elseif ( is_404() ) {
		$meta['robots'] = 'noindex, nofollow';
	}

	// Fallback for OG image - use site logo or customizer setting.
	if ( empty( $meta['og_image'] ) ) {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( $image ) {
				$meta['og_image'] = $image[0];
			}
		}
	}

	// Default OG image from customizer.
	if ( empty( $meta['og_image'] ) ) {
		$default_og = magpro_get_option( 'default_og_image' );
		if ( $default_og ) {
			$meta['og_image'] = $default_og;
		}
	}

	// Pagination robots.
	if ( is_paged() ) {
		$meta['robots'] = 'noindex, follow';
	}

	return $meta;
}

/**
 * Generate meta description from post content.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function magpro_generate_meta_description( $post ) {
	// Use excerpt if available.
	if ( has_excerpt( $post ) ) {
		return wp_trim_words( get_the_excerpt( $post ), 25 );
	}

	// Generate from content.
	$content = get_post_field( 'post_content', $post );
	$content = wp_strip_all_tags( strip_shortcodes( $content ) );
	return wp_trim_words( $content, 25 );
}

/**
 * Output Open Graph meta tags.
 *
 * @param array $meta SEO meta data.
 */
function magpro_output_og_tags( $meta ) {
	$site_name = get_bloginfo( 'name' );
	$locale    = get_locale();

	echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $meta['og_type'] ) . '">' . "\n";

	if ( ! empty( $meta['title'] ) ) {
		echo '<meta property="og:title" content="' . esc_attr( $meta['title'] ) . '">' . "\n";
	}

	if ( ! empty( $meta['description'] ) ) {
		echo '<meta property="og:description" content="' . esc_attr( $meta['description'] ) . '">' . "\n";
	}

	if ( ! empty( $meta['og_url'] ) ) {
		echo '<meta property="og:url" content="' . esc_url( $meta['og_url'] ) . '">' . "\n";
	}

	if ( ! empty( $meta['og_image'] ) ) {
		echo '<meta property="og:image" content="' . esc_url( $meta['og_image'] ) . '">' . "\n";
		echo '<meta property="og:image:width" content="1200">' . "\n";
		echo '<meta property="og:image:height" content="630">' . "\n";
	}

	// Article-specific OG tags.
	if ( 'article' === $meta['og_type'] && is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:author" content="' . esc_attr( get_the_author() ) . '">' . "\n";

		$categories = get_the_category();
		if ( ! empty( $categories ) ) {
			echo '<meta property="article:section" content="' . esc_attr( $categories[0]->name ) . '">' . "\n";
		}

		$tags = get_the_tags();
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '">' . "\n";
			}
		}
	}
}

/**
 * Output Twitter Card meta tags.
 *
 * @param array $meta SEO meta data.
 */
function magpro_output_twitter_tags( $meta ) {
	$card_type = ! empty( $meta['og_image'] ) ? 'summary_large_image' : 'summary';

	echo '<meta name="twitter:card" content="' . esc_attr( $card_type ) . '">' . "\n";

	$twitter_handle = magpro_get_option( 'twitter_handle' );
	if ( $twitter_handle ) {
		echo '<meta name="twitter:site" content="@' . esc_attr( $twitter_handle ) . '">' . "\n";
	}

	if ( ! empty( $meta['title'] ) ) {
		echo '<meta name="twitter:title" content="' . esc_attr( $meta['title'] ) . '">' . "\n";
	}

	if ( ! empty( $meta['description'] ) ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $meta['description'] ) . '">' . "\n";
	}

	if ( ! empty( $meta['og_image'] ) ) {
		echo '<meta name="twitter:image" content="' . esc_url( $meta['og_image'] ) . '">' . "\n";
	}
}

/**
 * Dynamic document title with separator.
 *
 * @param array $title Title parts.
 * @return array
 */
function magpro_document_title_parts( $title ) {
	$sep = magpro_get_option( 'title_separator', '|' );

	if ( isset( $title['tagline'] ) ) {
		$title['tagline'] = get_bloginfo( 'description' );
	}

	return $title;
}
add_filter( 'document_title_parts', 'magpro_document_title_parts' );

/**
 * Set document title separator.
 *
 * @return string
 */
function magpro_document_title_separator() {
	return magpro_get_option( 'title_separator', '|' );
}
add_filter( 'document_title_separator', 'magpro_document_title_separator' );

/**
 * Add next/prev rel links for paginated archives.
 */
function magpro_adjacent_rel_links() {
	if ( magpro_seo_plugin_active() ) {
		return;
	}

	global $wp_query;

	if ( ! is_singular() && $wp_query->max_num_pages > 1 ) {
		$paged = max( 1, get_query_var( 'paged' ) );

		if ( $paged > 1 ) {
			echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '">' . "\n";
		}

		if ( $paged < $wp_query->max_num_pages ) {
			echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'magpro_adjacent_rel_links', 2 );
