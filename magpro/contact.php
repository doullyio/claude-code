<?php
/**
 * Contact page template.
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

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'magpro-contact-page' ); ?>>
				<header class="magpro-page-header">
					<?php the_title( '<h1 class="magpro-page-title">', '</h1>' ); ?>
				</header>

				<div class="magpro-contact-content entry-content">
					<?php the_content(); ?>
				</div>

				<div class="magpro-contact-wrapper">
					<div class="magpro-contact-form-wrapper">
						<h2><?php esc_html_e( 'Send us a Message', 'magpro' ); ?></h2>
						<?php
						// Display contact form.
						if ( shortcode_exists( 'contact-form-7' ) ) {
							// If CF7 is installed.
							$contact_form_shortcode = get_theme_mod( 'magpro_contact_form_shortcode' );
							if ( $contact_form_shortcode ) {
								echo do_shortcode( $contact_form_shortcode );
							} else {
								echo '<p class="magpro-contact-notice">' . esc_html__( 'Contact form not configured. Please add a Contact Form 7 shortcode in Customizer > MagPro Settings > Contact.', 'magpro' ) . '</p>';
							}
						} else {
							// Fallback: basic contact form.
							?>
							<form class="magpro-contact-form" method="post" action="">
								<div class="magpro-form-group">
									<label for="contact-name"><?php esc_html_e( 'Name', 'magpro' ); ?> *</label>
									<input type="text" id="contact-name" name="contact_name" required>
								</div>

								<div class="magpro-form-group">
									<label for="contact-email"><?php esc_html_e( 'Email', 'magpro' ); ?> *</label>
									<input type="email" id="contact-email" name="contact_email" required>
								</div>

								<div class="magpro-form-group">
									<label for="contact-subject"><?php esc_html_e( 'Subject', 'magpro' ); ?> *</label>
									<input type="text" id="contact-subject" name="contact_subject" required>
								</div>

								<div class="magpro-form-group">
									<label for="contact-message"><?php esc_html_e( 'Message', 'magpro' ); ?> *</label>
									<textarea id="contact-message" name="contact_message" rows="6" required></textarea>
								</div>

								<button type="submit" class="magpro-btn magpro-btn-primary"><?php esc_html_e( 'Send Message', 'magpro' ); ?></button>
							</form>
							<?php
						}
						?>
					</div>

					<div class="magpro-contact-info">
						<h2><?php esc_html_e( 'Contact Information', 'magpro' ); ?></h2>

						<?php
						$phone = get_theme_mod( 'magpro_contact_phone' );
						$email = get_theme_mod( 'magpro_contact_email' );
						$address = get_theme_mod( 'magpro_contact_address' );
						?>

						<?php if ( $email ) : ?>
						<div class="magpro-contact-item">
							<h3><?php esc_html_e( 'Email', 'magpro' ); ?></h3>
							<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
						</div>
						<?php endif; ?>

						<?php if ( $phone ) : ?>
						<div class="magpro-contact-item">
							<h3><?php esc_html_e( 'Phone', 'magpro' ); ?></h3>
							<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
						</div>
						<?php endif; ?>

						<?php if ( $address ) : ?>
						<div class="magpro-contact-item">
							<h3><?php esc_html_e( 'Address', 'magpro' ); ?></h3>
							<p><?php echo wp_kses_post( nl2br( $address ) ); ?></p>
						</div>
						<?php endif; ?>

						<div class="magpro-contact-social">
							<h3><?php esc_html_e( 'Follow Us', 'magpro' ); ?></h3>
							<?php
							$networks = array(
								'facebook_url'  => 'Facebook',
								'twitter_url'   => 'Twitter',
								'instagram_url' => 'Instagram',
								'youtube_url'   => 'YouTube',
								'linkedin_url'  => 'LinkedIn',
							);

							foreach ( $networks as $key => $label ) {
								$url = magpro_get_option( $key );
								if ( $url ) {
									echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</a>';
								}
							}
							?>
						</div>
					</div>
				</div>
			</article>

		</div>

		<?php if ( 'none' !== $sidebar ) : ?>
			<?php get_sidebar(); ?>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
