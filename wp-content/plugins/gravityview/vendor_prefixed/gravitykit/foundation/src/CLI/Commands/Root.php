<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by gravityview on 17-October-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityView\Foundation\CLI\Commands;

use GravityKit\GravityView\Foundation\CLI\AbstractCommand;
use GravityKitFoundation;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\format_items;

/**
 * Manage GravityKit products and licenses.
 */
class Root extends AbstractCommand {
	/**
	 * Display GravityKit Foundation version.
	 *
	 * @since      1.2.0
	 *
	 * @subcommand version
	 *
	 * @return void
	 */
	public function version() {
		$foundation_information = GravityKitFoundation::get_instance()->get_foundation_information();

		WP_CLI::line( $foundation_information['loaded_by_message'] );
	}
}
