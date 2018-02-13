<?php

namespace wpscholar\WordPress;

/**
 * Class AutomaticWorkflowStep
 *
 * @package wpscholar\WordPress
 */
class AutomaticWorkflowStep extends SimpleWorkflowStep {

	/**
	 * Callback that returns an array of IDs to be transitioned.
	 *
	 * @var callable
	 */
	protected $callback;

	/**
	 * AutomaticWorkflowStep constructor.
	 *
	 * @param $name
	 * @param array $data
	 */
	public function __construct( $name, array $data = [] ) {

		// Setup callback
		if ( isset( $data['callback'] ) && is_callable( $data['callback'] ) ) {
			$this->callback = $data['callback'];
		}
		unset( $data['callback'] );

		// Setup cron schedule
		$schedule = isset( $data['schedule'] ) ? $data['schedule'] : 'hourly';
		unset( $data['schedule'] );

		// Run parent constructor
		parent::__construct( $name, $data );

		// Setup cron event
		if ( ! wp_next_scheduled( $name ) ) {
			wp_schedule_event( time(), $schedule, $name );
		}

		// Add cron callback
		add_action( $name, array( $this, 'cron' ) );
	}

	/**
	 * Cron callback that executes transitions on returned object IDs.
	 */
	public function cron() {
		$ids = \call_user_func( $this->callback );
		if ( $ids && \is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$this->transition( absint( $id ) );
			}
		}
	}

}