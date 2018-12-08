<?php

namespace WPLibs\Session\Flash;

use WPLibs\Session\Store;

class WP_Sesstion_Store implements Session_Store {
	/**
	 * The session instance.
	 *
	 * @var \WPLibs\Session\Store
	 */
	protected $session;

	/**
	 * Create a new session store instance.
	 *
	 * @param \WPLibs\Session\Store $session The session store implementation.
	 */
	public function __construct( Store $session ) {
		$this->session = $session;
	}

	/**
	 * {@inheritdoc}
	 */
	public function flash( $name, $data ) {
		$this->session->flash( $name, $data );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_flash( $name, $default = null ) {
		return $this->session->pull( $name, $default );
	}
}
