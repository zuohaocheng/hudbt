<?php
/**
 * SnatchedFixture
 *
 */
class SnatchedFixture extends CakeTestFixture {
/**
 * Table name
 *
 * @var string
 */
	public $table = 'snatched';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'torrentid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'userid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'ip' => array('type' => 'string', 'null' => false, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'port' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'uploaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'downloaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'to_go' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'seedtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'leechtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'last_action' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'startdat' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'completedat' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'finished' => array('type' => 'string', 'null' => false, 'default' => 'no', 'length' => '3'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'torrentid_userid' => array('column' => array('torrentid', 'userid'), 'unique' => 0), 'userid' => array('column' => 'userid', 'unique' => 0)),
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
			'torrentid' => 1,
			'userid' => 1,
			'ip' => 'Lorem ipsum dolor sit amet',
			'port' => 1,
			'uploaded' => 1,
			'downloaded' => 1,
			'to_go' => 1,
			'seedtime' => 1,
			'leechtime' => 1,
			'last_action' => '2012-04-11 19:39:07',
			'startdat' => '2012-04-11 19:39:07',
			'completedat' => '2012-04-11 19:39:07'
		),
	);

	public function init() {
	  include('snatchedf.php');
	  $this->records = $records;
	  parent::init();
	}
}
