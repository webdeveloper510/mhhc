<?php

namespace GravityKit\GravityMaps;

use GravityKit\GravityMaps\Views\Map;
use GV\Widget;

/**
 * Widget to display page links
 */
class View_Widget extends Widget {
	public $icon = 'dashicons-location-alt';

	protected $show_on_single = false;

	function __construct() {
		$this->widget_description = __( 'Display the visible entries in a map', 'gk-gravitymaps' );

		$default_values = array( 'header' => 1, 'footer' => 1 );

		$settings = array();

		parent::__construct( __( 'Multiple Entries Map', 'gk-gravitymaps' ), 'map', $default_values, $settings );
	}

	/**
	 * Determine the widget's availability for registering.
	 * Only available if the current view is not a map.
	 *
	 * @since 3.0.1
	 *
	 * @param array $widgets
	 *
	 * @return array
	 */
	public function register_widget( $widgets ) {
		$view_id = $_GET['post'] ?? null;
		$parent_widgets = parent::register_widget( $widgets );

		if ( ! $view_id ) {
			return $parent_widgets;
		}

		$view_type = gravityview_get_template_id( $view_id );

		if ( 'map' !== $view_type ) {
			return $parent_widgets;
		}

		// Return without the map widget.
		return $widgets;
	}

	public function pre_render_frontend( $context = '' ) {
		global $post;

		$view_type = null;
		if ( $post ) {
			$view_type = gravityview_get_template_id( $post->ID );
		}

		if ( 'datatables_table' === $view_type ) {
			do_action( 'gravityview_log_error', 'Map not shown: the map widget does not currently work with the DataTables layout' );

			return false;
		}

		if ( empty( $context ) ) {
			return parent::pre_render_frontend( $context );
		}

		if ( 'map' === $view_type ) {
			do_action( 'gravityview_log_error', 'Map not shown: the map widget does not currently work with the Map layout' );

			return false;
		}
		return parent::pre_render_frontend( $context );
	}

	/**
	 * Trigger the `gravityview_map_render_div` action to render the map widget container
	 *
	 * Don't render the map if using DataTables layout
	 *
	 * @see Render_Map::render_map_div
	 *
	 * @param                             $widget_args
	 * @param string                      $content
	 * @param string|\GV\Template_Context $context
	 *
	 * @return void
	 */
	public function render_frontend( $widget_args, $content = '', $context = '' ) {
		if( ! $this->pre_render_frontend( $context ) ) {
			return;
		}

		Map::render_map_canvas( $context );
	}
}
