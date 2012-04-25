<?php
App::uses('AppModel', 'Model');
/**
 * Snatched Model
 *
 */
class Snatched extends AppModel {
/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'snatched';
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'userid',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Torrent' => array(
			'className' => 'Torrent',
			'foreignKey' => 'torrentid',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
/**
 * Display field
 *
 * @var string
 */
#	public $displayField = 'id';
}
