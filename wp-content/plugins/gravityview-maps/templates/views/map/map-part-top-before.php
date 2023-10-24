<?php

/**
 * The footer for the output list.
 *
 * @global \GV\Template_Context $gravityview
 */

if ( ! isset( $gravityview ) || empty( $gravityview->template ) ) {
	gravityview()->log->error( '{file} template loaded without context', [ 'file' => __FILE__ ] );
	return;
}

$map_settings = \GravityKit\GravityMaps\Admin::get_map_settings( $gravityview->view->ID );

$sticky_class = '';

if ( isset( $map_settings['map_canvas_sticky'] ) && $map_settings['map_canvas_sticky'] ) {
	$sticky_class = 'gv-map-sticky-container';
}

?>
<div class="gv-grid-col-1-1">
	<div class="<?php echo sanitize_html_class( $sticky_class ); ?>">
		<?php \GravityKit\GravityMaps\Views\Map::render_map_canvas( $gravityview ); ?>
	</div>
</div>
<div class="gv-grid-col-1-1">