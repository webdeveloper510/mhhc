<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\WP;

use GravityKit\GravityImport\Foundation\Core;
use GravityKit\GravityImport\Foundation\Helpers\Arr;
use GravityKit\GravityImport\Foundation\Settings\Framework as SettingsFramework;

/**
 * This class is responsible for adding a GravityKit menu and submenu items to the WP admin panel.
 */
class AdminMenu {
	const WP_ADMIN_MENU_SLUG = '_gk_admin_menu';

	/**
	 * @since 1.0.0
	 *
	 * @var AdminMenu Class instance.
	 */
	private static $_instance;

	/**
	 * @since 1.0.0
	 *
	 * @var array Submenus of the top menu.
	 */
	private static $_submenus = [
		'top'    => [],
		'center' => [],
		'bottom' => [],
	];

	/**
	 * Returns class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return AdminMenu
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initializes the class.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		static $initialized;

		if ( $initialized ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'network_admin_menu', [ $this, 'add_admin_menu' ] );

		$initialized = true;
	}

	/**
	 * Configures GravityKit top-level menu and submenu items in WP admin.
	 *
	 * @since 1.0.0
	 *
	 * @global array $menu
	 * @global array $submenu
	 *
	 * @retun void
	 */
	public function add_admin_menu() {
		global $menu, $submenu;

		// Make sure we're not adding a duplicate top-level menu.
		if ( strpos( json_encode( $menu ?: [] ), self::WP_ADMIN_MENU_SLUG ) !== false ) {
			return;
		}

		$_get_divider = function () {
			// Divider is added to the menu title; because WP wraps it in <a>, we need to first close the tag, then add the divider.
			return '</a> <hr style="margin: 10px 12px; border: none; height: 1px; background-color: hsla( 0, 0%, 100%, .2 );" tabindex="-1" />';
		};

		$total_badge_count         = 0;
		$submenus                  = self::get_submenus();
		$filtered_submenus         = [];
		$user_first_met_capability = false;

		// Filter submenus by removing those for which the user doesn't have the required capability.
		foreach ( $submenus as $submenu_data ) {
			if ( empty( $submenu_data ) ) {
				continue;
			}

			foreach ( array_values( $submenu_data ) as $index => $submenu_item ) {
				if ( ! current_user_can( $submenu_item['capability'] ) ) {
					continue;
				} elseif ( ! $user_first_met_capability ) {
					$user_first_met_capability = $submenu_item['capability'];
				}

				/**
				 * @filter `gk/foundation/admin-menu/submenu/{$submenu_id}/counter` Displays counter next to the submenu title.
				 *
				 * @since  1.0.0
				 *
				 * @param int $badge_count
				 */
				$badge_count = (int) apply_filters( "gk/foundation/admin-menu/submenu/{$submenu_item['id']}/counter", 0 );

				if ( Arr::get( $submenu, 'hide' ) ) {
					$badge_count = 0;
				}

				if ( $badge_count > 0 ) {
					$total_badge_count += $badge_count;
				}

				$submenu_item_id = Arr::get( $submenu_item, 'id' );

				$filtered_submenu = [
					'id'                 => $submenu_item_id,
					'slug'               => self::WP_ADMIN_MENU_SLUG,
					'page_title'         => Arr::get( $submenu_item, 'page_title' ),
					'menu_title'         => Arr::get( $submenu_item, 'menu_title' ) . $this->get_badge_counter_markup( $submenu_item_id, $badge_count ),
					'capability'         => Arr::get( $submenu_item, 'capability' ),
					'callback'           => Arr::get( $submenu_item, 'callback' ),
					'hide'               => Arr::get( $submenu_item, 'hide' ),
					'hide_admin_notices' => Arr::get( $submenu_item, 'hide_admin_notices' ),
				];

				if ( $index === count( $submenu_data ) - 1 ) {
					$filtered_submenu['divider'] = $_get_divider();
				}

				$filtered_submenus[] = $filtered_submenu;
			}
		}

		if ( empty( $filtered_submenus ) ) {
			return;
		}

		// Add top-level menu.
		$page_title = esc_html__( 'GravityKit', 'gk-gravityimport' );
		$menu_title = esc_html__( 'GravityKit', 'gk-gravityimport' );

		/**
		 * Controls the position of the top-level GravityKit admin menu.
		 *
		 * @filter gk/foundation/admin-menu/position
		 *
		 * @since  1.0.0
		 *
		 * @param float $menu_position Default: value of `gform_menu_position` filter +  0.001.
		 */
		$menu_position = apply_filters( 'gk/foundation/admin-menu/position', (float) apply_filters( 'gform_menu_position', '16.9' ) + .0001 );

		add_menu_page(
			$page_title,
			$menu_title,
			$user_first_met_capability, // Use the first submenu capability that the user has met to display the main GravityKit menu.
			self::WP_ADMIN_MENU_SLUG,
			null,
			'data:image/svg+xml;base64,' . base64_encode( '<svg id="Artwork" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path fill="#a7aaad" class="st0" d="M128 0C57.3 0 0 57.3 0 128s57.3 128 128 128 128-57.3 128-128S198.7 0 128 0zm0 243.2c-63.6 0-115.2-51.6-115.2-115.2S64.4 12.8 128 12.8 243.2 64.4 243.2 128 191.6 243.2 128 243.2zm7.9-172.5c-.8.1-1.4-.5-1.5-1.3V57.7c-.1-.9.4-1.8 1.3-2.1 7.8-4.2 10.6-13.9 6.4-21.7-4.2-7.8-13.9-10.6-21.7-6.4-7.8 4.2-10.6 13.9-6.4 21.7 1.5 2.7 3.7 4.9 6.4 6.4.8.3 1.4 1.2 1.3 2.1v11.4c.1.8-.4 1.5-1.2 1.6h-.3c-41 3-68.9 29.6-68.9 66.9 0 39.6 31.5 67.2 76.8 67.2s76.8-27.6 76.8-67.2c-.1-37.3-28-63.9-69-66.9zM128 182.4c-35.9 0-60.8-18.4-60.8-44.8S92.1 92.8 128 92.8s60.8 18.4 60.8 44.8-24.9 44.8-60.8 44.8zm53.8-44.8c0 22.3-22.1 37.8-53.8 37.8-5.1 0-10.2-.4-15.2-1.3-6.8-1.2-9.4-3.2-12-9.6-3.1-7.5-4.8-16.6-4.8-26.9s1.7-19.4 4.8-26.9c2.7-6.4 5.2-8.4 12-9.6 5-.9 10.1-1.3 15.2-1.3 31.7 0 53.8 15.5 53.8 37.8z"/></svg>' ),
			$menu_position
		);

		/**
		 * @filter `gk/foundation/admin-menu/counter` Displays counter next to the top-menu title.
		 *
		 * @since  1.0.0
		 *
		 * @param int $total_badge_count
		 */
		$total_badge_count = (int) apply_filters( 'gk/foundation/admin-menu/counter', $total_badge_count );

		foreach ( $menu as &$menu_item ) {
			if ( $menu_item[2] === self::WP_ADMIN_MENU_SLUG ) {
				$menu_item[0] .= $this->get_badge_counter_markup( self::WP_ADMIN_MENU_SLUG, $total_badge_count );
			}
		}

		$top_level_menu_action = SettingsFramework::get_instance()->get_plugin_setting( Core::ID, 'top_level_menu_action', 'submenu' );

		$top_level_menu_action_submenu = Arr::first( $filtered_submenus, function ( $submenu ) use ( $top_level_menu_action ) {
			return $submenu['id'] === $top_level_menu_action;
		} );

		if ( $top_level_menu_action_submenu && $top_level_menu_action !== Arr::get( $filtered_submenus, '0.id' ) ) {
			// Add and hide a first submenu item that will be used as an action for the top-level menu GravityKit menu.
			// An alternative is to use the `parent_file` filter, but that would still show the first submenu item's ID in the URL.
			add_submenu_page(
				self::WP_ADMIN_MENU_SLUG,
				'',
				'',
				$user_first_met_capability,
				$top_level_menu_action
			);

			add_filter( 'gk/foundation/inline-styles', function ( $styles ) {
				$styles[] = [
					'style' => '#toplevel_page_' . self::WP_ADMIN_MENU_SLUG . ' ul.wp-submenu li:nth-child(2) {
						display: none;
					}',
				];

				return $styles;
			} );

			// When the user hovers over the top-level menu item, replace the menu title with the first submenu item's title.
			add_filter( 'gk/foundation/inline-scripts', function ( $styles ) use ( $top_level_menu_action ) {
				$styles[] = [
					'script' => <<<JS
document.addEventListener( 'DOMContentLoaded', () => {
	const menuLinkEl = document.querySelector( 'a.toplevel_page__gk_admin_menu' );
	const menuNameEl = menuLinkEl.querySelector( 'div.wp-menu-name' );
	const menuActionEl = document.querySelector( '#${top_level_menu_action}-badge' )?.parentNode;

	if ( !menuLinkEl || !menuNameEl || !menuActionEl ) {
		return;
	}

	menuNameEl.dataset.originalContent = menuNameEl.innerHTML;

	const restoreOriginalContent = () => {
		menuNameEl.innerHTML = menuNameEl.dataset.originalContent;
	};

	const showMenuActionContent = () => {
		menuNameEl.innerHTML = menuActionEl.innerHTML;
	};

	menuLinkEl.addEventListener( 'mouseover', showMenuActionContent );
	menuLinkEl.addEventListener( 'mouseout', restoreOriginalContent );
	menuLinkEl.addEventListener( 'focus', showMenuActionContent );
	menuLinkEl.addEventListener( 'blur', restoreOriginalContent );
} );
JS
				];

				return $styles;
			} );
		}

		// Add submenus.
		foreach ( $filtered_submenus as $index => $filtered_submenu ) {
			add_submenu_page(
				$filtered_submenu['slug'],
				$filtered_submenu['page_title'],
				$filtered_submenu['menu_title'],
				$filtered_submenu['capability'],
				$filtered_submenu['id'],
				$filtered_submenu['callback']
			);

			if ( isset( $filtered_submenu['hide_admin_notices'] ) ) {
				add_action( 'in_admin_header', function () use ( $filtered_submenu ) {
					if ( $filtered_submenu['id'] !== Arr::get( $_REQUEST, 'page' ) ) {
						return;
					}

					remove_all_actions( 'user_admin_notices' );
					remove_all_actions( 'admin_notices' );
				}, 999 );
			}

			// Add divider unless it's the last submenu item that we've added.
			if ( ! isset( $filtered_submenu['divider'] ) || $index === count( $filtered_submenus ) - 1 ) {
				continue;
			}

			$added_submenu_to_update    = array_pop( $submenu[ self::WP_ADMIN_MENU_SLUG ] );
			$added_submenu_to_update[0] .= $filtered_submenu['divider'];

			$submenu[ self::WP_ADMIN_MENU_SLUG ][] = $added_submenu_to_update;
		}

		// On a multisite the first submenu item equals the top-level menu.
		// Let's indiscriminately remove all submenu items that have the top-level menu's slug.
		foreach ( $submenu[ self::WP_ADMIN_MENU_SLUG ] as $key => $submenu_item ) {
			if ( $submenu_item[2] === self::WP_ADMIN_MENU_SLUG ) {
				unset( $submenu[ self::WP_ADMIN_MENU_SLUG ][ $key ] );
			}
		}

		add_filter( 'gk/foundation/inline-styles', function ( $styles ) use ( $filtered_submenus ) {
			// Top-level menu item SVG icon style.
			$styles[] = [
				'style' => '#toplevel_page_' . self::WP_ADMIN_MENU_SLUG . ' div.wp-menu-image.svg { background-size: 1.5em auto; }',
			];

			// Styles for submenus that should be hidden.
			$hide_styles = [];

			foreach ( $filtered_submenus as $submenu ) {
				if ( isset( $submenu['top_level_menu_action'] ) ) {
					$hide_styles[] = '#toplevel_page_' . self::WP_ADMIN_MENU_SLUG . ' ul.wp-submenu li:nth-child(2)';
				}

				if ( $submenu['hide'] ) {
					$hide_styles[] = '#toplevel_page_' . self::WP_ADMIN_MENU_SLUG . ' ul.wp-submenu li a[href*="' . $submenu['id'] . '"]';
				}
			}

			if ( empty( $hide_styles ) ) {
				return $styles;
			}

			$styles[] = [ 'style' => join( ',', $hide_styles ) . ' { display: none !important; }' ];

			return $styles;
		} );
	}

	/**
	 * Adds a submenu to the GravityKit top-level menu in WP admin.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $submenu  The submenu data.
	 * @param string $position The position of the submenu. Default: 'top'.
	 *
	 * @retun void
	 */
	static function add_submenu_item( $submenu, $position = 'top' ) {
		if ( ! isset( $submenu['id'] ) ) {
			return;
		}

		$submenus = self::get_submenus();

		$submenus[ $position ] = Arr::get( $submenus, $position, [] );

		if ( ! isset( $submenu['order'] ) ) {
			$order = array_column( $submenus[ $position ], 'order' );

			if ( empty( $order ) ) {
				$submenu['order'] = 1;
			} else {
				$submenu['order'] = max( $order ) + 100;
			}
		}

		$submenus[ $position ][ $submenu['id'] ] = $submenu;

		$order = array_column( $submenus[ $position ], 'order' );

		array_multisort( $submenus[ $position ], SORT_NUMERIC, $order );

		self::$_submenus = $submenus;
	}

	/**
	 * Returns submenus optionally modified by a filter.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	static function get_submenus() {
		/**
		 * @filter `gk/foundation/admin-menu/submenus` Modifies the submenus object.
		 *
		 * @since  1.0.0
		 *
		 * @param array $submenus Submenus.
		 */
		return apply_filters( 'gk/foundation/admin-menu/submenus', self::$_submenus );
	}

	/**
	 * Removes a submenu from the GravityKit top-level menu in WP admin and if the top-level menu is empty, removes it as well.
	 *
	 * @since 1.0.0
	 *
	 * @global array $submenu
	 *
	 * @retun void
	 */
	static function remove_submenu_item( $id ) {
		global $submenu;

		if ( ! isset( $submenu[ self::WP_ADMIN_MENU_SLUG ] ) ) {
			return;
		}

		foreach ( $submenu[ self::WP_ADMIN_MENU_SLUG ] as $index => $submenu_item ) {
			if ( $submenu_item[2] === $id ) {
				unset( $submenu[ self::WP_ADMIN_MENU_SLUG ][ $index ] );
			}
		}

		if ( ! empty( $submenu[ self::WP_ADMIN_MENU_SLUG ] ) ) {
			return;
		}

		self::remove_admin_menu();
	}

	/**
	 * Removes the GravityKit top-level menu from WP admin.
	 *
	 * @since 1.0.0
	 *
	 * @global array $menu
	 *
	 * @retun void
	 */
	static function remove_admin_menu() {
		global $menu;

		foreach ( $menu as $index => $menu_item ) {
			if ( $menu_item[2] === self::WP_ADMIN_MENU_SLUG ) {
				unset( $menu[ $index ] );
			}
		}
	}

	/**
	 * Returns the markup for the badge counter.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $menu_id
	 * @param int|string $badge_count
	 *
	 * @return string
	 */
	public function get_badge_counter_markup( $menu_id, $badge_count ) {
		$badge_count = (int) $badge_count;

		return '<span id="' . $menu_id . '-badge" style="margin-left: 5px;" class="update-plugins count-' . $badge_count . '"><span class="plugin-count">' . number_format_i18n( $badge_count ) . '</span></span>';
	}
}
