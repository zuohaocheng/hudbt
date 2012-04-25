<?php
App::uses('Peer', 'Model');

/**
 * Peer Test Case
 *
 */
class PeerTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.peer');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Peer = ClassRegistry::init('Peer');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Peer);

		parent::tearDown();
	}

}
