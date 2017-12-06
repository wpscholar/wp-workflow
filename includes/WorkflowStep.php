<?php

namespace wpscholar\WordPress;

/**
 * Class WorkflowStep
 *
 * @package wpscholar\WordPress
 */
abstract class WorkflowStep {

	/**
	 * Internal name for this step.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Contains all pertinent data associated with this step.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Boolean representing whether the current step is enabled.
	 *
	 * @var bool
	 */
	public $isEnabled = true;

	/**
	 * Capabilities required to perform a transition
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * WorkflowStep constructor.
	 *
	 * @param string $name
	 * @param array $data
	 */
	public function __construct( $name, array $data = [] ) {

		// Setup actions
		if ( isset( $data['actions'] ) && is_array( $data['actions'] ) ) {
			foreach ( $data['actions'] as $action ) {
				if ( is_callable( $action ) ) {
					$this->addAction( $action );
				}
			}
		}
		unset( $data['actions'] );

		// Setup conditions
		if ( isset( $data['conditions'] ) && is_array( $data['conditions'] ) ) {
			foreach ( $data['conditions'] as $condition ) {
				if ( is_callable( $condition ) ) {
					$this->addCondition( $condition );
				}
			}
		}
		unset( $data['conditions'] );

		// Setup capabilities
		if ( isset( $data['capabilities'] ) && is_array( $data['capabilities'] ) ) {
			foreach ( $data['capabilities'] as $capability ) {
				$this->addCapability( $capability );
			}
		}
		unset( $data['capabilities'] );

		// Set class properties
		$this->name = $name;
		$this->data = $data;
	}

	/**
	 * Check if a user has the required capabilities to transition an object.
	 *
	 * @return bool
	 */
	public function canTransition() {
		$canTransition = true;
		$user = wp_get_current_user();
		if ( $this->capabilities ) {
			foreach ( $this->capabilities as $capability ) {
				if ( ! $user->has_cap( $capability ) ) {
					$canTransition = false;
					break;
				}
			}
		}

		return $canTransition;
	}

	/**
	 * Add a capability
	 * A user attempting to perform a transition must have all capabilities.
	 *
	 * @param string $capability
	 */
	public function addCapability( $capability ) {
		$this->capabilities[] = $capability;
	}

	/**
	 * Add a condition
	 * Conditions are checks to see if a transition should be allowed to occur.
	 *
	 * @param callable $condition
	 * @param int $priority
	 */
	public function addCondition( callable $condition, $priority = 10 ) {
		add_filter( "should_transition-{$this->name}", $condition, $priority, 2 );
	}

	/**
	 * Add an action
	 * Actions are events that fire when a transition occurs.
	 *
	 * @param callable $action
	 * @param int $priority
	 */
	public function addAction( callable $action, $priority = 10 ) {
		add_action( "transition-{$this->name}", $action, $priority, 2 );
	}

	/**
	 * Check if an object should transition.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function shouldTransition( $id = 0 ) {
		return $this->isEnabled ? (bool) apply_filters( "should_transition-{$this->name}", true, $id ) : false;
	}

	/**
	 * Transition an object.
	 *
	 * @param int $id
	 *
	 * @return bool Returns true on success or false on failure.
	 */
	public function transition( $id = 0 ) {
		$transitioned = false;
		if ( $this->isEnabled && $this->canTransition() && $this->shouldTransition( $id ) ) {
			do_action( "transition-{$this->name}", $id, $this->data );
			$transitioned = true;
		}

		return $transitioned;
	}

}