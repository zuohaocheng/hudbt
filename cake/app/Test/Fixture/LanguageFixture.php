<?php
/**
 * LanguageFixture
 *
 */
class LanguageFixture extends CakeTestFixture {
/**
 * Table name
 *
 * @var string
 */
	public $table = 'language';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 5, 'key' => 'primary'),
		'lang_name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'flagpic' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'sub_lang' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3),
		'rule_lang' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'site_lang' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'site_lang_folder' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
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
			'lang_name' => 'Lorem ipsum dolor sit amet',
			'flagpic' => 'Lorem ipsum dolor sit amet',
			'sub_lang' => 1,
			'rule_lang' => 1,
			'site_lang' => 1,
			'site_lang_folder' => 'Lorem ipsum dolor sit amet'
		),
	);
}
