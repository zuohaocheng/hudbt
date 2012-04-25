<?php
/**
 * SubFixture
 *
 */
class SubFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'torrent_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 8, 'key' => 'index'),
		'lang_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 5),
		'title' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'filename' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'uppedby' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'hits' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'ext' => array('type' => 'string', 'null' => false, 'length' => 10, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'torrentid_langid' => array('column' => array('torrent_id', 'lang_id'), 'unique' => 0)),
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
			'torrent_id' => 1,
			'lang_id' => 1,
			'title' => 'Lorem ipsum dolor sit amet',
			'filename' => 'Lorem ipsum dolor sit amet',
			'added' => '2012-04-11 19:33:32',
			'size' => 1,
			'uppedby' => 1,
			'hits' => 1,
			'ext' => 'Lorem ip'
		),
	);

	/* public function init() { */
	/*   include('subsf.php'); */
	/*   $this->records = $records; */
	/*   parent::init(); */
	/* } */
}
