<?php
/**
 * Default content template part.
 *
 * @package MagPro
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'magpro-article' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
	<div class="magpro-article-thumb">
		<a href="<?php the_permalink(); ?>">
			<?php echo magpro_post_thumbnail( get_the_ID(), 'magpro-featured' ); ?>
		</a>
	</div>
	<?php endif; ?>

	<div class="magpro-article-content">
		<?php
		$category = magpro_get_primary_category();
		if ( $category ) :
		?>
		<span class="magpro-article-category">
			<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
		</span>
		<?php endif; ?>

		<?php the_title( '<h2 class="magpro-article-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>

		<?php magpro_post_meta(); ?>

		<div class="magpro-article-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="magpro-read-more"><?php esc_html_e( 'Read More', 'magpro' ); ?></a>
	</div>
</article>
