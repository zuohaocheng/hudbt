<?php
/**
 * CommentFixture
 *
 */
class CommentFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'user' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'torrent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'text' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'ori_text' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'editedby' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'editdate' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'offer' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'quote' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10),
		'editnotseen' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'user' => array('column' => 'user', 'unique' => 0), 'torrent_id' => array('column' => array('torrent', 'id'), 'unique' => 0), 'offer_id' => array('column' => array('offer', 'id'), 'unique' => 0)),
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
			'user' => 1,
			'torrent' => 1,
			'added' => '2012-04-12 14:46:22',
			'text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'ori_text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'editedby' => 1,
			'editdate' => '2012-04-12 14:46:22',
			'offer' => 1,
			'quote' => 1,
			'editnotseen' => 1
		),
	);
}
