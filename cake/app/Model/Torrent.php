<?php
App::uses('AppModel', 'Model');
/**
 * Torrent Model
 *
 * @property Sub $Sub
 * @property Tcategory $Tcategory
 */
class Torrent extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Sub' => array(
			'className' => 'Sub',
			'foreignKey' => 'torrent_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Tcategory' => array(
			'className' => 'Tcategory',
			'joinTable' => 'torrents_tcategories',
			'foreignKey' => 'torrent_id',
			'associationForeignKey' => 'tcategory_id',
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
