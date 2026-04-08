<?php
/**
 * Search results template.
 *
 * @package MagPro
 */

get_header();

$sidebar = get_theme_mod( 'magpro_sidebar_position', 'right' );
?>

<div class="magpro-container">
	<?php magpro_breadcrumbs(); ?>

	<div class="magpro-content-wrap <?php echo 'none' !== $sidebar ? 'has-sidebar' : 'full-width'; ?>">
		<div class="magpro-content-area">

			<header class="magpro-archive-header">
				<h1 class="magpro-archive-title">
					<?php
					printf(
						/* translators: %s: search query */
						esc_html__( 'Search Results for: %s', 'magpro' ),
						'<span>' . esc_html( get_search_query() ) . '</span>'
					);
					?>
				</h1>
				<p class="magpro-search-count">
					<?php
					global $wp_query;
					printf(
						/* translators: %d: number of results */
						esc_html( _n( '%d result found', '%d results found', $wp_query->found_posts, 'magpro' ) ),
						(int) $wp_query->found_posts
					);
					?>
				</p>
			</header>

			<?php if ( have_posts() ) : ?>

				<div class="magpro-posts-grid magpro-cols-<?php echo esc_attr( get_theme_mod( 'magpro_posts_per_row', '3' ) ); ?>">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/post-card' );
					endwhile;
					?>
				</div>

				<nav class="magpro-pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'magpro' ); ?>">
					<?php
					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => '<span aria-hidden="true">&laquo;</span> <span class="screen-reader-text">' . __( 'Previous', 'magpro' ) . '</span>',
						'next_text' => '<span class="screen-reader-text">' . __( 'Next', 'magpro' ) . '</span> <span aria-hidden="true">&raquo;</span>',
					) );
					?>
				</nav>

			<?php else : ?>

				<div class="magpro-no-results">
					<p><?php esc_html_e( 'No results found. Try a different search term.', 'magpro' ); ?></p>
					<?php get_search_form(); ?>
				</div>

			<?php endif; ?>

		</div>

		<?php if ( 'none' !== $sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
