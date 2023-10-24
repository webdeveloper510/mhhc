<?php

namespace GravityKit\GravityMaps\Templates\Modifiers;

use GravityKit\GravityMaps\AbstractSingleton;
use GravityKit\GravityMaps\Render_Map;

/**
 * Class Directory Map Without Fields.
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Templates\Modifiers
 */
class DirectoryMapWithoutFields extends AbstractSingleton {
	/**
	 * @inheritDoc
	 */
	public function register(): void {
		add_filter( 'gk/gravityview/renderer/should-display-configuration-notice', [ $this, 'should_display_configuration_notice' ], 10, 3 );
	}

	/**
	 * Includes a CSS class to hide the individual empty entries on the list/table/map views.
	 *
	 * @since 3.0
	 *
	 * @param string $passed_css_class
	 *
	 * @return string
	 */
	public function include_hide_individual_entries_class( $passed_css_class = '' ): string {
		$passed_css_class .= ' gk-hide-individual-entries';

		return $passed_css_class;
	}

	/**
	 * Checks if we should display the configuration notice.
	 *
	 * @since 3.0
	 *
	 * @param bool                 $display     Whether to display the notice. Default: true.
	 * @param \GV\Template_Context $gravityview The $gravityview template object.
	 * @param string               $context     The context of the notice. Possible values: `directory`, `single`, `edit`.
	 *
	 * @return bool
	 */
	public function should_display_configuration_notice( $display, $gravityview, $context ): bool {
		// This prevents shortcodes later having problems.
		remove_filter( 'gravityview/render/container/class', [ $this, 'include_hide_individual_entries_class' ], 15 );

		if ( ! $display ) {
			return $display;
		}

		if ( 'directory' !== $context ) {
			return $display;
		}

		if (
			'map' !== $gravityview->view->settings->get( 'template_id' )
			&& ! Render_Map::get_instance()->has_maps()
		) {
			return $display;
		}

		add_filter( 'gravityview/render/container/class', [ $this, 'include_hide_individual_entries_class' ], 15, 3 );

		return false;
	}
}