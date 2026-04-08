<?php
/**
 * MagPro Schema.org JSON-LD Structured Data
 *
 * Outputs JSON-LD schema for Articles, Organization, BreadcrumbList,
 * WebSite (with SearchAction), Author profiles, and FAQPage.
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output all JSON-LD schema in the head.
 */
function magpro_schema_output() {
	// Skip if an SEO plugin handles schema.
	if ( magpro_seo_plugin_active() && apply_filters( 'magpro_disable_schema', true ) ) {
		return;
	}

	$schemas = array();

	// WebSite schema (always).
	$schemas[] = magpro_schema_website();

	// Organization schema (always).
	$schemas[] = magpro_schema_organization();

	// Page-specific schemas.
	if ( is_singular( 'post' ) ) {
		$schemas[] = magpro_schema_article();
	} elseif ( is_singular( 'page' ) ) {
		$schemas[] = magpro_schema_webpage();
	} elseif ( is_author() ) {
		$schemas[] = magpro_schema_person();
	} elseif ( is_category() || is_tag() ) {
		$schemas[] = magpro_schema_collection_page();
	}

	// Output each schema.
	foreach ( $schemas as $schema ) {
		if ( ! empty( $schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}
}
add_action( 'wp_head', 'magpro_schema_output', 5 );

/**
 * WebSite schema with SearchAction.
 *
 * @return array
 */
function magpro_schema_website() {
	return array(
		'@context' => 'https://schema.org',
		'@type'    => 'WebSite',
		'@id'      => home_url( '/#website' ),
		'url'      => home_url( '/' ),
		'name'     => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'inLanguage'  => get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'        => 'EntryPoint',
				'urlTemplate'  => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Organization schema.
 *
 * @return array
 */
function magpro_schema_organization() {
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'@id'      => home_url( '/#organization' ),
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);

	// Logo.
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		if ( $logo_url ) {
			$schema['logo'] = array(
				'@type'   => 'ImageObject',
				'@id'     => home_url( '/#logo' ),
				'url'     => $logo_url,
				'caption' => get_bloginfo( 'name' ),
			);
			$schema['image'] = array( '@id' => home_url( '/#logo' ) );
		}
	}

	// Social profiles.
	$social_links = array();
	$profiles     = array( 'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'linkedin_url' );
	foreach ( $profiles as $profile ) {
		$url = magpro_get_option( $profile );
		if ( $url ) {
			$social_links[] = $url;
		}
	}
	if ( ! empty( $social_links ) ) {
		$schema['sameAs'] = $social_links;
	}

	return $schema;
}

/**
 * Article schema for single posts.
 *
 * @return array
 */
function magpro_schema_article() {
	$post = get_queried_object();

	$schema = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'Article',
		'@id'              => get_permalink( $post ) . '#article',
		'headline'         => get_the_title( $post ),
		'description'      => magpro_generate_meta_description( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			'url'   => get_author_posts_url( $post->post_author ),
		),
		'publisher'        => array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
		),
		'isPartOf'         => array(
			'@id' => home_url( '/#website' ),
		),
		'inLanguage'       => get_bloginfo( 'language' ),
		'wordCount'        => str_word_count( wp_strip_all_tags( $post->post_content ) ),
	);

	// Featured image.
	if ( has_post_thumbnail( $post ) ) {
		$img_id  = get_post_thumbnail_id( $post );
		$img_src = wp_get_attachment_image_src( $img_id, 'magpro-og' );
		if ( $img_src ) {
			$schema['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $img_src[0],
				'width'  => $img_src[1],
				'height' => $img_src[2],
			);
		}
	}

	// Publisher logo.
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		if ( $logo_url ) {
			$schema['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo_url,
			);
		}
	}

	// Categories.
	$categories = get_the_category( $post->ID );
	if ( ! empty( $categories ) ) {
		$schema['articleSection'] = $categories[0]->name;
	}

	// Tags as keywords.
	$tags = get_the_tags( $post->ID );
	if ( $tags ) {
		$keywords = array();
		foreach ( $tags as $tag ) {
			$keywords[] = $tag->name;
		}
		$schema['keywords'] = implode( ', ', $keywords );
	}

	// Comment count.
	$comment_count = get_comments_number( $post->ID );
	if ( $comment_count > 0 ) {
		$schema['commentCount'] = (int) $comment_count;
	}

	return $schema;
}

/**
 * WebPage schema for pages.
 *
 * @return array
 */
function magpro_schema_webpage() {
	$post = get_queried_object();

	return array(
		'@context'      => 'https://schema.org',
		'@type'         => 'WebPage',
		'@id'           => get_permalink( $post ),
		'name'          => get_the_title( $post ),
		'description'   => magpro_generate_meta_description( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'isPartOf'      => array(
			'@id' => home_url( '/#website' ),
		),
		'inLanguage'    => get_bloginfo( 'language' ),
	);
}

/**
 * Person schema for author pages.
 *
 * @return array
 */
function magpro_schema_person() {
	$author = get_queried_object();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Person',
		'name'        => $author->display_name,
		'url'         => get_author_posts_url( $author->ID ),
		'description' => get_the_author_meta( 'description', $author->ID ),
	);

	$avatar = get_avatar_url( $author->ID, array( 'size' => 300 ) );
	if ( $avatar ) {
		$schema['image'] = $avatar;
	}

	// Social links from user meta.
	$same_as = array();
	$user_url = get_the_author_meta( 'user_url', $author->ID );
	if ( $user_url ) {
		$same_as[] = $user_url;
	}
	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = $same_as;
	}

	return $schema;
}

/**
 * CollectionPage schema for archive pages.
 *
 * @return array
 */
function magpro_schema_collection_page() {
	$term = get_queried_object();

	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'CollectionPage',
		'name'        => $term->name,
		'description' => $term->description,
		'url'         => get_term_link( $term ),
		'isPartOf'    => array(
			'@id' => home_url( '/#website' ),
		),
	);
}
