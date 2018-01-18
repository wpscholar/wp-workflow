<?php

namespace wpscholar\WordPress;

/**
 * Class Workflow
 *
 * @package wpscholar\WordPress
 */
class Workflow {

	/**
	 * Register a new workflow step
	 *
	 * @param string $name
	 * @param array $args
	 *
	 * @return WorkflowStep|false Returns class instance success or false on failure.
	 */
	public static function registerStep( $name, array $args ) {

		$registered = false;

		if ( ! WorkflowRegistry::has( $name ) ) {

			// Define an object type by default (allows for filtering)
			if ( ! isset( $args['object_type'] ) ) {
				$args['object_type'] = 'post';
			}

			// Setup type
			$type = isset( $args['type'] ) ? $args['type'] : 'type';
			unset( $args['type'] );

			// Default to simple workflow
			$workflowClass = __NAMESPACE__ . '\\SimpleWorkflowStep';

			if ( class_exists( $type ) ) {
				// If workflow class was explicitly declared, use it
				$workflowClass = $type;
			} else {
				// Normalize type
				$workflowType = str_replace( ' ', '',
					ucwords( str_replace( [ '-', '_' ], ' ', strtolower( $type ) ) )
				);
				// Dynamically determine appropriate workflow class
				$class = __NAMESPACE__ . '\\' . $workflowType . 'WorkflowStep';
				if ( class_exists( $class ) ) {
					$workflowClass = $class;
				}
			}

			// Create instance of workflow step
			$instance = new $workflowClass( $name, $args );

			// Register new workflow step
			WorkflowRegistry::add( $name, $instance );

			$registered = $instance;

		}

		return $registered;

	}


	/**
	 * Get actionable workflow steps for a specific object.
	 *
	 * @param int $id
	 * @param array $conditions
	 *
	 * @return WorkflowStep[]
	 */
	public static function getActionableSteps( $id, array $conditions = [] ) {

		$actionableSteps = [];
		$steps = WorkflowRegistry::getAll();

		foreach ( $steps as $step ) {
			$isActionable = $step->shouldTransition( $id );
			if ( $isActionable && $conditions ) {
				foreach ( $conditions as $property => $value ) {
					if ( \is_callable( $value ) ) {
						$isActionable = $value( $step );
					} else if ( ! isset( $step->data[ $property ] ) || $step->data[ $property ] !== $value ) {
						$isActionable = false;
					}
				}
			}
			if ( $isActionable ) {
				$actionableSteps[] = $step;
			}
		}

		return $actionableSteps;
	}

	/**
	 * Checks if a specific workflow step is actionable.
	 *
	 * @param int $id
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function isStepActionable( $id, $name ) {
		$isActionable = false;
		$step = WorkflowRegistry::get( $name );
		if ( $step ) {
			$isActionable = $step->shouldTransition( $id );
		}

		return $isActionable;
	}

	/**
	 * Trigger a workflow step by name for a specific object (and with provided data).
	 *
	 * @param string $name
	 * @param int $id
	 * @param array $data
	 *
	 * @return bool Returns true on success or false on failure.
	 */
	public static function triggerStep( $name, $id, array $data = [] ) {
		$triggered = false;
		if ( WorkflowRegistry::has( $name ) ) {
			$step = WorkflowRegistry::get( $name );
			if ( $step ) {
				$step->data = array_merge( $step->data, $data );
				$triggered = $step->transition( $id );
			}
		}

		return $triggered;
	}

}