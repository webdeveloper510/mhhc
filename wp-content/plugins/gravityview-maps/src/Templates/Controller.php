<?php

namespace GravityKit\GravityMaps\Templates;

use GravityKit\GravityMaps\AbstractSingleton;
use GravityKit\GravityMaps\Views\Map;
use GV\View;

/**
 * Class Controller
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Templates
 */
class Controller extends AbstractSingleton {
	/**
	 * @inheritDoc
	 */
	protected function register(): void {
		$this->hooks();
		$this->boot();
	}

	/**
	 * Boots related classes that need to be initialized.
	 *
	 * @since 3.0
	 */
	protected function boot(): void {
		Modifiers\DirectoryMapWithoutFields::instance();
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
		add_filter( 'get_post_metadata', [ $this, 'filter_template_type_preset' ], 10, 4 );
	}

	/**
	 * Adds actions.
	 *
	 * @since 3.0
	 */
	protected function add_actions(): void {
	}

	/**
	 * Filters the values of the `_gravityview_directory_template` meta key, and only when it has the value of
	 * `preset_business_map`, it will return the slug of the Map view.
	 *
	 * This is here mostly for backwards compatibility with the old preset maps.
	 *
	 * @since 3.0.1
	 *
	 * @param mixed  $value     Which value to return.
	 * @param int    $object_id Post ID.
	 * @param string $meta_key  Meta key.
	 * @param bool   $single    Whether to return only the first value of the specified $meta_key.
	 *
	 * @return string
	 */
	public function filter_template_type_preset( $value, $object_id, $meta_key, $single ) {
		if ( null !== $value ) {
			return $value;
		}

		if ( '_gravityview_directory_template' !== $meta_key ) {
			return $value;
		}

		// Remove the filter to ensure it's not a recursive call.
		remove_filter( 'get_post_metadata', [ $this, 'filter_template_type_preset' ] );

		$actual_value = get_post_meta( $object_id, $meta_key, true );

		// Add the filter after getting the actual value.
		add_filter( 'get_post_metadata', [ $this, 'filter_template_type_preset' ], 10, 4 );

		// Bail if the actual value is not `preset_business_map`.
		if ( 'preset_business_map' !== $actual_value ) {
			return $value;
		}

		return Map::$slug;
	}
}