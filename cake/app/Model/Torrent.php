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

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [
			    'sp_state' => [
					   'rule' => ['between', 1, 7],
					   'message' => 'Invalid sp state.',
					   ],
			    'promotion_time_type' => [
			    			      'rule' => ['range', -1, 3],
			    			      ],
			    /* 'promotion_until' => [ */
			    /* 			  'rule' => ['datetime', '', '/[0-9]{4}(-[0-9]{1,2}){2} ([0-9]{1,2}:){2}[0-9]{1,2}/'], */
			    /* 			  ], */
			    'pos_state' => [
			    	       'rule' => ['inList', ['normal', 'sticky']],
						  ],
			    'oday' => [
			    	       'rule' => ['inList', ['yes', 'no']],
			    	       ],
			    ];

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	/* public $hasMany = array( */
	/* 	'Sub' => array( */
	/* 		'className' => 'Sub', */
	/* 		'foreignKey' => 'torrent_id', */
	/* 		'dependent' => false, */
	/* 		'conditions' => '', */
	/* 		'fields' => '', */
	/* 		'order' => '', */
	/* 		'limit' => '', */
	/* 		'offset' => '', */
	/* 		'exclusive' => '', */
	/* 		'finderQuery' => '', */
	/* 		'counterQuery' => '' */
	/* 	) */
	/* ); */

	/* public $belongsTo = array( */
	/* 	'User' => array( */
	/* 		'className' => 'User', */
	/* 		'foreignKey' => 'owner', */
	/* 		'conditions' => '', */
	/* 		'fields' => '', */
	/* 		'order' => '' */
	/* 	) */
	/* ); */


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
