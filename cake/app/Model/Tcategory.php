<?php
App::uses('AppModel', 'Model');
/**
 * Tcategory Model
 *
 * @property RedirectTo $RedirectTo
 * @property Tcategory $Tcategory
 * @property Torrent $Torrent
 */
class Tcategory extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'RedirectTo' => array(
			'className' => 'Tcategory',
			'foreignKey' => 'redirect_to_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Parent' => array(
			'className' => 'Tcategory',
			'joinTable' => 'tcategories_tcategories',
			'foreignKey' => 'tcategory_id',
			'associationForeignKey' => 'parent_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		),
		'Torrent' => array(
			'className' => 'Torrent',
			'joinTable' => 'torrents_tcategories',
			'foreignKey' => 'tcategory_id',
			'associationForeignKey' => 'torrent_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

}
