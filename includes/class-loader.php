<?php
/**
 * Basic hook loader.
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class STVL_Loader {

	/**
	 * Registers an action callback.
	 *
	 * @param string   $hook Hook name.
	 * @param object   $component Component instance.
	 * @param string   $callback Callback method.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		add_action( $hook, array( $component, $callback ), $priority, $accepted_args );
	}

	/**
	 * Registers a filter callback.
	 *
	 * @param string   $hook Hook name.
	 * @param object   $component Component instance.
	 * @param string   $callback Callback method.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted args.
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook, array( $component, $callback ), $priority, $accepted_args );
	}
}
