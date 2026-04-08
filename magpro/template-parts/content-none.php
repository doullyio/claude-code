<?php
/**
 * Template part for no content found.
 *
 * @package MagPro
 */

?>
<section class="magpro-no-results">
	<header class="magpro-no-results-header">
		<h1 class="magpro-no-results-title"><?php esc_html_e( 'Nothing Found', 'magpro' ); ?></h1>
	</header>

	<div class="magpro-no-results-content">
		<?php if ( is_search() ) : ?>
			<p><?php esc_html_e( 'Sorry, no results matched your search terms. Please try again with different keywords.', 'magpro' ); ?></p>
			<?php get_search_form(); ?>
		<?php elseif ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				printf(
					/* translators: %s: URL to create new post */
					wp_kses( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'magpro' ), array( 'a' => array( 'href' => array() ) ) ),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
		<?php else : ?>
			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Try searching.', 'magpro' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
