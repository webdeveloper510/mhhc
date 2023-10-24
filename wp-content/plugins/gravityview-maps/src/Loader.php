<?php

namespace GravityKit\GravityMaps;

/**
 * Components loader
 *
 * @since     0.1.0
 */
class Loader {
	/**
	 * Components of this extension.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	protected $components = array(
		'Admin',
		'Settings',
		'Form_Fields',
		'Widgets',
		'Fields',
		'Cache_Markers',
		'Geocoding',
		'Render_Map',
		'Available_Icons',
		'GF_Entry_Geocoding',
		'Custom_Map_Icons',
		'Search_Filter',
	);

	/**
	 * Component instances.
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $component_instances = array();

	/**
	 * Path to the main plugin file, same as `plugin_file`.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $path = null;

	/**
	 * Path to the main plugin file, same as `path`.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $plugin_file = null;

	/**
	 * Plugin version.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $plugin_version = null;

	/**
	 * Plugin basename.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $dir = null;

	/**
	 * Includes directory.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $includes_dir = null;

	/**
	 * Templates directory.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $templates_dir = null;

	/**
	 * URL to the main plugin folder.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $url = null;

	/**
	 * Base JS URL.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $js_url = null;

	/**
	 * Base CSS URL.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	public $css_url = null;

	/**
	 * Store the correct version of the plugin loader, not in a global variable.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * Set properties and load components.
	 *
	 * @since 0.1.0
	 *
	 * @param string $plugin_file
	 * @param string $plugin_version
	 *
	 * @return void
	 */
	public function __construct( $plugin_file, $plugin_version ) {
		$this->path           = $plugin_file;
		$this->plugin_file    = $plugin_file;
		$this->plugin_version = $plugin_version;

		$this->set_properties();

		// Older components are loading still on `init` for backwards compatibility.
		add_action( 'init', array( $this, 'load_components' ) );

		// New components should be loaded after all plugins are loaded, `init` is too late.
		add_action( 'plugins_loaded', [ $this, 'load_plugin' ], 150 );

		static::$instance = $this;
	}

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.0
	 *
	 * @return self
	 */
	public static function instance(): self {
		return static::$instance;
	}

	/**
	 * Set properties of this extension that will be useful for components.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function set_properties() {
		// Directories.
		$this->dir           = trailingslashit( plugin_dir_path( $this->plugin_file ) );
		$this->includes_dir  = trailingslashit( $this->dir . 'src' );
		$this->templates_dir = trailingslashit( $this->dir . 'templates' );

		// URLs.
		$this->url     = trailingslashit( plugin_dir_url( $this->plugin_file ) );
		$this->js_url  = trailingslashit( $this->url . 'assets/js' );
		$this->css_url = trailingslashit( $this->url . 'assets/css' );
	}

	/**
	 * Loads components.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function load_components() {
		foreach ( $this->components as $component ) {
			$class = __NAMESPACE__ . '\\' . $component;

			$this->component_instances[ $component ] = new $class( $this );
			$this->component_instances[ $component ]->load();
		}
	}

	/**
	 * Loads controllers and newer components.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function load_plugin(): void {
		Views\Controller::instance();
		Templates\Controller::instance();

		/**
		 * Fires when the plugin is loaded.
		 *
		 * Note that at this point not everything is loaded, for backwards compatibility a lot still loads in `init`.
		 *
		 * @since 3.0
		 */
		do_action( 'gk/gravitymaps/loaded' );
	}
}
