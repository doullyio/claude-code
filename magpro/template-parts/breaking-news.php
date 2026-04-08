<?php
/**
 * Breaking news ticker template part.
 *
 * @package MagPro
 */

$tag_slug = get_theme_mod( 'magpro_breaking_news_tag', 'breaking' );

$breaking = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 8,
	'post_status'    => 'publish',
	'tag'            => $tag_slug,
	'no_found_rows'  => true,
) );

if ( ! $breaking->have_posts() ) {
	// Fallback: show latest posts.
	$breaking = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 8,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	) );
}

if ( ! $breaking->have_posts() ) {
	return;
}
?>

<div class="magpro-breaking-news" role="marquee" aria-label="<?php esc_attr_e( 'Breaking News', 'magpro' ); ?>">
	<div class="magpro-container magpro-breaking-inner">
		<span class="magpro-breaking-label"><?php esc_html_e( 'Breaking', 'magpro' ); ?></span>
		<div class="magpro-breaking-ticker" id="magpro-ticker">
			<ul class="magpro-ticker-list">
				<?php
				while ( $breaking->have_posts() ) :
					$breaking->the_post();
				?>
				<li class="magpro-ticker-item">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</li>
				<?php endwhile; ?>
			</ul>
		</div>
		<div class="magpro-breaking-controls">
			<button class="magpro-ticker-prev" aria-label="<?php esc_attr_e( 'Previous', 'magpro' ); ?>">&lsaquo;</button>
			<button class="magpro-ticker-next" aria-label="<?php esc_attr_e( 'Next', 'magpro' ); ?>">&rsaquo;</button>
		</div>
	</div>
</div>

<?php wp_reset_postdata(); ?>
