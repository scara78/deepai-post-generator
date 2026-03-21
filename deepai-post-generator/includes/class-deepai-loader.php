<?php
/**
 * Hook loader class.
 *
 * Maintains a list of all hooks registered throughout the plugin
 * and fires them with WordPress on run().
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_Loader {

	/**
	 * Array of actions registered with WordPress.
	 *
	 * @var array
	 */
	protected $actions;

	/**
	 * Array of filters registered with WordPress.
	 *
	 * @var array
	 */
	protected $filters;

	/**
	 * Initialize the collections.
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Add an action to the collection.
	 *
	 * @param string $hook          WordPress hook name.
	 * @param object $component     Object instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter to the collection.
	 *
	 * @param string $hook          WordPress hook name.
	 * @param object $component     Object instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Utility to register hooks into a collection.
	 *
	 * @param array  $hooks         Collection of hooks.
	 * @param string $hook          Hook name.
	 * @param object $component     Object instance.
	 * @param string $callback      Method name.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
