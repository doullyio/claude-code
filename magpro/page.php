<?php
/**
 * Page template.
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

			<?php
			while ( have_posts() ) :
				the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'magpro-page' ); ?>>
				<header class="magpro-page-header">
					<?php the_title( '<h1 class="magpro-page-title">', '</h1>' ); ?>
				</header>

				<div class="magpro-page-content entry-content">
					<?php
					the_content();

					wp_link_pages( array(
						'before' => '<div class="magpro-page-links">' . __( 'Pages:', 'magpro' ),
						'after'  => '</div>',
					) );
					?>
				</div>

				<?php
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
				?>
			</article>
			<?php endwhile; ?>

		</div>

		<?php if ( 'none' !== $sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
