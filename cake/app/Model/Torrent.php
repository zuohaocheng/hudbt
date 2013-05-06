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
	public $hasMany = array(
		'Sub' => array(
			'className' => 'Sub',
			'foreignKey' => 'torrent_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
			       ),
		'Snatched' => [
			       'className' => 'Snatched',
			       'foreignKey' => 'torrentid',
			       'dependent' => true,
			       'conditions' => '',
			       'fields' => '',
			       'order' => '',
			       'limit' => '',
			       'offset' => '',
			       'exclusive' => '',
			       'finderQuery' => '',
			       'counterQuery' => ''
			       ],
		'Peer' => [
			       'className' => 'Peer',
			       'foreignKey' => 'torrent',
			       'dependent' => true,
			       'conditions' => '',
			       'fields' => '',
			       'order' => '',
			       'limit' => '',
			       'offset' => '',
			       'exclusive' => '',
			       'finderQuery' => '',
			       'counterQuery' => ''
			   ],
		'File' => [
			       'className' => 'File',
			       'foreignKey' => 'torrent',
			       'dependent' => true,
			       'conditions' => '',
			       'fields' => '',
			       'order' => '',
			       'limit' => '',
			       'offset' => '',
			       'exclusive' => '',
			       'finderQuery' => '',
			       'counterQuery' => ''
			   ],
		/* 'File' => [ */
		/* 	       'className' => 'Comment', */
		/* 	       'foreignKey' => 'torrent', */
		/* 	       'dependent' => false, */
		/* 	       'conditions' => '', */
		/* 	       'fields' => '', */
		/* 	       'order' => '', */
		/* 	       'limit' => '', */
		/* 	       'offset' => '', */
		/* 	       'exclusive' => '', */
		/* 	       'finderQuery' => '', */
		/* 	       'counterQuery' => '' */
		/* 	   ],	 */	
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'owner',
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

	public function beforeDelete() {
	  if (!$this->reason) {
	    return false;
	  }

	  $this->data = $this->read(['name', 'id', 'owner', 'added', 'anonymous'], $this->id); //performance prob?

	  function getUseridFor($type) {
	    return function($v) use ($type) {
	      return $v[$type]['userid'];
	    };
	  };
	  $users_s = $this->Snatched->find('all',
					  ['conditions' => ['Snatched.torrentid' => $this->id,
							    'Snatched.finished' => 'no',
							    'Snatched.userid !=' => $data['Torrent']['owner'],
							    'User.accepttdpms' => 'yes'],
					   'fields' => ['Snatched.userid']]);
	  $users_p = $this->Peer->find('all',
				       ['conditions' => ['Peer.torrent' => $this->id,
							 'Peer.seeder' => 'yes',
							 'Peer.userid !=' => $data['Torrent']['owner'],
							 'User.accepttdpms' => 'yes',],
					'fields' => ['Peer.userid']]);
	  $this->users = array_unique(array_merge(array_map(getUseridFor('Snatched'), $users_s),  array_map(getUseridFor('Peer'), $users_p)));

	  global $CURUSER;
	}

	public function afterDelete() {
	  global $torrent_dir, $CURUSER;
	  $torrent_file = $torrent_dir . '/' . $this->id . '.torrent';
	  if (file_exists($torrent_file)) {
	    unlink();
	  }

	  $data = $this->data;
	  require(get_langfile_path("delete.php",true));

	  //send pm to downloaders & seeders

	  $users = $this->users;
	  foreach ($users as $uid) {
	    $lang = get_user_lang($uid);
	    $subject = $lang_delete_target[$lang]['msg_torrent_deleted'];
	    $msg = sprintf($lang_delete_target[$lang]['msg_torrent_downloaded'], $data['Torrent']['name'] , $CURUSER['id'], $this->reason);

	    send_pm(0, $uid, $subject, $msg);
	  }

	  //Send pm to torrent uploader
	  if ($CURUSER["id"] != $data['Torrent']['owner']){
	    $uid = $data['Torrent']['owner'];
	    $lang = get_user_lang($uid);
	    $subject = $lang_delete_target[$lang]['msg_torrent_deleted'];
	    $msg = sprintf($lang_delete_target[$lang]['msg_torrent_uploaded'], $data['Torrent']['name'] , $CURUSER['id'], $this->reason);
	    send_pm(0, $uid, $subject, $msg);
	  }

	  //deduct bonus
	  global $no_deduct_bonus_on_deletion_torrent;
	  $tadded = strtotime($data['Torrent']['added']);
	  if ((TIMENOW - $tadded) < $no_deduct_bonus_on_deletion_torrent) {
	    global $uploadtorrent_bonus;
	    KPS("-", $uploadtorrent_bonus, $data['Torrent']['owner']);
	  }

	  if ($data['Torrent']['anonymous'] == 'yes' && $CURUSER["id"] == $data['Torrent']["owner"]) {
	    write_log("Torrent " . $this->id . ' (' . $data['Torrent']['name'] . ") was deleted by its anonymous uploader " . $this->reason,'normal');
	  }
	  else {
	    if (isset($this->user)) {
	      $username = 'System';
	    }
	    else {
	      $username = $CURUSER['username'];
	    }
	    write_log("Torrent " . $this->id . ' (' . $data['Torrent']['name'] . ") was deleted by " . $username . $this->reason,'normal');
	  }
	}	  
}
