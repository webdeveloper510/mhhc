<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mhhc' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'Admin@123' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '(hW_R>>^e@H(-.S.kDq2c}/b(DYc8k_9N+D.dG wEMM.+?/xDTboo6i0Y$%N!2+K' );
define( 'SECURE_AUTH_KEY',  '*;#Pp(Y8cSR{han=}]xN|:w=X<:^S+OyAgKz41a!7,Bs 6Y22~CREyY;Js_@yX}q' );
define( 'LOGGED_IN_KEY',    '^WC;Q7uK=9c;zRZ+hBq#qdI>Vj +38s&C1jiKdPT)l/-+qpnux=_poA>J3:+?PM>' );
define( 'NONCE_KEY',        'u-W3.4kzvy5LGC%?i`KV,de&QjV!%CeyB+(*;;Tpk@Op&PF,1(=^zT d{8kT ?!g' );
define( 'AUTH_SALT',        'Q@WkZ,CF1ps]D8#CTzZ`~d!E)&$jYOeAjd(/Ve>82%B}fYhClKC6bq,q|an!e-1q' );
define( 'SECURE_AUTH_SALT', 'L+Y(Tva7z!TvnteI6,&:O}JE4ba a}xw,N46aD!QZ@i (QC&9Y{_d;+ r?NZ|Dxv' );
define( 'LOGGED_IN_SALT',   'k6Q>y`RrDWvmJ]@0KQ1Lq4IJ.-C1<T_pxRgfJAO(uu n}<:E)^w8!z2e}Ua,UR*e' );
define( 'NONCE_SALT',       'V(=hY2ZkMHspd=)1j..h)_u:lewpS=PwPv/.?{.}[{bv55D#>6}EcmmlpGbuAv4X' );

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
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define('FS_METHOD','direct');

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
