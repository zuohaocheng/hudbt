<?php
App::uses('UserProperty', 'Model');

/**
 * UserProperty Test Case
 *
 */
class UserPropertyTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.user_property', 'app.user', 'app.torrent', 'app.sub', 'app.snatched', 'app.peer', 'app.comment', 'app.tcategory', 'app.tcategories_tcategory', 'app.torrents_tcategory');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserProperty = ClassRegistry::init('UserProperty');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserProperty);

		parent::tearDown();
	}

}
