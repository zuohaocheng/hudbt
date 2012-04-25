<?php
App::uses('AppModel', 'Model');
/**
 * File Model
 *
 */
class File extends AppModel {
  	public $belongsTo = array(
		'Torrent' => array(
			'className' => 'Torrent',
			'foreignKey' => 'torrent',
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
	public $displayField = 'filename';
}
