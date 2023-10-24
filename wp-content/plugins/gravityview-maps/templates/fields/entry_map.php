<?php
/**
 * Field Entry Map template
 *
 * @since     1.0.0
 * @license   GPL2+
 * @author    GravityKit <hello@gravitykit.com>
 * @link      https://www.gravitykit.com
 * @copyright Copyright 2015, Katz Web Services, Inc.
 *
 * @package   GravityView_Maps
 *
 * @var \GV\Template_Context $gravityview
 */

$data = \GravityKit\GravityMaps\Data::get_instance( $gravityview->view );
$markers = $data::get_markers();

if ( $markers ) {
	\GravityKit\GravityMaps\Views\Map::render_map_canvas( null, $gravityview->entry );
}