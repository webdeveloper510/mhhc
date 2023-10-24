<?php
namespace GravityKit\GravityMaps\Views;

use GravityKit\GravityMaps\AbstractSingleton;
use GravityKit\GravityMaps\Admin;
use GravityKit\GravityMaps\Foundation\Helpers\Arr;

/**
 * Class CanvasPositionModifier
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Views
 */
class CanvasPositionModifier extends AbstractSingleton {
	/**
	 * @inheritDoc
	 */
	protected function register(): void {
		$this->hooks();
	}

	/**
	 * Configures hooks.
	 *
	 * @since 3.0
	 */
	protected function hooks(): void {
		$this->add_filters();
		$this->add_actions();
	}

	/**
	 * Adds filters.
	 *
	 * @since 3.0
	 */
	protected function add_filters(): void {
	}

	/**
	 * Adds actions.
	 *
	 * @since 3.0
	 */
	protected function add_actions(): void {
		add_action( 'gravityview/template/map/body/before', [ $this, 'render_layout' ], 10, 1 );
		add_action( 'gravityview/template/map/body/after', [ $this, 'render_layout' ], 10, 1 );
	}

	/**
	 * Attaches the map layout to fit around the canvas position selected in the settings.
	 *
	 * Possible Templates loaded:
	 * - views/map/map-part-bottom-after.php
	 * - views/map/map-part-bottom-before.php
	 * - views/map/map-part-left-after.php
	 * - views/map/map-part-left-before.php
	 * - views/map/map-part-right-after.php
	 * - views/map/map-part-right-before.php
	 * - views/map/map-part-top-after.php
	 * - views/map/map-part-top-before.php
	 *
	 * @since 3.0
	 *
	 * @param \GV\Context $context The view context.
	 */
	public function render_layout( $context ): void {
		// When dealing with the old layouts just bail, we don't need to do anything.
		if ( ! $context instanceof \GV\Context ) {
			return;
		}

		// Don't show the map widget if we're doing "Hide data until search"
		if ( $context->view->settings->get( 'hide_until_searched' ) && ! $context->request->is_search() ) {
			return;
		}

		$view = $context->view->get_post();

		if ( ! $view ) {
			return;
		}

		// before or after entries
		$zone = str_replace( 'gravityview/template/map/body/', '', current_filter() );

		$settings = Admin::get_map_settings( $view->ID );

		// map position layout (top, right, left or bottom)
		$pos = Arr::get( $settings, 'map_canvas_position', 'top' );

		// render template
		$context->template->get_template_part( 'map/map-part', $pos . '-' . $zone );
	}
}