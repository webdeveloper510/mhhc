<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\Logger;

use GravityKit\GravityImport\Foundation\ThirdParty\Monolog\Logger as MonologLogger;
use GravityKit\GravityImport\Foundation\ThirdParty\Monolog\Handler\AbstractProcessingHandler;

/**
 * Handler for the Query Monitor plugin.
 *
 * @see https://github.com/johnbillion/query-monitor
 */
class QueryMonitorHandler extends AbstractProcessingHandler {
	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0.0
	 */
	public function __construct( $level = MonologLogger::DEBUG, $bubble = true ) {
		parent::__construct( $level, $bubble );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 1.0.0
	 */
	protected function write( array $record ) {
		$level = strtolower( $record['level_name'] );

		do_action( "qm/${level}", $record['formatted'] );
	}
}
