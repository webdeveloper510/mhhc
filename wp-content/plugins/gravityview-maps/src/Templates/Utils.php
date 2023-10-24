<?php

namespace GravityKit\GravityMaps\Templates;

use GV\Request;
use GV\Utils as GV_Utils;

/**
 * Class Utils for template-related utilities.
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps\Template
 */
class Utils {
	/**
	 * Returns whether the requested page is a GravityView search result or not.
	 *
	 * @since 3.0
	 *
	 * @param Request|null $request The request.
	 *
	 * @return bool
	 */
	public static function is_search( ?Request $request = null ): bool {
		if ( 1 === (int) GV_Utils::_REQUEST( 'is_current' ) ) {
			return true;
		}

		if ( GV_Utils::_REQUEST( 'address_search' ) ) {
			return true;
		}

		if ( is_null( $request ) ) {
			$request = gravityview()->request;
		}

		return $request->is_search();
	}
}