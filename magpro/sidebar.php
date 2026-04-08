<?php
/**
 * Sidebar template.
 *
 * @package MagPro
 */

if ( ! is_active_sidebar( 'sidebar-main' ) ) {
	return;
}
?>

<aside id="secondary" class="magpro-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'magpro' ); ?>">
	<?php magpro_display_ad( 'sidebar_top' ); ?>

	<?php dynamic_sidebar( 'sidebar-main' ); ?>

	<?php magpro_display_ad( 'sidebar_sticky', array( 'class' => 'magpro-ad-unit magpro-ad-sticky' ) ); ?>
</aside>
