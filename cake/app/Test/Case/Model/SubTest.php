<?php
App::uses('Sub', 'Model');

/**
 * Sub Test Case
 *
 */
class SubTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.sub', 'app.torrent', 'app.user', 'app.tcategory', 'app.tcategories_tcategory', 'app.torrents_tcategory', 'app.lang');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Sub = ClassRegistry::init('Sub');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Sub);

		parent::tearDown();
	}

}
