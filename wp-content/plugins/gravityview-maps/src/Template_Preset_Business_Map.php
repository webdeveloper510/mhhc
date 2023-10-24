<?php

namespace GravityKit\GravityMaps;

// When GravityView is enabled but not active due to version mismatch, the class will not exist.
if ( ! class_exists( '\GravityKit\GravityMaps\Template_Map_Default' ) ) {
	return;
}

/**
 * Defines default (list) template for the Business Map View
 */
class Template_Preset_Business_Map extends Template_Map_Default {
	function __construct() {
		/**
		 * @global Loader $gravityview_maps
		 */
		global $gravityview_maps;

		$id = 'map';

		$settings = array(
			'slug'          => 'map',
			'type'          => 'preset',
			'label'         => __( 'Business Map Listing', 'gk-gravitymaps' ),
			'description'   => __( 'Display business profiles pinned in a map.', 'gk-gravitymaps' ),
			'logo'          => plugins_url( 'src/presets/business-map/logo-business-map.png', $gravityview_maps->plugin_file ),
			'preview'       => 'https://demo.gravitykit.com/blog/view/business-map/',
			'preset_form'   => $gravityview_maps->dir . 'src/presets/business-map/form-business-map.xml',
			'preset_fields' => $gravityview_maps->dir . 'src/presets/business-map/fields-business-map.xml'
		);

		parent::__construct( $id, $settings );
	}
}
