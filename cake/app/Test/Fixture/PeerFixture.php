<?php
/**
 * PeerFixture
 *
 */
class PeerFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'torrent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'peer_id' => array('type' => 'binary', 'null' => false, 'default' => NULL, 'length' => 20),
		'ip' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'port' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'uploaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'downloaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'to_go' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'started' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_action' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'prev_action' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'userid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'agent' => array('type' => 'string', 'null' => false, 'length' => 60, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'finishedat' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'downloadoffset' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'uploadoffset' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'passkey' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'seeder' => array('type' => 'string', 'null' => false, 'default' => 'no', 'length' => '3'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'userid' => array('column' => 'userid', 'unique' => 0), 'torrent' => array('column' => 'torrent', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'torrent' => 1,
			'peer_id' => 'Lorem ipsum dolor ',
			'ip' => 'Lorem ipsum dolor sit amet',
			'port' => 1,
			'uploaded' => 1,
			'downloaded' => 1,
			'to_go' => 1,
			'started' => '2012-04-11 19:42:39',
			'last_action' => '2012-04-11 19:42:39',
			'prev_action' => '2012-04-11 19:42:39',
			'userid' => 1,
			'agent' => 'Lorem ipsum dolor sit amet',
			'finishedat' => 1,
			'downloadoffset' => 1,
			'uploadoffset' => 1,
			'passkey' => 'Lorem ipsum dolor sit amet',
			'seeder' => 'yes',
		),
	);

	public function init() {
	  include('peersf.php');
	  $this->records = $records;
	  parent::init();
	}	
}
