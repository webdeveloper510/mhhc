<?php
/**
 * @license GPL-2.0-or-later
 *
 * Modified by The GravityKit Team on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityImport\Foundation\Settings;

class Helpers {
	/**
	 * Compares 2 values using an operator.
	 *
	 * @see UI/src/lib/validation.js
	 *
	 * @param string $first
	 * @param string $second
	 * @param string $op
	 *
	 * @return bool
	 */
	static function compare_values( $first, $second, $op ) {
		switch ( $op ) {
			case '!=':
				return $first != $second;
			case '>':
				return (int) $first > (int) $second;
			case '<':
				return (int) $first < (int) $second;
			case 'pattern':
				return preg_match( '/' . $first . '/', $second );
			case '=':
			default:
				return $first == $second;
		}
	}
}