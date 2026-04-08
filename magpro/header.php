<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary-content"><?php esc_html_e( 'Skip to content', 'magpro' ); ?></a>

<header id="masthead" class="magpro-header" role="banner">
	<?php if ( has_nav_menu( 'secondary' ) ) : ?>
	<div class="magpro-topbar">
		<div class="magpro-container">
			<nav class="magpro-topbar-nav" aria-label="<?php esc_attr_e( 'Secondary Menu', 'magpro' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'secondary',
					'menu_class'     => 'magpro-topbar-menu',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</nav>
			<div class="magpro-topbar-right">
				<?php if ( get_theme_mod( 'magpro_dark_mode', false ) ) : ?>
				<button class="magpro-dark-toggle" id="magpro-dark-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'magpro' ); ?>">
					<span class="magpro-icon-sun" aria-hidden="true">&#9788;</span>
					<span class="magpro-icon-moon" aria-hidden="true">&#9790;</span>
				</button>
				<?php endif; ?>
				<time class="magpro-topbar-date" datetime="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></time>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="magpro-header-main">
		<div class="magpro-container magpro-header-inner">
			<div class="magpro-branding">
				<?php if ( has_custom_logo() ) : ?>
					<div class="magpro-logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<div class="magpro-site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
						<?php
						$description = get_bloginfo( 'description', 'display' );
						if ( $description ) :
						?>
						<p class="magpro-site-description"><?php echo esc_html( $description ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( is_active_sidebar( 'header-ad' ) ) : ?>
			<div class="magpro-header-ad-area">
				<?php dynamic_sidebar( 'header-ad' ); ?>
			</div>
			<?php else : ?>
				<?php magpro_display_ad( 'header' ); ?>
			<?php endif; ?>
		</div>
	</div>

	<nav id="site-navigation" class="magpro-main-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'magpro' ); ?>">
		<div class="magpro-container magpro-nav-inner">
			<button class="magpro-menu-toggle" id="magpro-menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'magpro' ); ?>">
				<span class="magpro-hamburger" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
				</span>
			</button>

			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_id'        => 'primary-menu',
				'menu_class'     => 'magpro-primary-menu',
				'container'      => false,
				'depth'          => 3,
				'fallback_cb'    => 'magpro_fallback_menu',
			) );
			?>

			<div class="magpro-nav-search">
				<button class="magpro-search-toggle" id="magpro-search-toggle" aria-label="<?php esc_attr_e( 'Toggle search', 'magpro' ); ?>" aria-expanded="false">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</button>
				<div class="magpro-search-overlay" id="magpro-search-overlay" hidden>
					<div class="magpro-search-overlay-inner">
						<?php get_search_form(); ?>
						<button class="magpro-search-close" aria-label="<?php esc_attr_e( 'Close search', 'magpro' ); ?>">&times;</button>
					</div>
				</div>
			</div>
		</div>
	</nav>

	<?php
	if ( get_theme_mod( 'magpro_breaking_news', true ) && ( is_home() || is_front_page() ) ) {
		get_template_part( 'template-parts/breaking-news' );
	}
	?>
</header>

<?php magpro_display_ad( 'below_header' ); ?>

<div id="page" class="magpro-site">
	<main id="primary-content" class="magpro-main" role="main">
<?php
/**
 * Fallback menu if no menu is assigned.
 */
function magpro_fallback_menu() {
	echo '<ul class="magpro-primary-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'magpro' ) . '</a></li>';
	wp_list_pages( array(
		'title_li' => '',
		'depth'    => 1,
	) );
	echo '</ul>';
}
