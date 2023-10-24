<?php
/**
 * The entry loop for the list output.
 *
 * @global \GV\Template_Context $gravityview
 */

if ( ! isset( $gravityview ) || empty( $gravityview->template ) ) {
	gravityview()->log->error( '{file} template loaded without context', [ 'file' => __FILE__ ] );
	return;
}

$template = $gravityview->template;

// There are no entries.
if ( ! $gravityview->entries->count() ) {

	$no_results_css_class = 'gv-no-results gv-no-results-text';

	if ( 1 === (int) $gravityview->view->settings->get( 'no_entries_options', '0' ) ) {
		$no_results_css_class = 'gv-no-results gv-no-results-form';
	}

	?>
	<div class="gv-map-view <?php echo esc_attr( $no_results_css_class ); ?>">
		<?php \GravityKit\GravityMaps\Views\Map::render_map_canvas( $gravityview ); ?>

		<div class="gv-map-view-title">
			<h3><?php echo gv_no_results( true, $gravityview ); ?></h3>
		</div>
	</div>
	<?php
} else {
	/** @action `gravityview/template/map/body/before` */
	$template::body_before( $gravityview );

	// There are entries. Loop through them.
	foreach ( $gravityview->entries->all() as $entry ) {

		$entry_slug = GravityView_API::get_entry_slug( $entry->ID, $entry->as_entry() );

		/** @filter `gravityview/template/list/entry/class` */
		$entry_class = $template::entry_class( 'gv-map-view', $entry, $gravityview );

		?>
		<div id="gv_map_<?php echo $entry['id']; ?>" class="<?php echo gravityview_sanitize_html_class( $entry_class ); ?>">

			<?php

			/** @action `gravityview/template/map/entry/before` */
			$template::entry_before( $entry, $gravityview );

			?>

			<div class="gv-grid gv-map-view-main-attr">

			<?php
			/**
			 * @var bool                 $has_image
			 * @var \GV\Field_Collection $image
			 */
			extract( $template->extract_zone_vars( [ 'image' ] ) );

			if ( $has_image ) {
				/** @action `gravityview/template/map/entry/image/before` */
				$template::entry_before( $entry, $gravityview, 'image' );
				?>

				<div class="gv-grid-col-1-3 gv-map-view-image">
					<?php
					foreach ( $image->all() as $i => $field ) {
						echo $gravityview->template->the_field( $field, $entry, [ 'zone_id' => 'directory_map-image' ] );
					}
					?>
				</div>

				<?php

				/** @action `gravityview/template/map/entry/image/after` */
				$template::entry_after( $entry, $gravityview, 'image' );
			}
			?>

			<?php
			/**
			 * @var bool                 $has_title
			 * @var \GV\Field_Collection $title
			 */
			extract( $template->extract_zone_vars( [ 'title' ] ) );

			if ( $has_title ) {

				/** @action `gravityview/template/map/entry/title/before` */
				$template::entry_before( $entry, $gravityview, 'title' );

				?>

				<div class="gv-grid-col-1-3 gv-map-view-title">
					<?php
					$did_main = 0;
					foreach ( $title->all() as $i => $field ) {
						// The first field in the title zone is the main
						if ( $did_main == 0 ) {
							$did_main = 1;
							$extras   = [ 'wpautop' => false, 'markup' => '<h3 class="{{ class }}">{{ label }}{{ value }}</h3>' ];
						} else {
							$extras = [ 'wpautop' => true ];
						}

						$extras['zone_id'] = 'directory_map-title';
						echo $gravityview->template->the_field( $field, $entry, $extras );
					}
					?>
				</div>
				<?php

				/** @action `gravityview/template/map/entry/title/after` */
				$template::entry_after( $entry, $gravityview, 'title' );
			}

			/**
			 * @var bool                 $has_details
			 * @var \GV\Field_Collection $details
			 */
			extract( $template->extract_zone_vars( [ 'details' ] ) );

			if ( $has_details ) {
				/** @action `gravityview/template/map/entry/details/before` */
				$template::entry_before( $entry, $gravityview, 'details' );
				?>
				<div class="gv-grid-col-1-3 gv-map-view-details">
					<?php
					foreach ( $details->all() as $i => $field ) {
						echo $gravityview->template->the_field( $field, $entry, [ 'zone_id' => 'directory_map-details' ] );
					}
					?>
				</div>

				<?php

				/** @action `gravityview/template/map/entry/details/after` */
				$template::entry_after( $entry, $gravityview, 'details' );
			}

			?>
			</div>

			<?php

			/**
			 * @var bool                 $has_middle
			 * @var \GV\Field_Collection $middle
			 */
			extract( $template->extract_zone_vars( [ 'middle' ] ) );

			if ( $has_middle ) {
				/** @action `gravityview/template/map/entry/middle/before` */
				$template::entry_before( $entry, $gravityview, 'middle' );
				?>

				<div class="gv-grid gv-map-view-middle-container">
					<div class="gv-grid-col-1-1 gv-map-view-middle">
						<?php
						foreach ( $middle->all() as $i => $field ) {
							echo $gravityview->template->the_field( $field, $entry, [ 'zone_id' => 'directory_map-middle' ] );
						}
						?>
					</div>
				</div>

				<?php

				/** @action `gravityview/template/map/entry/middle/after` */
				$template::entry_after( $entry, $gravityview, 'middle' );
			}

			/**
			 * @var bool                 $has_footer
			 * @var \GV\Field_Collection $footer
			 */
			extract( $template->extract_zone_vars( [ 'footer' ] ) );

			// Is the footer configured?
			if ( $has_footer ) {
				/** @action `gravityview/template/map/entry/footer/before` */
				$template::entry_before( $entry, $gravityview, 'footer' );
				?>

				<div class="gv-grid gv-map-view-footer-container">
					<div class="gv-grid-col-1-1 gv-map-view-footer">
						<?php
						foreach ( $footer->all() as $i => $field ) {
							echo $gravityview->template->the_field( $field, $entry, [ 'zone_id' => 'directory_map-footer' ] );
						}
						?>
					</div>
				</div>

				<?php

				/** @action `gravityview/template/map/entry/footer/after` */
				$template::entry_after( $entry, $gravityview, 'footer' );
			}

			/** @action `gravityview/template/map/entry/after` */
			$template::entry_after( $entry, $gravityview );

			?>
		</div>

	<?php
	}

	/** @action `gravityview/template/map/body/after` */
	$template::body_after( $gravityview );
}

