<?php
/**
 * MagPro Helper Functions
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the estimated reading time for a post.
 *
 * @param int $post_id Post ID.
 * @return int Minutes to read.
 */
function magpro_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$content  = get_post_field( 'post_content', $post_id );
	$words    = str_word_count( wp_strip_all_tags( $content ) );
	$minutes  = max( 1, (int) ceil( $words / 250 ) );
	return $minutes;
}

/**
 * Truncate text to a given number of words.
 *
 * @param string $text  Text to truncate.
 * @param int    $limit Word limit.
 * @param string $more  More indicator.
 * @return string
 */
function magpro_truncate_words( $text, $limit = 25, $more = '&hellip;' ) {
	return wp_trim_words( $text, $limit, $more );
}

/**
 * Get the first category of the current post.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|false
 */
function magpro_get_primary_category( $post_id = null ) {
	$post_id    = $post_id ?: get_the_ID();
	$categories = get_the_category( $post_id );

	if ( empty( $categories ) ) {
		return false;
	}

	// If Yoast is active, use its primary category.
	if ( class_exists( 'WPSEO_Primary_Term' ) ) {
		$primary = new WPSEO_Primary_Term( 'category', $post_id );
		$term_id = $primary->get_primary_term();
		if ( $term_id ) {
			$term = get_term( $term_id, 'category' );
			if ( ! is_wp_error( $term ) ) {
				return $term;
			}
		}
	}

	return $categories[0];
}

/**
 * Get a responsive image with srcset for a post thumbnail.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @param array  $attr    Additional attributes.
 * @return string Image HTML.
 */
function magpro_post_thumbnail( $post_id = null, $size = 'magpro-featured', $attr = array() ) {
	$post_id = $post_id ?: get_the_ID();

	$defaults = array(
		'loading'  => 'lazy',
		'decoding' => 'async',
		'class'    => 'magpro-thumbnail',
	);

	$attr = wp_parse_args( $attr, $defaults );

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	// Return placeholder SVG if no thumbnail.
	return '<img src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'800\' height=\'450\' viewBox=\'0 0 800 450\'%3E%3Crect fill=\'%23f0f0f0\' width=\'800\' height=\'450\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'24\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ENo Image%3C/text%3E%3C/svg%3E" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="magpro-thumbnail magpro-placeholder" width="800" height="450" loading="lazy" decoding="async">';
}

/**
 * Get social share URLs for the current post.
 *
 * @param int $post_id Post ID.
 * @return array Share URLs.
 */
function magpro_get_share_urls( $post_id = null ) {
	$post_id   = $post_id ?: get_the_ID();
	$permalink = urlencode( get_permalink( $post_id ) );
	$title     = urlencode( get_the_title( $post_id ) );

	return array(
		'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $permalink,
		'twitter'   => 'https://twitter.com/intent/tweet?url=' . $permalink . '&text=' . $title,
		'linkedin'  => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $permalink,
		'whatsapp'  => 'https://api.whatsapp.com/send?text=' . $title . '%20' . $permalink,
		'telegram'  => 'https://t.me/share/url?url=' . $permalink . '&text=' . $title,
		'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . $permalink . '&description=' . $title,
		'email'     => 'mailto:?subject=' . $title . '&body=' . $permalink,
	);
}

/**
 * Display breadcrumbs.
 *
 * @return void
 */
function magpro_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$sep = '<span class="magpro-breadcrumb-sep" aria-hidden="true">/</span>';

	echo '<nav class="magpro-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'magpro' ) . '">';
	echo '<ol class="magpro-breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

	// Home.
	echo '<li class="magpro-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '" itemprop="item"><span itemprop="name">' . esc_html__( 'Home', 'magpro' ) . '</span></a>';
	echo '<meta itemprop="position" content="1">';
	echo '</li>' . $sep;

	$position = 2;

	if ( is_category() || is_single() ) {
		$category = magpro_get_primary_category();
		if ( $category ) {
			echo '<li class="magpro-breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
			echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" itemprop="item"><span itemprop="name">' . esc_html( $category->name ) . '</span></a>';
			echo '<meta itemprop="position" content="' . $position . '">';
			echo '</li>' . $sep;
			$position++;
		}
	}

	if ( is_single() || is_page() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html( get_the_title() ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	} elseif ( is_category() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html( single_cat_title( '', false ) ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	} elseif ( is_tag() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html( single_tag_title( '', false ) ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	} elseif ( is_search() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html__( 'Search Results', 'magpro' ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	} elseif ( is_404() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html__( 'Page Not Found', 'magpro' ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	} elseif ( is_archive() ) {
		echo '<li class="magpro-breadcrumb-item magpro-breadcrumb-current" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
		echo '<span itemprop="name">' . esc_html( get_the_archive_title() ) . '</span>';
		echo '<meta itemprop="position" content="' . $position . '">';
		echo '</li>';
	}

	echo '</ol></nav>';
}

/**
 * Get theme option with default fallback.
 *
 * @param string $key     Option key.
 * @param mixed  $default Default value.
 * @return mixed
 */
function magpro_get_option( $key, $default = '' ) {
	return get_theme_mod( 'magpro_' . $key, $default );
}

/**
 * Display post meta (date, author, comments, reading time).
 *
 * @param array $args Display arguments.
 * @return void
 */
function magpro_post_meta( $args = array() ) {
	$defaults = array(
		'show_date'    => true,
		'show_author'  => true,
		'show_comments' => true,
		'show_reading_time' => true,
	);
	$args = wp_parse_args( $args, $defaults );

	echo '<div class="magpro-post-meta">';

	if ( $args['show_author'] ) {
		echo '<span class="magpro-meta-author">';
		echo '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>';
		echo '</span>';
	}

	if ( $args['show_date'] ) {
		echo '<time class="magpro-meta-date" datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	}

	if ( $args['show_comments'] && ! post_password_required() && comments_open() ) {
		echo '<span class="magpro-meta-comments">';
		$count = get_comments_number();
		/* translators: %s: number of comments */
		printf( esc_html( _n( '%s Comment', '%s Comments', $count, 'magpro' ) ), esc_html( number_format_i18n( $count ) ) );
		echo '</span>';
	}

	if ( $args['show_reading_time'] ) {
		$minutes = magpro_reading_time();
		echo '<span class="magpro-meta-reading-time">';
		/* translators: %d: number of minutes */
		printf( esc_html( _n( '%d min read', '%d min read', $minutes, 'magpro' ) ), $minutes );
		echo '</span>';
	}

	echo '</div>';
}

/**
 * Get paginated post query for magazine layouts.
 *
 * @param array $args WP_Query arguments.
 * @return WP_Query
 */
function magpro_get_posts( $args = array() ) {
	$defaults = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	return new WP_Query( wp_parse_args( $args, $defaults ) );
}

/**
 * Get related posts based on categories and tags.
 *
 * @param int $post_id Post ID.
 * @param int $count   Number of related posts.
 * @return WP_Query
 */
function magpro_get_related_posts( $post_id = null, $count = 4 ) {
	$post_id    = $post_id ?: get_the_ID();
	$categories = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );
	$tags       = wp_get_post_tags( $post_id, array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'post__not_in'   => array( $post_id ),
		'tax_query'      => array(
			'relation' => 'OR',
		),
	);

	if ( ! empty( $categories ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $categories,
		);
	}

	if ( ! empty( $tags ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'term_id',
			'terms'    => $tags,
		);
	}

	return new WP_Query( $args );
}
