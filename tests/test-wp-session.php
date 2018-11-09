<?php

use WPLibs\Session\WP_Session;

class Session_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$_COOKIE['test_cookie'] = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
		$wp_session = new WP_Session('test');
		$wp_session->start_session();

		$this->session = $wp_session;
	}

	public function tearDown() {
		parent::tearDown();

		$this->session->save();
	}

	public function testSession() {
		$this->session->put('test_set_cookie', 111);
	}

	public function testSession2() {
		$this->assertEquals(111, $this->session->get('test_set_cookie'));
	}

	public function testCountable() {
		$session = $this->session;
		$session->flush();

		$session->put( 'a', 5 );
		$session->put( 'b', 5 );
		$this->assertEquals( 2, count( $session ) );
	}

	public function testArrayable() {
		$session = $this->session;
		$session->flush();

		$session->put( 'a', 5 );
		$session['b'] = 100;

		$this->assertTrue( isset( $session['b'] ) );
		$this->assertFalse( isset( $session['bababa'] ) );

		$this->assertEquals( 5, $session['a'] );
		$this->assertEquals( 100, $session['b'] );

		unset( $session['b'] );
		$this->assertFalse( isset( $session['b'] ) );
	}
}
