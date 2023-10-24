<?php

namespace GravityKit\GravityMaps\Views;

use GravityKit\GravityMaps\AbstractSingleton;
use GravityKit\GravityMaps\Loader;
use GravityKit\GravityMaps\Template_Map_Default;
use GravityKit\GravityMaps\Template_Preset_Business_Map;
use GV\Request;
use GV\View;

/**
 * Class Controller
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Views
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
		CanvasPositionModifier::instance();
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
		add_filter( 'gravityview/template/view/class', [ $this, 'filter_modify_view_class' ], 15, 3 );
		add_filter( 'gravityview_template_paths', [ $this, 'add_template_path' ] );
		add_filter( 'gravityview/template/fields_template_paths', [ $this, 'add_template_path' ] );
	}

	/**
	 * Adds actions.
	 *
	 * @since 3.0
	 */
	protected function add_actions(): void {
		// Register the Maps View template type to GV core.
		add_action( 'init', [ $this, 'register_templates' ], 20 );
	}

	/**
	 * Modifies the View Class to use the Map Class.
	 *
	 * @since 3.0
	 *
	 * @param string  $class   The chosen class - Default: {@see \GV\View_Table_Template}
	 * @param View    $view    The view about to be rendered.
	 * @param Request $request The associated request.
	 *
	 * @return string
	 */
	public function filter_modify_view_class( $class, $view, $request ): string {
		if ( Map::$slug !== $view->settings->get( 'template' ) ) {
			return $class;
		}

		return Map::class;
	}

	/**
	 * Include this extension templates path.
	 *
	 * @since 3.0
	 *
	 * @param array $file_paths List of template paths ordered
	 *
	 * @return array
	 */
	public function add_template_path( $file_paths ): array {
		// Index 100 is the default GravityView template path.
		$file_paths[133] = plugin_dir_path( Loader::instance()->path ) . 'templates';

		return $file_paths;
	}

	/**
	 * Register the Maps View template type to GV core
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function register_templates(): void {
		// When GravityView is enabled but not active due to version mismatch, the class will not exist.
		if ( ! class_exists( '\GravityKit\GravityMaps\Template_Map_Default' ) ) {
			return;
		}

		new Template_Map_Default;
		new Template_Preset_Business_Map;
	}

}