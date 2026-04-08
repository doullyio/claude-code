<?php
/**
 * Post card template part for grid layouts.
 *
 * @package MagPro
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'magpro-post-card' ); ?>>
	<div class="magpro-card-thumb">
		<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php echo magpro_post_thumbnail( get_the_ID(), 'magpro-card' ); ?>
		</a>
		<?php
		$category = magpro_get_primary_category();
		if ( $category ) :
		?>
		<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="magpro-card-category"><?php echo esc_html( $category->name ); ?></a>
		<?php endif; ?>
	</div>

	<div class="magpro-card-content">
		<?php the_title( '<h3 class="magpro-card-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h3>' ); ?>

		<div class="magpro-card-meta">
			<time class="magpro-card-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<span class="magpro-card-reading-time">
				<?php
				$minutes = magpro_reading_time();
				/* translators: %d: number of minutes */
				printf( esc_html( _n( '%d min', '%d min', $minutes, 'magpro' ) ), $minutes );
				?>
			</span>
		</div>

		<div class="magpro-card-excerpt">
			<?php echo esc_html( magpro_truncate_words( get_the_excerpt(), 15 ) ); ?>
		</div>
	</div>
</article>
