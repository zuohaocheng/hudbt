<?php
App::uses('AppModel', 'Model');
/**
 * Sub Model
 *
 * @property Torrent $Torrent
 * @property Lang $Lang
 */
class Sub extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'title';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Torrent' => array(
			'className' => 'Torrent',
			'foreignKey' => 'torrent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'uppedby',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
		/* 'Lang' => array( */
		/* 	'className' => 'Lang', */
		/* 	'foreignKey' => 'lang_id', */
		/* 	'conditions' => '', */
		/* 	'fields' => '', */
		/* 	'order' => '' */
		/* ) */
	);
	
	public function beforeDelete() {
	  global $SUBSPATH;
	  $data = $this->read(['ext', 'torrent_id'], $this->id);
	  unlink($SUBSPATH.'/'.$data['Sub']['torrent_id'].'/'.$this->id.'.'.$data['Sub']['ext']);
	  var_dump($SUBSPATH.'/'.$data['Sub']['torrent_id'].'/'.$this->id.'.'.$data['Sub']['ext']);
	}
}
