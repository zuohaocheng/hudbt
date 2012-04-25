<?php
App::uses('Torrent', 'Model');

/**
 * Torrent Test Case
 *
 */
class TorrentTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.torrent', 'app.user', 'app.snatched', 'app.sub', 'app.peer', 'app.comment', 'app.tcategory', 'app.tcategories_tcategory', 'app.torrents_tcategory');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Torrent = ClassRegistry::init('Torrent');

		$res = sql_query("SELECT * FROM users WHERE users.id = 1 LIMIT 1");
		$GLOBALS["CURUSER"] = mysql_fetch_array($res);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Torrent);

		parent::tearDown();
	}

	public function testFixture() {
	  $this->Torrent->id = 1;
	  $this->assertTrue($this->Torrent->exists());
	}

	public function testLogin() {
	  global $CURUSER;
	  $this->assertTrue(isset($CURUSER));
	}

	public function testDeleteWithoutReason() {
	  $this->Torrent->id = 1;
	  $this->assertFalse($this->Torrent->delete());
	}

	public function testDeleteWithReason10() {
	  $this->Torrent->id = 1;
	  $this->Torrent->reason = 'blablah';
	  $this->assertTrue($this->Torrent->delete());
	}

	/* public function testDeleteWithReason1() { */
	/*   $this->Torrent->id = 1; */
	/*   $this->Torrent->reason = 'blablah'; */
	/*   $this->assertTrue($this->Torrent->delete()); */
	/* } */

}
