<?php
App::uses('Snatched', 'Model');

/**
 * Snatched Test Case
 *
 */
class SnatchedTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.snatched');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Snatched = ClassRegistry::init('Snatched');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Snatched);

		parent::tearDown();
	}

}
