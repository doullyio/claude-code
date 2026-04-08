	</main><!-- .magpro-main -->
</div><!-- #page -->

<?php magpro_display_ad( 'footer' ); ?>

<footer id="colophon" class="magpro-footer" role="contentinfo">
	<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
	<div class="magpro-footer-widgets">
		<div class="magpro-container">
			<div class="magpro-footer-grid">
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="magpro-footer-col">
					<?php dynamic_sidebar( 'footer-1' ); ?>
				</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
				<div class="magpro-footer-col">
					<?php dynamic_sidebar( 'footer-2' ); ?>
				</div>
				<?php endif; ?>

				<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
				<div class="magpro-footer-col">
					<?php dynamic_sidebar( 'footer-3' ); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="magpro-footer-bottom">
		<div class="magpro-container magpro-footer-bottom-inner">
			<div class="magpro-copyright">
				<?php
				$copyright = get_theme_mod( 'magpro_footer_copyright' );
				if ( $copyright ) {
					$copyright = str_replace( '{year}', gmdate( 'Y' ), $copyright );
					$copyright = str_replace( '{site}', get_bloginfo( 'name' ), $copyright );
					echo wp_kses_post( $copyright );
				} else {
					printf(
						/* translators: 1: current year, 2: site name */
						esc_html__( '&copy; %1$s %2$s. All rights reserved.', 'magpro' ),
						esc_html( gmdate( 'Y' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
				}
				?>
			</div>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="magpro-footer-nav" aria-label="<?php esc_attr_e( 'Footer Menu', 'magpro' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'menu_class'     => 'magpro-footer-menu',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</nav>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php if ( get_theme_mod( 'magpro_back_to_top', true ) ) : ?>
<button class="magpro-back-to-top" id="magpro-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'magpro' ); ?>" hidden>
	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>
</button>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
