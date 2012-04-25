<?php
/**
 * TorrentFixture
 *
 */
class TorrentFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 8, 'key' => 'primary'),
		'info_hash' => array('type' => 'text', 'null' => true, 'default' => NULL, 'length' => 20, 'key' => 'unique'),
		'name' => array('type' => 'string', 'null' => false, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'filename' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'save_as' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'descr' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'small_descr' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'ori_descr' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'category' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'key' => 'index'),
		'source' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'medium' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'codec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'standard' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'processing' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'team' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'audiocodec' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'numfiles' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'comments' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'views' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'hits' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'times_completed' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'leechers' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'seeders' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'last_action' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'owner' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8, 'key' => 'index'),
		'nfo' => array('type' => 'binary', 'null' => true, 'default' => NULL),
		'sp_state' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3),
		'promotion_time_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'promotion_until' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'url' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'cache_stamp' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'picktime' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_reseed' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'storing' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
		'anonymous' => array('type' => 'string', 'null' => false, 'length' => 3, 'default' => 'no'),
		'visible' => array('type' => 'string', 'null' => false, 'length' => 3, 'default' => 'no'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), /* 'info_hash' => array('column' => 'info_hash', 'unique' => 1), 'owner' => array('column' => 'owner', 'unique' => 0), 'visible_pos_id' => array('column' => array('visible', 'pos_state', 'id'), 'unique' => 0), 'url' => array('column' => 'url', 'unique' => 0), 'category_visible_banned' => array('column' => array('category', 'visible', 'banned'), 'unique' => 0), 'visible_banned_pos_id' => array('column' => array('visible', 'banned', 'pos_state', 'id'), 'unique' => 0), 'startseed' => array('column' => 'startseed', 'unique' => 0), 'storing' => array('column' => 'storing', 'unique' => 0), 'name' => array('column' => 'name', 'unique' => 0) */),
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
			'info_hash' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'name' => 'Lorem ipsum dolor sit amet',
			'filename' => 'Lorem ipsum dolor sit amet',
			'save_as' => 'Lorem ipsum dolor sit amet',
			'descr' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'small_descr' => 'Lorem ipsum dolor sit amet',
			'ori_descr' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'category' => 1,
			'source' => 1,
			'medium' => 1,
			'codec' => 1,
			'standard' => 1,
			'processing' => 1,
			'team' => 1,
			'audiocodec' => 1,
			'size' => 1,
			'added' => '2012-04-12 16:48:41',
			'numfiles' => 1,
			'comments' => 1,
			'views' => 1,
			'hits' => 1,
			'times_completed' => 1,
			'leechers' => 1,
			'seeders' => 1,
			'last_action' => '2012-04-12 16:48:41',
			'owner' => 1,
			'nfo' => 'Lorem ipsum dolor sit amet',
			'sp_state' => 1,
			'promotion_time_type' => 1,
			'promotion_until' => '2012-04-12 16:48:41',
			'url' => 1,
			'cache_stamp' => 1,
			'picktime' => '2012-04-12 16:48:41',
			'last_reseed' => '2012-04-12 16:48:41',
			'storing' => 1,
			'anonymous' => 'yes'
		),
	);

	public function init() {
	  include('torrentsf.php');
	  $this->records = $records;
	  parent::init();
	}
}
