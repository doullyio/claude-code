<?php
/**
 * Archive template.
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
				<?php the_archive_title( '<h1 class="magpro-archive-title">', '</h1>' ); ?>
				<?php the_archive_description( '<div class="magpro-archive-desc">', '</div>' ); ?>
			</header>

			<?php magpro_display_ad( 'before_content' ); ?>

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
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>

		</div>

		<?php if ( 'none' !== $sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
