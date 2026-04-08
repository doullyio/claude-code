<?php
/**
 * The main template file.
 *
 * @package MagPro
 */

get_header();

$layout = get_theme_mod( 'magpro_homepage_layout', 'magazine' );
$sidebar = get_theme_mod( 'magpro_sidebar_position', 'right' );
?>

<div class="magpro-container">
	<?php magpro_breadcrumbs(); ?>

	<div class="magpro-content-wrap <?php echo 'none' !== $sidebar ? 'has-sidebar' : 'full-width'; ?>">
		<div class="magpro-content-area">

			<?php if ( have_posts() ) : ?>

				<?php if ( is_home() && ! is_paged() && 'magazine' === $layout ) : ?>
					<?php
					// Featured/hero section for magazine layout.
					$hero_query = new WP_Query( array(
						'post_type'      => 'post',
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'meta_key'       => '_magpro_featured',
						'meta_value'     => '1',
					) );

					// Fallback to latest post if no featured post.
					if ( ! $hero_query->have_posts() ) {
						$hero_query = new WP_Query( array(
							'post_type'      => 'post',
							'posts_per_page' => 1,
							'post_status'    => 'publish',
						) );
					}

					if ( $hero_query->have_posts() ) :
						$hero_query->the_post();
					?>
					<article class="magpro-hero-article">
						<a href="<?php the_permalink(); ?>" class="magpro-hero-link">
							<div class="magpro-hero-image">
								<?php
								echo magpro_post_thumbnail( get_the_ID(), 'magpro-hero', array(
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'class'         => 'magpro-hero-img',
								) );
								?>
							</div>
							<div class="magpro-hero-content">
								<?php
								$cat = magpro_get_primary_category();
								if ( $cat ) :
								?>
								<span class="magpro-hero-category"><?php echo esc_html( $cat->name ); ?></span>
								<?php endif; ?>
								<h2 class="magpro-hero-title"><?php the_title(); ?></h2>
								<div class="magpro-hero-excerpt"><?php echo esc_html( magpro_truncate_words( get_the_excerpt(), 20 ) ); ?></div>
								<?php magpro_post_meta(); ?>
							</div>
						</a>
					</article>
					<?php
					wp_reset_postdata();
					endif;
					?>

					<?php magpro_display_ad( 'before_content' ); ?>

					<div class="magpro-posts-grid magpro-cols-<?php echo esc_attr( get_theme_mod( 'magpro_posts_per_row', '3' ) ); ?>">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/post-card' );
						endwhile;
						?>
					</div>

				<?php else : ?>

					<?php magpro_display_ad( 'before_content' ); ?>

					<div class="magpro-posts-grid magpro-cols-<?php echo esc_attr( get_theme_mod( 'magpro_posts_per_row', '3' ) ); ?>">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/post-card' );
						endwhile;
						?>
					</div>

				<?php endif; ?>

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
