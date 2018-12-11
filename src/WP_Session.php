<?php

namespace WPLibs\Session;

class WP_Session implements \ArrayAccess, \Countable {
	/**
	 * The session cookie name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The session store instance.
	 *
	 * @var \WPLibs\Session\Store
	 */
	protected $session;

	/**
	 * Default session configure.
	 *
	 * @var array
	 */
	protected $config = [
		'lifetime'        => 1440,  // The session lifetime in minutes.
		'expire_on_close' => false, // If true, the session immediately expire on the browser closing.
		'cookie_name'     => null,
	];

	/**
	 * Create new session.
	 *
	 * @param string                        $name   The session cookie name, should be unique.
	 * @param array                         $config The session configure.
	 * @param \SessionHandlerInterface|null $handler The session handler.
	 */
	public function __construct( $name, array $config = [], \SessionHandlerInterface $handler = null ) {
		$this->name = sanitize_key( $name );

		$this->config = array_merge( $this->config, $config );

		if ( ! $this->config['cookie_name'] ) {
			$this->config['cookie_name'] = $this->name . '_cookie';
		}

		$this->session = new Store( $name, $handler ?: new WP_Session_Handler( $this->name, $this->config['lifetime'] ) );
	}

	/**
	 * Hooks into WordPress to start, commit and run garbage collector.
	 *
	 * @return void
	 */
	public function hooks() {
		// Start and commit the session.
		add_action( 'init', [ $this, 'start_session' ], 0 );
		add_action( 'shutdown', [ $this, 'commit_session' ] );

		// Register the garbage collector.
		add_action( 'wp', [ $this, 'register_garbage_collection' ] );
		add_action( $this->get_schedule_name(), [ $this, 'cleanup_expired_sessions' ] );
	}

	/**
	 * Start the session when `plugin_loaded`.
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function start_session() {
		$session = $this->get_store();
		$session_name = $this->config['cookie_name'];

		$session->set_id(
			isset( $_COOKIE[ $session_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $session_name ] ) ) : null
		);

		$session->start();

		if ( ! $this->running_in_cli() ) {
			// Add the session identifier to cookie, so we can re-use that in lifetime.
			$expiration_date = $this->config['expire_on_close'] ? 0 : time() + $this->lifetime_in_seconds();

			setcookie( $session_name, $session->get_id(), $expiration_date, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl() );
		}
	}

	/**
	 * Commit session when `shutdown` fired.
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function commit_session() {
		$this->get_store()->save();
	}

	/**
	 * Clean up expired sessions by removing data and their expiration entries from
	 * the WordPress options table.
	 *
	 * This method should never be called directly and should instead be triggered as part
	 * of a scheduled task or cron job.
	 *
	 * @access private
	 */
	public function cleanup_expired_sessions() {
		if ( defined( 'WP_SETUP_CONFIG' ) || defined( 'WP_INSTALLING' ) ) {
			return;
		}

		$this->get_store()->get_handler()->gc( $this->lifetime_in_seconds() );
	}

	/**
	 * Register the garbage collector as a hourly event.
	 *
	 * @access private
	 */
	public function register_garbage_collection() {
		if ( ! wp_next_scheduled( $schedule = $this->get_schedule_name() ) ) {
			wp_schedule_event( time(), 'hourly', $schedule );
		}
	}

	/**
	 * Return the name of schedule.
	 *
	 * @return string
	 */
	protected function get_schedule_name() {
		return $this->name . '_session_garbage_collection';
	}

	/**
	 * Returns lifetime minutes in seconds.
	 *
	 * @return int
	 */
	protected function lifetime_in_seconds() {
		return $this->config['lifetime'] * 60;
	}

	/**
	 * Determines current process is running in CLI.
	 *
	 * @return bool
	 */
	protected function running_in_cli() {
		return PHP_SAPI === 'cli' || defined( 'WP_CLI' );
	}

	/**
	 * Get the session implementation.
	 *
	 * @return \WPLibs\Session\Store|\WPLibs\Session\Session
	 */
	public function get_store() {
		return $this->session;
	}

	/**
	 * Set session store.
	 *
	 * @param Session $store The session store implementation.
	 */
	public function set_store( Session $store ) {
		$this->session = $store;
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->session->all() );
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed $key The offset key.
	 * @return bool
	 */
	public function offsetExists( $key ) {
		return $this->session->exists( $key );
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed $key The offset key.
	 * @return mixed
	 */
	public function offsetGet( $key ) {
		return $this->session->get( $key );
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed $key   The offset key.
	 * @param  mixed $value The offset value.
	 * @return void
	 */
	public function offsetSet( $key, $value ) {
		$this->session->put( $key, $value );
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string $key Offset key.
	 * @return void
	 */
	public function offsetUnset( $key ) {
		$this->session->remove( $key );
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string $method     Call method.
	 * @param  array  $parameters Call method parameters.
	 * @return mixed
	 */
	public function __call( $method, $parameters ) {
		return call_user_func_array( [ $this->session, $method ], $parameters );
	}
}
