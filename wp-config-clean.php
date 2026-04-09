<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u393073823_IXRji' );

/** Database username */
define( 'DB_USER', 'u393073823_H06dA' );

/** Database password */
define( 'DB_PASSWORD', 'lkJ3SUCxzg' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',          '*>KC3a^>l 6[IfLz|P$Cq$mdI=5Ft jcw]43fhdioy$@<y~laJ[EKj$;#]GDZ7`:' );
define( 'SECURE_AUTH_KEY',   'H6a;XNVgY5Et59yqmWrn+{BtN*a}5-u{ZM%J`>z|Mq^yPiDu.$`4%MosV`t,Y-B/' );
define( 'LOGGED_IN_KEY',     'e4;`O>pppwk>]Y:0uzb1!6@JA,naZ<a^S&2T=rS!Q9>7NLW/3XqXV)a8R$dFHMeN' );
define( 'NONCE_KEY',         'ekp/l$M8_~!t]K}z`8kN!R_%(OB+sE_Z#@9PFNyyHpL(Um$a -L>}d;>ZTldVN*x' );
define( 'AUTH_SALT',         'zg.q1)?GOdW.]]leRl0(_nbR.sQ:+)8|vP,MtiI4dk`&d?4+AtaJ}yxm%r1G~@jk' );
define( 'SECURE_AUTH_SALT',  'HKKG{#JvfDp,,k2Z@t:~iOE.?e.AZ5U2)Q.&=V361-SF7]IUU^PI8E6Ozv:N6wF}' );
define( 'LOGGED_IN_SALT',    '4X,fA,v;)KfN6=  ;?]_>-_4+c/  @VR_nGA>wDasLA&BF#>AUI}[<Jzk1D8*d|@' );
define( 'NONCE_SALT',        '4Yd[gFs/q8{s|+B:VF{^~X]E*0uu2yjj fWR-u5P+F$<1(X5XnoXh5yY+o&oMc#t' );
define( 'WP_CACHE_KEY_SALT', 'nI P/wBe_}CQlQCwB4F;/zIm|VubC{EsY+Qn]*}0}[`qr-;z)NbXJZMI*HAckhGM' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', 'cc73255bcd014a857fcb351c4500e26d' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
