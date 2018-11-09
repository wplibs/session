<?php

namespace WPLibs\Session;

class WP_Session_Handler implements \SessionHandlerInterface {
	/**
	 * THe session name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The number of minutes the session should be valid.
	 *
	 * @var int
	 */
	protected $minutes;

	/**
	 * The existence state of the session.
	 *
	 * @var bool
	 */
	protected $exists = false;

	/**
	 * Create a new database session handler instance.
	 *
	 * @param  string $name    The session name.
	 * @param  int    $minutes The number of minutes.
	 */
	public function __construct( $name, $minutes ) {
		$this->name    = $name;
		$this->minutes = $minutes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function open( $save_path, $session_name ) {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function read( $session_id ) {
		$session = get_option( $this->get_option_name( $session_id ), null );

		if ( $this->expired( $session ) ) {
			$this->exists = true;

			return null;
		}

		if ( isset( $session['payload'] ) ) {
			$this->exists = true;

			return $session['payload'];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function write( $session_id, $data ) {
		$payload = [
			'payload'       => $data,
			'last_activity' => time(),
		];

		// Try determines  existence state of session ID.
		if ( ! $this->exists ) {
			$this->read( $session_id );
		}

		if ( $this->exists ) {
			update_option( $this->get_option_name( $session_id ), $payload, false );
		} else {
			add_option( $this->get_option_name( $session_id ), $payload, '', 'no' );
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroy( $session_id ) {
		delete_option( $this->get_option_name( $session_id ) );

		$this->exists = false;

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc( $lifetime ) {
		global $wpdb;

		$placeholder = $this->get_option_name( '' );

		// @codingStandardsIgnoreLine
		$sessions = $wpdb->get_results( "SELECT * FROM `$wpdb->options` WHERE `option_name` LIKE '{$placeholder}%' LIMIT 0, 10000", ARRAY_A );

		if ( empty( $sessions ) ) {
			return;
		}

		$expired      = [];
		$expired_time = time() - $lifetime;

		foreach ( $sessions as $session ) {
			$payload = maybe_unserialize( $session['option_value'] );

			if ( ! isset( $payload['last_activity'] ) || $payload['last_activity'] <= $expired_time ) {
				$expired[] = (int) $session['option_id'];
			}
		}

		// Delete expired sessions.
		if ( ! empty( $expired ) ) {
			$placeholders = implode( ', ', $expired );

			// @codingStandardsIgnoreLine
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_id` IN ($placeholders)" );
		}
	}

	/**
	 * Determine if the session is expired.
	 *
	 * @param  array $session An array session payload data.
	 * @return bool
	 */
	protected function expired( $session ) {
		return isset( $session['last_activity'] ) && $session['last_activity'] < ( time() - $this->minutes * 60 );
	}

	/**
	 * Returns option name.
	 *
	 * @param  string $session_id Session ID.
	 * @return string
	 */
	protected function get_option_name( $session_id ) {
		return "_wp_session_{$this->name}_{$session_id}";
	}
}
