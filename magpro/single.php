<?php
/**
 * Single post template.
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

				magpro_display_ad( 'before_content' );

				get_template_part( 'template-parts/content', 'single' );

				magpro_display_ad( 'after_content' );

				// Author box.
				$author_id   = get_the_author_meta( 'ID' );
				$author_bio  = get_the_author_meta( 'description' );
				if ( $author_bio ) :
				?>
				<div class="magpro-author-box">
					<div class="magpro-author-avatar">
						<?php echo get_avatar( $author_id, 100, '', '', array( 'loading' => 'lazy' ) ); ?>
					</div>
					<div class="magpro-author-info">
						<h4 class="magpro-author-name">
							<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
								<?php the_author(); ?>
							</a>
						</h4>
						<p class="magpro-author-bio"><?php echo esc_html( $author_bio ); ?></p>
					</div>
				</div>
				<?php endif; ?>

				<?php
				// Related posts.
				$related = magpro_get_related_posts( get_the_ID(), 4 );
				if ( $related->have_posts() ) :
				?>
				<section class="magpro-related-posts" aria-label="<?php esc_attr_e( 'Related Posts', 'magpro' ); ?>">
					<h3 class="magpro-section-title"><span><?php esc_html_e( 'Related Posts', 'magpro' ); ?></span></h3>
					<div class="magpro-posts-grid magpro-cols-2">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/post-card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
				<?php endif; ?>

				<?php
				// Post navigation.
				the_post_navigation( array(
					'prev_text' => '<span class="magpro-nav-label">' . __( 'Previous Article', 'magpro' ) . '</span><span class="magpro-nav-title">%title</span>',
					'next_text' => '<span class="magpro-nav-label">' . __( 'Next Article', 'magpro' ) . '</span><span class="magpro-nav-title">%title</span>',
					'class'     => 'magpro-post-navigation',
				) );
				?>

				<?php
				magpro_display_ad( 'before_comments' );

				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
				?>

			<?php endwhile; ?>

		</div>

		<?php if ( 'none' !== $sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
