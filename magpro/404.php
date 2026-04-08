<?php
/**
 * 404 (Not Found) template.
 *
 * @package MagPro
 */

get_header();
?>

<div class="magpro-container">
	<?php magpro_breadcrumbs(); ?>

	<div class="magpro-content-wrap full-width">
		<div class="magpro-content-area">

			<section class="magpro-404">
				<div class="magpro-404-content">
					<h1 class="magpro-404-title">404</h1>
					<h2 class="magpro-404-subtitle"><?php esc_html_e( 'Page Not Found', 'magpro' ); ?></h2>
					<p class="magpro-404-message"><?php esc_html_e( 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'magpro' ); ?></p>

					<div class="magpro-404-search">
						<?php get_search_form(); ?>
					</div>

					<div class="magpro-404-links">
						<h3><?php esc_html_e( 'Popular Articles', 'magpro' ); ?></h3>
						<?php
						$popular = new WP_Query( array(
							'post_type'      => 'post',
							'posts_per_page' => 6,
							'orderby'        => 'comment_count',
							'order'          => 'DESC',
							'no_found_rows'  => true,
						) );

						if ( $popular->have_posts() ) :
						?>
						<div class="magpro-posts-grid magpro-cols-3">
							<?php
							while ( $popular->have_posts() ) :
								$popular->the_post();
								get_template_part( 'template-parts/post-card' );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</section>

		</div>
	</div>
</div>

<?php get_footer(); ?>
