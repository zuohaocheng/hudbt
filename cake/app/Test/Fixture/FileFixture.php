<?php
/**
 * FileFixture
 *
 */
class FileFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'torrent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'filename' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'torrent' => array('column' => 'torrent', 'unique' => 0)),
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
			'filename' => 'Lorem ipsum dolor sit amet',
			'size' => 1
		),
	);
}
