<?php

namespace wpscholar\WordPress;

/**
 * Class WorkflowRegistry
 *
 * @package wpscholar\WordPress
 */
class WorkflowRegistry {

	/**
	 * Internal array containing all registered workflow steps.
	 *
	 * @var array
	 */
	protected static $steps = [];

	/**
	 * Add a new workflow step.
	 *
	 * @param string $name
	 * @param WorkflowStep $value
	 */
	public static function add( $name, WorkflowStep $value ) {
		self::$steps[ $name ] = $value;
	}

	/**
	 * Check if workflow step exists.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function has( $name ) {
		return isset( self::$steps[ $name ] );
	}

	/**
	 * Get a workflow step by name.
	 *
	 * @param string $name
	 *
	 * @return WorkflowStep|null
	 */
	public static function get( $name ) {
		$value = null;
		if ( self::has( $name ) ) {
			$value = self::$steps[ $name ];
		}

		return $value;
	}

	/**
	 * Get all registered workflow steps.
	 *
	 * @return WorkflowStep[]
	 */
	public static function getAll() {
		return self::$steps;
	}

	/**
	 * Remove a workflow step by name.
	 *
	 * @param string $name
	 */
	public static function remove( $name ) {
		unset( self::$steps[ $name ] );
	}

	/**
	 * Remove all workflow steps from registry.
	 */
	public static function removeAll() {
		self::$steps = [];
	}

}