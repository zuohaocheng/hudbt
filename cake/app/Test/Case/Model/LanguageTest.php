<?php
App::uses('Language', 'Model');

/**
 * Language Test Case
 *
 */
class LanguageTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.language');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Language = ClassRegistry::init('Language');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Language);

		parent::tearDown();
	}

}
