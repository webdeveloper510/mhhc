<?php

namespace GravityKit\GravityMaps\Views;

use GravityView_View;
use GV\Entry;
use GV\Entry_Collection;
use GV\Field;
use GV\Mocks\Legacy_Context;
use GV\Request;
use GV\View;
use GV\View_Template;
use GV\GF_Form;
use GV\Internal_Source;
use GV\Internal_Field;
use GV\Template_Context;
use GV\Field_Renderer;
use GV\Utils;

/**
 * Class Map
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Views
 */
class Map extends View_Template {
	/**
	 * @since 3.0
	 *
	 * @var string The template slug to be loaded (like "map")
	 */
	public static $slug = 'map';

	/**
	 * Class constructor.
	 *
	 * @since 3.0
	 *
	 * @param View             $view
	 * @param Entry_Collection $entries
	 * @param Request          $request
	 */
	public function __construct( View $view, Entry_Collection $entries, Request $request ) {
		parent::__construct( $view, $entries, $request );

		$this->plugin_directory = plugin_dir_path( GRAVITYVIEW_MAPS_FILE );
	}

	/**
	 * Outputs the field.
	 *
	 * @since 3.0
	 *
	 * @param Field $field  The field to output.
	 * @param Entry $entry  The entry.
	 * @param array $extras Extra stuff, like autop, etc.
	 *
	 * @return string|void
	 */
	public function the_field( Field $field, Entry $entry, $extras = null ) {
		$form = $this->view->form;

		if ( isset( $this->view->unions[ $entry['form_id'] ] ) ) {
			if ( isset( $this->view->unions[ $entry['form_id'] ][ $field->ID ] ) ) {
				$field = $this->view->unions[ $entry['form_id'] ][ $field->ID ];
			} elseif ( ! $field instanceof Internal_Field ) {
				$field = Internal_Field::from_configuration( [ 'id' => 'custom' ] );
			}
		}

		if ( $entry->is_multi() ) {
			if ( ! $entry->from_field( $field ) ) {
				return;
			}
			$form = GF_Form::by_id( $field->form_id );
		}

		/**
		 * Push legacy entry context.
		 */
		Legacy_Context::load( [
			'entry' => $entry,
			'form'  => $form,
		] );

		$context = Template_Context::from_template( $this, compact( 'field', 'entry' ) );

		$renderer = new Field_Renderer();
		$source   = is_numeric( $field->ID ) ? $form : new Internal_Source();

		$value = $renderer->render( $field, $this->view, $source, $entry, $this->request );

		/**
		 * @deprecated Here for back-compatibility.
		 */
		$label = apply_filters( 'gravityview_render_after_label', $field->get_label( $this->view, $form, $entry ), $field->as_configuration() );
		$label = apply_filters( 'gravityview/template/field_label', $label, $field->as_configuration(), $form->form ? $form->form : null, null );

		/**
		 * @filter `gravityview/template/field/label` Override the field label.
		 * @since  2.0
		 *
		 * @param string           $label   The label to override.
		 * @param Template_Context $context The context.
		 */
		$label = apply_filters( 'gravityview/template/field/label', $label, $context );

		/**
		 * @filter `gravityview/render/hide-empty-zone` Whether to hide an empty zone.
		 *
		 * @param bool             $hide_empty Should the row be hidden if the value is empty? Default: don't hide.
		 * @param Template_Context $context    The template context.
		 */
		$hide_empty = apply_filters( 'gravityview/render/hide-empty-zone', Utils::get( $extras, 'hide_empty', $this->view->settings->get( 'hide_empty', false ) ), $context );

		if ( is_numeric( $field->ID ) ) {
			$extras['field'] = $field->as_configuration();
		}

		$extras['entry']      = $entry->as_entry();
		$extras['hide_empty'] = $hide_empty;
		$extras['label']      = $label;
		$extras['value']      = $value;

		return \gravityview_field_output( $extras, $context );
	}

	/**
	 * Returns an array of variables that are ready for extraction.
	 *
	 * @since 3.0
	 *
	 * @param string|array $zones The field zones to grab.
	 *
	 * @return array<string, mixed> An associative array where key-value pair of $zone & \GV\Field_Collection, or 'has_$zone' and count.
	 */
	public function extract_zone_vars( $zones ) {
		if ( ! is_array( $zones ) ) {
			$zones = [ $zones ];
		}

		$vars = [];
		foreach ( $zones as $zone ) {
			$zone_var              = str_replace( '-', '_', $zone );
			$vars[ $zone_var ]     = $this->view->fields->by_position( 'directory_map-' . $zone )->by_visible( $this->view );
			$vars["has_$zone_var"] = $vars[ $zone_var ]->count();
		}

		return $vars;
	}

	/**
	 * Modifies the class applied to the entry row.
	 *
	 * @since 3.0
	 *
	 * @param string $class The class.
	 * @param Entry  $entry The entry.
	 * @param Template_Context The template context.
	 *
	 * @return string The classes.
	 */
	public static function entry_class( $class, $entry, $context ) {
		/**
		 * @filter     `gravityview_entry_class` Modify the class applied to the entry row.
		 * @deprecated Use `gravityview/template/map/entry/class`
		 *
		 * @param array            $entry Current entry being displayed
		 * @param GravityView_View $this  Current GravityView_View object
		 * @param string           $class Existing class.
		 *
		 * @return string The modified class.
		 */
		$class = apply_filters_deprecated(
			'gravityview_entry_class',
			[ $class, $entry->as_entry(), GravityView_View::getInstance() ],
			'3.0',
			'gravityview/template/map/entry/class'
		);

		/**
		 * @filter `gravityview/template/map/entry/class` Modify the class aplied to the entry row.
		 *
		 * @param string $class The existing class.
		 * @param Template_Context The template context.
		 *
		 * @return string The modified class.
		 */
		return apply_filters( 'gravityview/template/map/entry/class', $class, Template_Context::from_template( $context->template, compact( 'entry' ) ) );
	}

	/**
	 * Executes before the map entries are output.
	 *
	 * @since 3.0
	 *
	 * @param $context Template_Context The template context.
	 *
	 * @return void
	 */
	public static function body_before( $context ) {
		/**
		 * @action `gravityview/template/map/body/before` Executes before the map entries are output.
		 * @since  2.0
		 *
		 * @param Template_Context $context The template context.
		 */
		do_action( 'gravityview/template/map/body/before', $context );

		/**
		 * @action     `gravityview_map_body_before` Inside the `tbody`, before any rows are rendered. Can be used to insert additional rows.
		 * @since      1.0.7
		 * @deprecated Use `gravityview/template/map/body/before`
		 *
		 * @param GravityView_View $gravityview_view Current GravityView_View object.
		 */
		do_action_deprecated(
			'gravityview_map_body_before',
			[ GravityView_View::getInstance() /** ugh! */ ],
			'3.0',
			'gravityview/template/map/body/before'
		);
	}

	/**
	 * Executes after the map entries are output.
	 *
	 * @since 3.0
	 *
	 * @param Template_Context $context The template context.
	 *
	 * @return void
	 */
	public static function body_after( $context ) {
		/**
		 * @action `gravityview/template/map/body/after` Executes after the map entries are output.
		 * @since  2.0
		 *
		 * @param Template_Context $context The template context.
		 */
		do_action( 'gravityview/template/map/body/after', $context );

		/**
		 * @action     `gravityview_map_body_after` Inside the `tbody`, after any rows are rendered. Can be used to insert additional rows.
		 * @since      1.0.7
		 * @deprecated Use `gravityview/template/map/body/after`
		 *
		 * @param GravityView_View $gravityview_view Current GravityView_View object.
		 */
		do_action_deprecated(
			'gravityview_map_body_after',
			[ GravityView_View::getInstance() /** ugh! */ ],
			'3.0',
			'gravityview/template/map/body/after'
		);
	}

	/**
	 * Executes before each entry or its element (e.g., title, image, etc.).
	 *
	 * @since 3.0
	 *
	 * @param Entry            $entry   The entry.
	 * @param Template_Context $context The template context.
	 * @param string           $zone    The map zone (footer, image, title, etc.).
	 *
	 * @return void
	 */
	public static function entry_before( $entry, $context, $zone = '' ) {
		$zone = str_replace( '//', '/', "/$zone/" );

		/**
		 * @action `gravityview/template/map/entry/$zone/before` Output inside the `entry` of the map at the end.
		 * @since  2.0
		 *
		 * @param Template_Context $context The template context.
		 */
		do_action( sprintf( 'gravityview/template/map/entry%sbefore', $zone ), Template_Context::from_template( $context->template, compact( 'entry' ) ) );

		$zone = str_replace( '/', '_', $zone );

		/**
		 * @action     `gravityview_map_entry_$zone_before` Inside the `entry`, before any rows are rendered. Can be used to insert additional rows.
		 * @since      1.0.7
		 * @deprecated Use `gravityview/template/map/entry/$zone/before`
		 *
		 * @param GravityView_View $gravityview_view Current GravityView_View object.
		 */
		do_action_deprecated(
			sprintf( 'gravityview_map_entry%sbefore', $zone ),
			[ $entry->as_entry(), GravityView_View::getInstance() /** ugh! */ ],
			'3.0',
			sprintf( 'gravityview/template/map/entry%sbefore', $zone )
		);
	}

	/**
	 * Executes after each entry or its element (e.g., title, image, etc.).
	 *
	 * @since 3.0
	 *
	 * @param Entry            $entry   The entry.
	 * @param Template_Context $context The template context.
	 * @param string           $zone    The map zone (footer, image, title, etc.).
	 *
	 * @return void
	 */
	public static function entry_after( $entry, $context, $zone = '' ) {
		$zone = str_replace( '//', '/', "/$zone/" );

		/**
		 * @action `gravityview/template/map/entry/$zone/after` Output inside the `entry` of the map at the end.
		 * @since  2.0
		 *
		 * @param Template_Context $context The template context.
		 */
		do_action( sprintf( 'gravityview/template/map/entry%safter', $zone ), Template_Context::from_template( $context->template, compact( 'entry' ) ) );

		$zone = str_replace( '/', '_', $zone );

		/**
		 * @action     `gravityview_map_entry_$zone_after` Inside the `entry`, after any rows are rendered. Can be used to insert additional rows.
		 * @since      1.0.7
		 * @deprecated Use `gravityview/template/map/entry/after`
		 *
		 * @param GravityView_View $gravityview_view Current GravityView_View object.
		 */
		do_action_deprecated(
			sprintf( 'gravityview_map_entry%safter', $zone ),
			[ $entry->as_entry(), GravityView_View::getInstance() /** ugh! */ ],
			'3.0',
			sprintf( 'gravityview/template/map/entry%safter', $zone )
		);
	}

	/**
	 * Renders the map canvas.
	 *
	 * @since 3.0
	 *
	 * @param null|Template_Context $context The template context.
	 * @param null|Entry            $entry   The entry.
	 *
	 * @return void
	 */
	public static function render_map_canvas( $context = null, $entry = null ) {
		/**
		 * @action `gk/gravitymaps/render/map-canvas` Renders the map canvas.
		 *
		 * @since  3.0
		 *
		 * @param null|Entry            $entry   The entry.
		 * @param null|Template_Context $context The template context.
		 */
		do_action( 'gk/gravitymaps/render/map-canvas', $entry, $context );

		/**
		 * @since      1.0.0 Introduced.
		 * @since      1.6.2 Added the Context param
		 * @deprecated 3.0
		 *
		 * @action     `gravityview_map_render_div` Outputs the map into the View.
		 *
		 * @param null|Entry            $entry   The entry.
		 * @param null|Template_Context $context The template context.
		 */
		do_action_deprecated(
			'gravityview_map_render_div',
			[ $entry, $context ],
			'3.0',
			'gk/gravitymaps/render/map-canvas'
		);
	}
}
