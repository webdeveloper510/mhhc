<?php
/**
 * The header for the output list.
 *
 * @global \GV\Template_Context $gravityview
 */

if ( ! isset( $gravityview ) || empty( $gravityview->template ) ) {
	gravityview()->log->error( '{file} template loaded without context', [ 'file' => __FILE__ ] );
	return;
}

?>
<?php gravityview_before( $gravityview ); ?>
<div class="<?php gv_container_class( 'gv-map-container gv-container gv-grid', true, $gravityview ); ?>">
	<?php gravityview_header( $gravityview ); ?>
