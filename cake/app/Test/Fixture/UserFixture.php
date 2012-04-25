<?php
/**
 * UserFixture
 *
 */
class UserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'username' => array('type' => 'string', 'null' => false, 'length' => 40, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'passhash' => array('type' => 'string', 'null' => false, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'secret' => array('type' => 'text', 'null' => false, 'default' => NULL, 'length' => 20),
		'email' => array('type' => 'string', 'null' => false, 'length' => 80, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'added' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_login' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_access' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index'),
		'last_home' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_offer' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'forum_access' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index'),
		'last_staffmsg' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_pm' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_comment' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_post' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_browse' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'last_music' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'last_catchup' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'editsecret' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'stylesheet' => array('type' => 'integer', 'null' => false, 'default' => '6', 'length' => 3),
		'caticon' => array('type' => 'integer', 'null' => false, 'default' => '5', 'length' => 3),
		'info' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'ip' => array('type' => 'string', 'null' => false, 'length' => 64, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'class' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3, 'key' => 'index'),
		'max_class_once' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3),
		'avatar' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'uploaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20, 'key' => 'index'),
		'downloaded' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20, 'key' => 'index'),
		'seedtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'leechtime' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20),
		'title' => array('type' => 'string', 'null' => false, 'length' => 30, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'country' => array('type' => 'integer', 'null' => false, 'default' => '107', 'length' => 5, 'key' => 'index'),
		'notifs' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modcomment' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'donated' => array('type' => 'float', 'null' => false, 'default' => '0.00', 'length' => '8,2'),
		'donated_cny' => array('type' => 'float', 'null' => false, 'default' => '0.00', 'length' => '8,2'),
		'donoruntil' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'warneduntil' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'noaduntil' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'torrentsperpage' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'topicsperpage' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'postsperpage' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'stafffor' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'supportfor' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'pickfor' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'supportlang' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'passkey' => array('type' => 'string', 'null' => false, 'length' => 32, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'promotion_link' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'clientselect' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'signature' => array('type' => 'string', 'null' => false, 'length' => 800, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'lang' => array('type' => 'integer', 'null' => false, 'default' => '6', 'length' => 5),
		'cheat' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'key' => 'index'),
		'download' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'upload' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'isp' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'invites' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'invited_by' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'vip_until' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'seedbonus' => array('type' => 'float', 'null' => false, 'default' => '0.0', 'length' => '10,1'),
		'charity' => array('type' => 'float', 'null' => false, 'default' => '0.0', 'length' => '10,1'),
		'bonuscomment' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'leechwarnuntil' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'lastwarned' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'timeswarned' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'warnedby' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
		'sbnum' => array('type' => 'integer', 'null' => false, 'default' => '70', 'length' => 5),
		'sbrefresh' => array('type' => 'integer', 'null' => false, 'default' => '120', 'length' => 5),
		'showdlnotice' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'pmnum' => array('type' => 'integer', 'null' => false, 'default' => '10', 'length' => 3),
		'school' => array('type' => 'integer', 'null' => false, 'default' => '16', 'length' => 5),
		'accepttdpms' => array('type' => 'string', 'null' => false, 'default' => 'yes'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)/* , 'username' => array('column' => 'username', 'unique' => 1), 'status_added' => array('column' => array('status', 'added'), 'unique' => 0), 'ip' => array('column' => 'ip', 'unique' => 0), 'uploaded' => array('column' => 'uploaded', 'unique' => 0), 'downloaded' => array('column' => 'downloaded', 'unique' => 0), 'country' => array('column' => 'country', 'unique' => 0), 'last_access' => array('column' => 'last_access', 'unique' => 0), 'enabled' => array('column' => 'enabled', 'unique' => 0), 'warned' => array('column' => 'warned', 'unique' => 0), 'cheat' => array('column' => 'cheat', 'unique' => 0), 'class' => array('column' => 'class', 'unique' => 0), 'passkey' => array('column' => 'passkey', 'unique' => 0), 'k_promotion_link' => array('column' => 'promotion_link', 'unique' => 0), 'k_forum_access' => array('column' => 'forum_access', 'unique' => 0) */),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records =[];// array(
	/* 	array( */
	/* 		'id' => 1, */
	/* 		'username' => 'Lorem ipsum dolor sit amet', */
	/* 		'passhash' => 'Lorem ipsum dolor sit amet', */
	/* 		'secret' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.', */
	/* 		'email' => 'Lorem ipsum dolor sit amet', */
	/* 		'added' => '2012-04-20 11:41:42', */
	/* 		'last_login' => '2012-04-20 11:41:42', */
	/* 		'last_access' => '2012-04-20 11:41:42', */
	/* 		'last_home' => '2012-04-20 11:41:42', */
	/* 		'last_offer' => '2012-04-20 11:41:42', */
	/* 		'forum_access' => '2012-04-20 11:41:42', */
	/* 		'last_staffmsg' => '2012-04-20 11:41:42', */
	/* 		'last_pm' => '2012-04-20 11:41:42', */
	/* 		'last_comment' => '2012-04-20 11:41:42', */
	/* 		'last_post' => '2012-04-20 11:41:42', */
	/* 		'last_browse' => 1, */
	/* 		'last_music' => 1, */
	/* 		'last_catchup' => 1, */
	/* 		'editsecret' => 'Lorem ipsum dolor sit amet', */
	/* 		'stylesheet' => 1, */
	/* 		'caticon' => 1, */
	/* 		'info' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.', */
	/* 		'ip' => 'Lorem ipsum dolor sit amet', */
	/* 		'class' => 1, */
	/* 		'max_class_once' => 1, */
	/* 		'avatar' => 'Lorem ipsum dolor sit amet', */
	/* 		'uploaded' => 1, */
	/* 		'downloaded' => 1, */
	/* 		'seedtime' => 1, */
	/* 		'leechtime' => 1, */
	/* 		'title' => 'Lorem ipsum dolor sit amet', */
	/* 		'country' => 1, */
	/* 		'notifs' => 'Lorem ipsum dolor sit amet', */
	/* 		'modcomment' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.', */
	/* 		'donated' => 1, */
	/* 		'donated_cny' => 1, */
	/* 		'donoruntil' => '2012-04-20 11:41:42', */
	/* 		'warneduntil' => '2012-04-20 11:41:42', */
	/* 		'noaduntil' => '2012-04-20 11:41:42', */
	/* 		'torrentsperpage' => 1, */
	/* 		'topicsperpage' => 1, */
	/* 		'postsperpage' => 1, */
	/* 		'stafffor' => 'Lorem ipsum dolor sit amet', */
	/* 		'supportfor' => 'Lorem ipsum dolor sit amet', */
	/* 		'pickfor' => 'Lorem ipsum dolor sit amet', */
	/* 		'supportlang' => 'Lorem ipsum dolor sit amet', */
	/* 		'passkey' => 'Lorem ipsum dolor sit amet', */
	/* 		'promotion_link' => 'Lorem ipsum dolor sit amet', */
	/* 		'clientselect' => 1, */
	/* 		'signature' => 'Lorem ipsum dolor sit amet', */
	/* 		'lang' => 1, */
	/* 		'cheat' => 1, */
	/* 		'download' => 1, */
	/* 		'upload' => 1, */
	/* 		'isp' => 1, */
	/* 		'invites' => 1, */
	/* 		'invited_by' => 1, */
	/* 		'vip_until' => '2012-04-20 11:41:42', */
	/* 		'seedbonus' => 1, */
	/* 		'charity' => 1, */
	/* 		'bonuscomment' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.', */
	/* 		'leechwarnuntil' => '2012-04-20 11:41:42', */
	/* 		'lastwarned' => '2012-04-20 11:41:42', */
	/* 		'timeswarned' => 1, */
	/* 		'warnedby' => 1, */
	/* 		'sbnum' => 1, */
	/* 		'sbrefresh' => 1, */
	/* 		'showdlnotice' => 1, */
	/* 		'pmnum' => 1, */
	/* 		'school' => 1 */
	/* 	), */
#	);
	public function init() {
	  include('usersf.php');
	  $this->records = $records;
	  parent::init();
	}
}




