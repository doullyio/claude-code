<?php
/**
 * Ad unit template part.
 *
 * Usage: get_template_part( 'template-parts/ad-unit' );
 * Set $args['location'] to specify the ad location.
 *
 * @package MagPro
 */

$location = isset( $args['location'] ) ? $args['location'] : 'default';
magpro_display_ad( $location );
