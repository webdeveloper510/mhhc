<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation;

use GravityKit\GravityImport\Foundation\Integrations\GravityForms;
use GravityKit\GravityImport\Foundation\Integrations\HelpScout;
use GravityKit\GravityImport\Foundation\Integrations\TrustedLogin;
use GravityKit\GravityImport\Foundation\WP\AdminMenu;
use GravityKit\GravityImport\Foundation\Logger\Framework as LoggerFramework;
use GravityKit\GravityImport\Foundation\WP\AjaxRouter;
use GravityKit\GravityImport\Foundation\CLI\CLI;
use GravityKit\GravityImport\Foundation\WP\PluginActivationHandler;
use GravityKit\GravityImport\Foundation\Settings\Framework as SettingsFramework;
use GravityKit\GravityImport\Foundation\Licenses\Framework as LicensesFramework;
use GravityKit\GravityImport\Foundation\Translations\Framework as TranslationsFramework;
use GravityKit\GravityImport\Foundation\Encryption\Encryption;
use GravityKit\GravityImport\Foundation\Helpers\Core as CoreHelpers;
use GravityKit\GravityImport\Foundation\Helpers\Arr;
use GravityKit\GravityImport\Foundation\WP\RESTController;
use GravityKitFoundation;

/**
 * Core class that initializes Foundation.
 *
 * @method static AjaxRouter ajax_router()
 * @method static RESTController rest_controller()
 * @method static Encryption encryption()
 * @method static TrustedLogin trustedlogin()
 * @method static HelpScout helpscout()
 * @method static GravityForms gravityforms()
 * @method static Logger logger( string $logger_name = null, string $logger_title = null )
 * @method static Settings settings()
 * @method static Licenses licenses()
 * @method static Translations translations()
 * @method static AdminMenu admin_menu()
 * @method static PluginActivationHandler plugin_activation_handler()
 */
class Core {
	const VERSION = '1.2.2';

	const ID = 'gk_foundation';

	const INIT_PRIORITY = 100;

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Core
	 */
	private static $_instance;

	/**
	 * Instance of plugin activation/deactivation handler class.
	 *
	 * @since 1.0.0
	 *
	 * @var PluginActivationHandler
	 */
	private $_plugin_activation_handler;

	/**
	 * Absolute paths to the plugin files that instantiated this class.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $_registered_plugins = [];

	/**
	 * Instances of various components that make up the Core functionality.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $_components = [];

	/**
	 * Random string generated once for the current request.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private static $_request_unique_string;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Absolute path to the main plugin file.
	 *
	 * @return void
	 */
	private function __construct( $plugin_file ) {
		$this->_plugin_activation_handler = new PluginActivationHandler();

		$this->_plugin_activation_handler->register_hooks( $plugin_file );

		$this->_registered_plugins = [
			$plugin_file => [
				'plugin_file'        => $plugin_file,
				'text_domain'        => CoreHelpers::get_plugin_data( $plugin_file )['TextDomain'],
				'foundation_version' => self::VERSION,
				'loads_foundation'   => true,
			],
		];

		add_filter(
			'gk/foundation/get-instance',
			function ( $passed_instance ) use ( $plugin_file ) {
				if ( ! $passed_instance || ! defined( get_class( $passed_instance ) . '::VERSION' ) || ! is_callable( [ $passed_instance, 'get_registered_plugins' ] ) ) {
					return $this;
				}

				$instance_to_return = version_compare( $passed_instance::VERSION, self::VERSION, '<' ) ? $this : $passed_instance;

				/**
				 * Controls whether the Foundation standalone plugin instance should always be returned regardless of the version.
				 *
				 * @filter gk/foundation/force-standalone-foundation-instance
				 *
				 * @since  1.0.2
				 *
				 * @param bool $force_standalone_instance Default: true.
				 */
				$force_standalone_instance = apply_filters( 'gk/foundation/force-standalone-foundation-instance', true );

				if ( $force_standalone_instance ) {
					$plugin_data = CoreHelpers::get_plugin_data( $plugin_file );

					if ( 'gk-foundation' === Arr::get( $plugin_data, 'TextDomain' ) ) {
						$instance_to_return = $this;
					}
				}

				// We need to make sure that the returned instance contains a list of all registered plugins that may have come with another passed instance.
				$registered_plugins = array_merge( $this->_registered_plugins, $passed_instance->get_registered_plugins() );

				$instance_to_return->set_registered_plugins( $registered_plugins );

				return $instance_to_return;
			}
		);

		add_action(
			'plugins_loaded',
			function () {
				if ( class_exists( 'GravityKitFoundation' ) ) {
					return;
				}

				$gk_foundation = apply_filters( 'gk/foundation/get-instance', null );

				if ( ! $gk_foundation ) {
					return;
				}

				$gk_foundation->init();
			},
			self::INIT_PRIORITY
		);
	}

	/**
	 * Registers class instance.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Absolute path to the main plugin file.
	 *
	 * @return void
	 */
	public static function register( $plugin_file ) {
		if ( wp_doing_ajax() &&
		     ( LicensesFramework::AJAX_ROUTER === ( $_REQUEST['ajaxRouter'] ?? '' ) ) &&
		     version_compare( $_REQUEST['frontendFoundationVersion'] ?? 0, self::VERSION, '<' )
		) {
			return;
		}

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_file );
		} elseif ( ! isset( self::$_instance->_registered_plugins[ $plugin_file ] ) ) {
			self::$_instance->_registered_plugins[ $plugin_file ] = [
				'plugin_file'        => $plugin_file,
				'text_domain'        => CoreHelpers::get_plugin_data( $plugin_file )['TextDomain'],
				'foundation_version' => self::$_instance->get_plugin_foundation_version( $plugin_file ),
				'has_foundation'     => false,
				'loads_foundation'   => false
			];
		}
	}

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Core
	 */
	public static function get_instance() {
		return self::$_instance;
	}

	/**
	 * Returns a list of plugins that have instantiated Foundation.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_registered_plugins() {
		return $this->_registered_plugins;
	}

	/**
	 * Sets a list of plugins that have instantiated Foundation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins Array of absolute paths to the plugin files that instantiated this class.
	 *
	 * @return void
	 */
	public function set_registered_plugins( $plugins ) {
		$registered_plugins = $this->_registered_plugins;

		foreach ( $plugins as $plugin ) {
			if ( is_array( $plugin ) ) {
				if ( ! isset( $registered_plugins[ $plugin['plugin_file'] ] ) ) {
					// 'loads_foundation' is set to true in __construct() for the plugin that instantiated Foundation (i.e., this instance), so we need to set it to false for all other plugins.
					$plugin['loads_foundation'] = false;

					$registered_plugins[ $plugin['plugin_file'] ] = $plugin;
				}
			} else {
				// Backward compatability with Foundation <1.20 where the plugins object was a simple array of plugin files.
				$registered_plugins[ $plugin ] = [
					'plugin_file'        => $plugin,
					'text_domain'        => CoreHelpers::get_plugin_data( $plugin )['TextDomain'],
					'foundation_version' => $this->get_plugin_foundation_version( $plugin ),
					'loads_foundation'   => false,
				];
			}
		}

		$registered_plugins = array_filter( $registered_plugins, 'is_array' );

		$this->_registered_plugins = $registered_plugins;
	}

	/**
	 * Initializes Foundation.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( did_action( 'gk/foundation/initialized' ) ) {
			return;
		}

		if ( CoreHelpers::is_wp_cli() ) {
			CLI::get_instance();
		}

		$this->_components = [
			'settings'        => SettingsFramework::get_instance(),
			'licenses'        => LicensesFramework::get_instance(),
			'translations'    => TranslationsFramework::get_instance(),
			'logger'          => LoggerFramework::get_instance(),
			'admin_menu'      => AdminMenu::get_instance(),
			'ajax_router'     => AjaxRouter::get_instance(),
			'rest_controller' => RESTController::get_instance(),
			'encryption'      => Encryption::get_instance(),
			'trustedlogin'    => TrustedLogin::get_instance(),
			'helpscout'       => HelpScout::get_instance(),
			'gravityforms'    => GravityForms::get_instance(),
		];

		foreach ( $this->_components as $component => $instance ) {
			if ( CoreHelpers::is_callable_class_method( [ $this->_components[ $component ], 'init' ] ) ) {
				$this->_components[ $component ]->init();
			}
		}

		self::$_request_unique_string = $this->encryption()->get_random_nonce();

		if ( is_admin() ) {
			$this->plugin_activation_handler()->fire_activation_hook();

			$this->configure_settings();

			add_action( 'admin_enqueue_scripts', [ $this, 'inline_scripts_and_styles' ], 20 );

			add_action( 'admin_footer', [ $this, 'show_loaded_by_message_on_admin_pages' ] );
		}

		class_alias( __CLASS__, 'GravityKitFoundation' );

		/**
		 * Fires when the class has finished initializing.
		 *
		 * @action gk/foundation/initialized
		 *
		 * @since  1.0.0
		 *
		 * @param $this
		 */
		do_action( 'gk/foundation/initialized', $this );
	}

	/**
	 * Configures general GravityKit settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function configure_settings() {
		add_filter(
			'gk/foundation/settings/data/plugins',
			function ( $plugins ) {
				$gk_settings = $this->settings()->get_plugin_settings( self::ID );

				// If multisite and not the main site, get default settings from the main site.
				// This allows site admins to configure the default settings for all subsites.
				// If no settings are found on the main site, default settings (set below) will be used.
				if ( ! is_main_site() && empty( $gk_settings ) ) {
					$gk_settings = $this->settings()->get_plugin_settings( self::ID, get_main_site_id() );
				}

				$default_settings = [
					'group_gk_products'     => 0,
					'top_level_menu_action' => $this->licenses()::ID,
					'support_email'         => get_bloginfo( 'admin_email' ),
					'support_port'          => 1,
					'no_conflict_mode'      => 1,
					'powered_by'            => 0,
					'beta'                  => 0,
				];

				$admin_menu_items = Arr::flatten( $this->admin_menu()->get_submenus(), 1 );

				$top_level_menu_action_choices = array_map( function ( $menu_item ) {
					if ( Arr::get( $menu_item, 'hide' ) || Arr::get( $menu_item, 'exclude_from_top_level_menu_action' ) ) {
						return;
					}

					return [
						'title' => $menu_item['menu_title'],
						'value' => $menu_item['id'],
					];
				}, $admin_menu_items );

				$top_level_menu_action_value = Arr::get( $gk_settings, 'top_level_menu_action' );
				$top_level_menu_action_value = in_array( $top_level_menu_action_value, Arr::flatten( $top_level_menu_action_choices ) ) ? $top_level_menu_action_value : $default_settings['top_level_menu_action'];

				$top_level_menu_action_choices = array_values( array_filter( $top_level_menu_action_choices ) );

				$general_settings = [];

				// TODO: This is a temporary notice. To be removed once GravityView is updated to v2.16.
				if ( defined( 'GV_PLUGIN_VERSION' ) && version_compare( GV_PLUGIN_VERSION, '2.16', '<' ) ) {
					$notice_1 = esc_html__( 'You are using a version of GravityView that does not yet support the new GravityKit settings framework.', 'gk-gravityimport' );

					$notice_2 = strtr(
						esc_html_x( 'As such, the settings below will not apply to GravityView pages and you will have to continue using the [link]old settings[/link] until an updated version of the plugin is available. We apologize for the inconvenience as we work to update our products in a timely fashion.', 'Placeholders inside [] are not to be translated.', 'gk-gravityimport' ),
						[
							'[link]'  => '<a href="' . admin_url( 'edit.php?post_type=gravityview&page=gravityview_settings' ) . '" class="text-blue-gv underline hover:text-gray-900 focus:text-gray-900 focus:no-underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">',
							'[/link]' => '</a>',
						]
					);

					$html = <<<HTML
<div class="bg-yellow-50 p-4 rounded-md">
	<div class="flex">
		<div class="flex-shrink-0">
			<svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
			</svg>
		</div>
		<div class="ml-3">
			<p class="text-sm">
				{$notice_1}
			</p>
			<br />
			<p class="text-sm">
				{$notice_2}
			</p>
		</div>
	</div>
</div>
HTML;

					$general_settings[] = [
						'id'   => 'legacy_settings_notice',
						'html' => $html,
					];
				}

				$general_settings = array_merge(
					$general_settings,
					[
						[
							'id'          => 'group_gk_products',
							'type'        => 'checkbox',
							'value'       => Arr::get( $gk_settings, 'group_gk_products', $default_settings['group_gk_products'] ),
							'choices'     => $top_level_menu_action_choices,
							'title'       => esc_html__( 'Group GravityKit Products', 'gk-gravityimport' ),
							'description' => esc_html__( 'Aggregate all GravityKit products into a single entry on the Plugins page for a cleaner view and easier management.', 'gk-gravityimport' ),
						],
						[
							'id'          => 'top_level_menu_action',
							'type'        => 'select',
							'value'       => $top_level_menu_action_value,
							'choices'     => $top_level_menu_action_choices,
							'title'       => esc_html__( 'GravityKit Menu Item Action', 'gk-gravityimport' ),
							'description' => esc_html__( 'Open the selected page when clicking the GravityKit menu item.', 'gk-gravityimport' ),
						],
						[
							'id'          => 'powered_by',
							'type'        => 'checkbox',
							'value'       => Arr::get( $gk_settings, 'powered_by', $default_settings['powered_by'] ),
							'title'       => esc_html__( 'Display "Powered By" Link', 'gk-gravityimport' ),
							'description' => esc_html__( 'A "Powered by GravityKit" link will be displayed below some GravityKit products. Help us spread the word!', 'gk-gravityimport' ),
						],
						[
							'id'          => 'affiliate_id',
							'type'        => 'number',
							'value'       => Arr::get( $gk_settings, 'affiliate_id' ),
							'title'       => esc_html__( 'Affiliate ID', 'gk-gravityimport' ),
							'description' => strtr(
								esc_html_x( 'Earn money when people clicking your links become GravityKit customers. [link]Register as an affiliate[/link]!', 'Placeholders inside [] are not to be translated.', 'gk-gravityimport' ),
								[
									'[link]'  => '<a href="https://www.gravitykit.com/account/affiliates/?utm_source=in-plugin&utm_medium=setting&utm_content=Register%20as%20an%20affiliate" class="underline" rel="external">',
									'[/link]' => '</a>',
								]
							),
							'requires'    => [
								'id'       => 'powered_by',
								'operator' => '=',
								'value'    => '1',
							],
						],
						[
							'id'          => 'beta',
							'type'        => 'checkbox',
							'value'       => Arr::get( $gk_settings, 'beta', $default_settings['beta'] ),
							'title'       => esc_html__( 'Become a Beta Tester', 'gk-gravityimport' ),
							'description' => esc_html__( 'You will have early access to the latest GravityKit products. There may be bugs! If you encounter an issue, report it to help make GravityKit products better!', 'gk-gravityimport' ),
						],
					]
				);

				$support_settings = [
					[
						'id'          => 'support_email',
						'type'        => 'text',
						'required'    => true,
						'value'       => Arr::get( $gk_settings, 'support_email', $default_settings['support_email'] ),
						'title'       => esc_html__( 'Support Email', 'gk-gravityimport' ),
						'description' => esc_html__( 'In order to provide responses to your support requests, please provide your email address.', 'gk-gravityimport' ),
						'validation'  => [
							[
								'rule'    => 'required',
								'message' => esc_html__( 'Support email is required', 'gk-gravityimport' ),
							],
							[
								'rule'    => 'email',
								'message' => esc_html__( 'Please provide a valid email address', 'gk-gravityimport' ),
							],
						],
					],
					[
						'id'          => 'support_port',
						'type'        => 'checkbox',
						'value'       => Arr::get( $gk_settings, 'support_port', $default_settings['support_port'] ),
						'title'       => esc_html__( 'Show Support Port', 'gk-gravityimport' ),
						'description' => ( esc_html__( 'The Support Port provides quick access to how-to articles and tutorials. For administrators, it also makes it easy to contact support.', 'gk-gravityimport' ) .
						                   strtr(
							                   esc_html_x( '[image]Support Port icon[/image]', 'Placeholders inside [] are not to be translated.', 'gk-gravityimport' ),
							                   [
								                   '[image]'  => '<div style="margin-top: 1em; width: 7em;">![',
								                   '[/image]' => '](' . CoreHelpers::get_assets_url( 'support-port-icon.jpg' ) . ')</div>',
							                   ]
						                   ) ),
						'markdown'    => true,
					],
				];

				$technical_settings = [
					[
						'id'          => 'no_conflict_mode',
						'type'        => 'checkbox',
						'value'       => Arr::get( $gk_settings, 'no_conflict_mode', $default_settings['no_conflict_mode'] ),
						'title'       => esc_html__( 'Enable No-Conflict Mode', 'gk-gravityimport' ),
						'description' => esc_html__( 'No-conflict mode prevents extraneous scripts and styles from being printed on GravityKit admin pages, reducing conflicts with other plugins and themes.', 'gk-gravityimport' ),
					],
				];

				$all_settings = [
					self::ID => [
						'id'       => self::ID,
						'title'    => 'GravityKit',
						'defaults' => $default_settings,
						'icon'     => CoreHelpers::get_assets_url( 'gravitykit-icon.png' ),
						'sections' => [
							[
								'title'    => esc_html__( 'General', 'gk-gravityimport' ),
								'settings' => $general_settings,
							],
							[
								'title'    => esc_html__( 'Support', 'gk-gravityimport' ),
								'settings' => $support_settings,
							],
							[
								'title'    => esc_html__( 'Technical', 'gk-gravityimport' ),
								'settings' => $technical_settings,
							],
						],
					],
				];

				/**
				 * Modifies the GravityKit general settings object.
				 *
				 * @filter gk/foundation/settings
				 *
				 * @since  1.0.0
				 *
				 * @param array $all_settings GravityKit general settings.
				 */
				$all_settings = apply_filters( 'gk/foundation/settings', $all_settings );

				return array_merge( $plugins, $all_settings );
			}
		);
	}

	/**
	 * Inlines scripts/styles.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function inline_scripts_and_styles() {
		/**
		 * Modifies scripts inlined by Foundation.
		 *
		 * @filter gk/foundation/inline-scripts
		 *
		 * @since  1.0.0
		 *
		 * @param array $inline_scripts Scripts inlined by Foundation.
		 */
		$inline_scripts = apply_filters( 'gk/foundation/inline-scripts', [] );

		if ( ! empty( $inline_scripts ) ) {
			$dependencies = [];
			$scripts      = [];

			foreach ( $inline_scripts as $script_data ) {
				if ( isset( $script_data['dependencies'] ) ) {
					$dependencies = array_merge( $dependencies, $script_data['dependencies'] );
				}

				if ( isset( $script_data['script'] ) ) {
					$scripts[] = $script_data['script'];
				}
			}

			wp_register_script( self::ID, false, $dependencies ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter,WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_script( self::ID );
			wp_add_inline_script( self::ID, implode( ' ', $scripts ) );
		}

		/**
		 * Modifies styles inlined by Foundation.
		 *
		 * @filter gk/foundation/inline-styles
		 *
		 * @since  1.0.0
		 *
		 * @param array $inline_styles Styles inlined by Foundation.
		 */
		$inline_styles = apply_filters( 'gk/foundation/inline-styles', [] );

		if ( ! empty( $inline_styles ) ) {
			$dependencies = [];
			$styles       = [];

			foreach ( $inline_styles as $style_data ) {
				if ( isset( $style_data['dependencies'] ) ) {
					$dependencies = array_merge( $dependencies, $style_data['dependencies'] );
				}

				if ( isset( $style_data['style'] ) ) {
					$styles[] = $style_data['style'];
				}
			}

			wp_register_style( self::ID, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( self::ID );
			wp_add_inline_style( self::ID, implode( ' ', $styles ) );
		}
	}

	/**
	 * Magic method to get private class instances.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name      Component/class name.
	 * @param array  $arguments Optional and not used.
	 *
	 * @return mixed
	 */
	public function __call( $name, array $arguments = [] ) {
		if ( 'plugin_activation_handler' === $name ) {
			return $this->_plugin_activation_handler;
		}

		if ( 'helpers' === $name ) {
			return (object) [
				'core'  => new CoreHelpers(),
				'array' => new Arr(),
			];
		}

		// Ajax logic was moved to a GravityKit\Foundation\WP\AjaxRouter in 1.0.11
		// TODO: remove when other plugins are updated not to use GravityKitFoundation::get_ajax_params().
		if ( 'get_ajax_params' === $name ) {
			return $this->_components['ajax_router']->get_ajax_params( Arr::get( $arguments, 0, '' ) );
		}

		if ( ! isset( $this->_components[ $name ] ) ) {
			return;
		}

		switch ( $name ) {
			case 'logger':
				$logger_name  = isset( $arguments[0] ) ? $arguments[0] : null;
				$logger_title = isset( $arguments[1] ) ? $arguments[1] : null;

				return call_user_func_array( [ $this->_components[ $name ], 'get_instance' ], [ $logger_name, $logger_title ] );
			default:
				return $this->_components[ $name ];
		}
	}

	/**
	 * Magic method to get private class instances as static methods.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name      Component/class name.
	 * @param array  $arguments Optional and not used.
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, array $arguments = [] ) {
		$instance = apply_filters( 'gk/foundation/get-instance', null );

		return call_user_func_array( [ $instance, $name ], $arguments );
	}

	/**
	 * Returns a unique value that was generated for this request.
	 * This value can be used, among other purposes, as a random initialization vector for encryption operations performed during the request (e.g., encrypting a license key in various places will result in the same encrypted value).
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_request_unique_string() {
		return self::$_request_unique_string;
	}

	/**
	 * Outputs an HTML comment with the Foundation version and the plugin that loaded it in admin pages.
	 *
	 * @since 1.0.1
	 * @since 1.2.0 Renamed to 'show_loaded_by_message_on_admin_pages'.
	 *
	 * @return void
	 */
	public function show_loaded_by_message_on_admin_pages() {
		$foundation_information = $this->get_foundation_information();

		if ( ! $foundation_information['show_loaded_by_message'] ) {
			return;
		}

		printf( '<!-- %s -->', $foundation_information['loaded_by_message'] );
	}

	/**
	 * Returns information about Foundation (version, plugin that loaded it, etc.).
	 *
	 * @since 1.2.0
	 *
	 * @return array{version: string, source: array, registered_plugins: array, loaded_by_foundation_message: string, display_loaded_by_foundation_message: bool}
	 */
	public function get_foundation_information(): array {
		$foundation_source = Arr::first( $this->_registered_plugins, function ( $plugin ) {
			return $plugin['loads_foundation'];
		} );

		$version            = $foundation_source['foundation_version'];
		$source_plugin      = CoreHelpers::get_plugin_data( $foundation_source['plugin_file'] );
		$source_plugin_name = $source_plugin['Name'] ?? __( 'Unknown Plugin', 'gk-gravityimport' );
		$loaded_by_message  = strtr(
			_x( 'GravityKit Foundation [version] (loaded by [plugin]).', 'Placeholders inside [] are not to be translated.', 'gk-gravityimport' ),
			[
				'[version]' => $version,
				'[plugin]'  => $source_plugin_name,
			]
		);

		/**
		 * Controls whether to include "GravityKit Foundation X (loaded by Y)" HTML comment in admin pages.
		 *
		 * @filter gk/foundation/show-loaded-by-message
		 *
		 * @since  1.2.0
		 *
		 * @param bool $show_loaded_by_message Whether to display the information.
		 */
		$show_loaded_by_message = apply_filters( 'gk/foundation/show-loaded-by-message', true );

		return [
			'version'                => $version,
			'source_plugin'          => $source_plugin,
			'registered_plugins'     => $this->_registered_plugins,
			'loaded_by_message'      => $loaded_by_message,
			'show_loaded_by_message' => $show_loaded_by_message,
		];
	}

	/**
	 * Gets the Foundation version included with a plugin.
	 *
	 * @since 1.2.0
	 *
	 * @param string $plugin_file Absolute path to the plugin file.
	 *
	 * @return string|null
	 */
	public function get_plugin_foundation_version( $plugin_file ) {
		// Try to get the version first from the registered plugins.
		$plugin = Arr::first( $this->_registered_plugins, function ( $plugin ) use ( $plugin_file ) {
			return $plugin['plugin_file'] === $plugin_file;
		} );

		// If the plugin is not registered, try to get the version from the plugin file.
		if ( ! isset( $plugin['foundation_version'] ) ) {
			$foundation_core = sprintf( '%s/vendor_prefixed/gravitykit/foundation/src/Core.php', dirname( $plugin_file ) );

			if ( ! file_exists( $foundation_core ) ) {
				$foundation_core = sprintf( '%s/vendor/gravitykit/foundation/src/Core.php', dirname( $plugin_file ) );

				if ( ! file_exists( $foundation_core ) ) {
					return null;
				}
			}

			return ( preg_match( "/const version = '([^']+)';/i", file_get_contents( $foundation_core ), $matches ) ) ? $matches[1] : null;

		}

		return $plugin['foundation_version'] ?? null;
	}

	/**
	 * Returns the latest Foundation version from all registered plugins.
	 *
	 * @since 1.2.0
	 *
	 * @param string|null $text_domain_to_exclude Text domain to exclude from the search.
	 *
	 * @return string
	 */
	public function get_latest_foundation_version_from_registered_plugins( $text_domain_to_exclude = null ) {
		$registered_plugins = $this->_registered_plugins;

		if ( $text_domain_to_exclude ) {
			$registered_plugins = array_filter( $this->_registered_plugins, function ( $plugin ) use ( $text_domain_to_exclude ) {
				return $plugin['text_domain'] !== $text_domain_to_exclude;
			} );
		}

		return max( array_map( function ( $plugin ) {
			return $plugin['foundation_version'] ?? 0;
		}, $registered_plugins ) ) ?: '';
	}
}
