<?php

namespace GravityKit\GravityMaps;

/**
 * Class AbstractSingleton.
 *
 * @since   3.0
 *
 * @package GravityKit\GravityMaps
 */
abstract class AbstractSingleton {
	/**
	 * List of all instances created.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * AbstractSingleton constructor. Don't extend this unless for very specific uses.
	 *
	 * @since 3.0
	 */
	protected function __construct() {
		// Initializes the singleton, with a method that is always called.
		$this->register();
	}

	/**
	 * Creates the instance of the object that extends this class.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public static function instance() {
		if ( ! isset( static::$instances[ static::class ] ) ) {
			static::$instances[ static::class ] = new static();
		}

		return static::$instances[ static::class ];
	}

	/**
	 * Runs when the singleton is initialized.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	abstract protected function register(): void;

	/**
	 * Prevents cloning the singleton.
	 *
	 * @since 3.0
	 */
	public function __clone() {
	}

	/**
	 * Prevents serializing the singleton.
	 *
	 * @since 3.0
	 */
	public function __wakeup() {
	}
}
