<?php
/**
 * Single post content template part.
 *
 * @package MagPro
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'magpro-single-article' ); ?>>
	<header class="magpro-single-header">
		<?php
		$category = magpro_get_primary_category();
		if ( $category ) :
		?>
		<span class="magpro-single-category">
			<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
		</span>
		<?php endif; ?>

		<?php the_title( '<h1 class="magpro-single-title">', '</h1>' ); ?>

		<?php magpro_post_meta( array( 'show_reading_time' => true ) ); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
	<figure class="magpro-single-featured">
		<?php
		echo magpro_post_thumbnail( get_the_ID(), 'magpro-hero', array(
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'class'         => 'magpro-single-img',
		) );
		$caption = get_the_post_thumbnail_caption();
		if ( $caption ) :
		?>
		<figcaption class="magpro-single-caption"><?php echo esc_html( $caption ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php do_action( 'magpro_after_hero' ); ?>
	<?php endif; ?>

	<div class="magpro-single-content entry-content">
		<?php
		the_content();

		wp_link_pages( array(
			'before' => '<div class="magpro-page-links"><span class="magpro-page-links-label">' . __( 'Pages:', 'magpro' ) . '</span>',
			'after'  => '</div>',
		) );
		?>
	</div>

	<footer class="magpro-single-footer">
		<?php
		$tags = get_the_tags();
		if ( $tags ) :
		?>
		<div class="magpro-single-tags">
			<span class="magpro-tags-label"><?php esc_html_e( 'Tags:', 'magpro' ); ?></span>
			<?php
			foreach ( $tags as $tag ) {
				echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="magpro-tag" rel="tag">' . esc_html( $tag->name ) . '</a>';
			}
			?>
		</div>
		<?php endif; ?>

		<div class="magpro-share-buttons">
			<span class="magpro-share-label"><?php esc_html_e( 'Share:', 'magpro' ); ?></span>
			<?php
			$share_urls = magpro_get_share_urls();
			$share_labels = array(
				'facebook'  => 'Facebook',
				'twitter'   => 'Twitter',
				'linkedin'  => 'LinkedIn',
				'whatsapp'  => 'WhatsApp',
				'telegram'  => 'Telegram',
				'pinterest' => 'Pinterest',
				'email'     => 'Email',
			);
			foreach ( $share_urls as $network => $url ) :
			?>
			<a href="<?php echo esc_url( $url ); ?>"
			   class="magpro-share-btn magpro-share-<?php echo esc_attr( $network ); ?>"
			   <?php echo 'email' !== $network ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
			   aria-label="<?php echo esc_attr( sprintf( __( 'Share on %s', 'magpro' ), $share_labels[ $network ] ) ); ?>">
				<?php echo esc_html( $share_labels[ $network ] ); ?>
			</a>
			<?php endforeach; ?>
		</div>
	</footer>
</article>
