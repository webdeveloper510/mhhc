<?php

namespace GravityKit\GravityImport;

class Compat {
	public function __construct() {
		/**
		 * Do only when processing via the Processor...
		 */
		add_action( 'gravityview/import/processor/init', function () {
			/**
			 * Evade the zero-spam plugin.
			 * https://github.com/gravityview/Import-Entries/issues/329
			 */
			if ( function_exists( 'zerospam_get_key' ) ) {
				add_action( 'gform_pre_submission', function () {
					$_POST['zerospam_key'] = zerospam_get_key(); // Inject the correct key ;)
				}, 1 );
			}

			// Imported files shall never be considered spam!
			add_filter( 'gform_entry_is_spam', '__return_false', 1000 );

			/**
			 * Akismet should not be spammed.
			 * https://github.com/gravityview/Import-Entries/issues/331
			 */
			add_filter( 'gform_akismet_enabled', '__return_false' );

			/**
			 * Invisible reCaptcha should be ignored.
			 * https://github.com/gravityview/Import-Entries/issues/336
			 */
			add_filter( 'google_invre_is_gf_excluded', '__return_true' );
		} );
	}
}