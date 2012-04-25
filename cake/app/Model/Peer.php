<?php
App::uses('AppModel', 'Model');
/**
 * Peer Model
 *
 */
class Peer extends AppModel {
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
			'foreignKey' => 'torrent',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
