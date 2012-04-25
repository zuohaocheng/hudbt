<?php
/**
 * MessageFixture
 *
 */
class MessageFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'sender' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'receiver' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'subject' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'msg' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'location' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 6),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'receiver' => array('column' => 'receiver', 'unique' => 0), 'sender' => array('column' => 'sender', 'unique' => 0)),
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
			'sender' => 1,
			'receiver' => 1,
			'added' => '2012-04-11 19:37:00',
			'subject' => 'Lorem ipsum dolor sit amet',
			'msg' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'location' => 1
		),
	);
}
