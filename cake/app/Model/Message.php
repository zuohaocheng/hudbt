<?php
App::uses('AppModel', 'Model');
/**
 * Message Model
 *
 */
class Message extends AppModel {

  	public $belongsTo = array(
		'Sender' => array(
			'className' => 'User',
			'foreignKey' => 'sender',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Receiver' => array(
			'className' => 'User',
			'foreignKey' => 'receiver',
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
	public $displayField = 'subject';
}
