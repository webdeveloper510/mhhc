<?php
/**
 * The GravityView DataTables Extension plugin
 *
 * Display entries in a dynamic table powered by DataTables & GravityView.
 *
 * @package   GravityView-DataTables-Ext
 * @license   GPL2+
 * @author    GravityKit <hello@gravitykit.com>
 * @link      https://www.gravitykit.com
 * @copyright Copyright 2021, Katz Web Services, Inc.
 *
 * @wordpress-plugin
 * Plugin Name: GravityView - DataTables Extension
 * Plugin URI: https://www.gravitykit.com/extensions/datatables/
 * Description: Display entries in a dynamic table powered by DataTables & GravityView.
 * Version: 3.2
 * Author: The GravityKit Team
 * Author URI:  https://www.gravitykit.com
 * Text Domain: gv-datatables
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'GV_DT_VERSION', '3.2' );

/** @define "GV_DT_FILE" "./" */
define( 'GV_DT_FILE', __FILE__ );

define( 'GV_DT_URL', plugin_dir_url( __FILE__ ) );

/** @define "GV_DT_DIR" "./" */
define( 'GV_DT_DIR', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', 'gv_extension_datatables_load' );

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Wrapper function to make sure GravityView_Extension has loaded
 * @return void
 */
function gv_extension_datatables_load() {
	if ( ! class_exists( 'GravityView_Extension' ) && ! class_exists( '\GV\Core' ) ) {
		// We prefer to use the one bundled with GravityView, but if it doesn't exist, go here.
		include_once GV_DT_DIR . 'lib/class-gravityview-extension.php';
	}

	class GV_Extension_DataTables extends GravityView_Extension {

		protected $_title = 'DataTables';

		protected $_version = GV_DT_VERSION;

		const version = GV_DT_VERSION;

		/**
		 * @var int The download ID on the GravityKit website
		 * @since 1.3.2
		 */
		protected $_item_id = 268;

		protected $_text_domain = 'gv-datatables';

		protected $_min_gravityview_version = '2.15';

		protected $_path = GV_DT_FILE;

		public function add_hooks() {

			// load DataTables admin logic
			add_action( 'gravityview_include_backend_actions', array( $this, 'backend_actions' ) );

			// load DataTables core logic
			add_action( 'init', array( $this, 'core_actions' ), 19 );

			// Register specific template. Run at 30 priority because GravityView_Plugin::frontend_actions() runs at 20
			add_action( 'init', array( $this, 'register_templates' ), 30 );

		}

		function backend_actions() {
			include_once GV_DT_DIR . 'includes/class-admin-datatables.php';
			include_once GV_DT_DIR . 'includes/class-datatables-migrate.php';
		}

		function core_actions() {
			include_once GV_DT_DIR . 'includes/class-datatables-data.php';

			include_once GV_DT_DIR . 'includes/extensions/class-datatables-extension.php';
			include_once GV_DT_DIR . 'includes/class-datatables-field-filters.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-search.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-buttons.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-scroller.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-fixedheader.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-responsive.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-auto-update.php';
			include_once GV_DT_DIR . 'includes/extensions/class-datatables-rowgroup.php';
		}

		function register_templates() {
			include_once GV_DT_DIR . 'includes/class-datatables-template.php';
			include_once GV_DT_DIR . 'includes/class-gv-template-view-datatable.php';
			include_once GV_DT_DIR . 'includes/class-gv-template-entry-datatable.php';
		}
	}

	new GV_Extension_DataTables;
}

// Register the extension with Foundation, which will enable translations and other features.
add_action( 'gravityview/loaded', function () {
	if ( ! class_exists( 'GravityKit\GravityView\Foundation\Core' ) ) {
		return;
	}

	GravityKit\GravityView\Foundation\Core::register( __FILE__ );
} );
