<?php
App::uses('AppModel', 'Model');
/**
 * Comment Model
 *
 */
class Comment extends AppModel {
  	public $hasMany = [
		'Quoted' => [
			'className' => 'Comment',
			'foreignKey' => 'quote',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
			  ],
			   ];
	
  	public $belongsTo = array(
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user',
			'conditions' => '',
			'fields' => '',
			'order' => ''
			   ],
		'EditedBy' => [
			   'className' => 'User',
			   'foreignKey' => 'editedby',
			   'conditions' => '',
			   'fields' => '',
			   'order' => ''
			   ],
		'Torrent' => [
			   'className' => 'Torrent',
			   'foreignKey' => 'torrent',
			   'conditions' => '',
			   'fields' => '',
			   'order' => ''
			   ],
		'Quotation' => [
			      'className' => 'Comment',
			      'foreignKey' => 'quote',
			      'conditions' => '',
			      'fields' => '',
			      'order' => ''
			      ],
	);
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'text';
}
