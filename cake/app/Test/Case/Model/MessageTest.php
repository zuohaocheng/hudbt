<?php
App::uses('Message', 'Model');

/**
 * Message Test Case
 *
 */
class MessageTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.message');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Message = ClassRegistry::init('Message');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Message);

		parent::tearDown();
	}

}
