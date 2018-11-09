<?php

namespace WPLibs\Session;

interface Session {
	/**
	 * Start the session, reading the data from a handler.
	 *
	 * @return bool
	 */
	public function start();

	/**
	 * Save the session data to storage.
	 *
	 * @return bool
	 */
	public function save();

	/**
	 * Get all of the session data.
	 *
	 * @return array
	 */
	public function all();

	/**
	 * Checks if a key exists.
	 *
	 * @param  string|array $key Key name or an array keys.
	 * @return bool
	 */
	public function exists( $key );

	/**
	 * Checks if a key is present and not null.
	 *
	 * @param  string|array $key Key name or an array keys.
	 * @return bool
	 */
	public function has( $key );

	/**
	 * Get an item from the session with "dot" notation.
	 *
	 * @param  string $key     Session key name.
	 * @param  mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $key, $default = null );

	/**
	 * Put a key/value pair or array of key/value pairs in the session.
	 *
	 * @param  string|array $key   An array of key/value pairs or string key name.
	 * @param  mixed        $value If $key is string, use this as value.
	 * @return void
	 */
	public function put( $key, $value = null );

	/**
	 * Remove an item from the session, returning its value.
	 *
	 * @param  string $key Session key name to remove.
	 * @return mixed
	 */
	public function remove( $key );

	/**
	 * Remove all of the items from the session.
	 *
	 * @return void
	 */
	public function flush();

	/**
	 * Generate a new session ID for the session.
	 *
	 * @param  bool $destroy Destroy current session.
	 * @return bool
	 */
	public function regenerate( $destroy = false );

	/**
	 * Set the session ID.
	 *
	 * @param  string $id A valid session ID.
	 * @return void
	 */
	public function set_id( $id );

	/**
	 * Get the current session ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the name of the session.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Determine if the session has been started.
	 *
	 * @return bool
	 */
	public function is_started();

	/**
	 * Get the session handler instance.
	 *
	 * @return \SessionHandlerInterface
	 */
	public function get_handler();
}
