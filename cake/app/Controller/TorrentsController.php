<?php
App::uses('AppController', 'Controller');
dbconn();
loggedinorreturn();
parked();
/**
 * Torrents Controller
 *
 * @property Torrent $Torrent
 * @property RequestHandlerComponent $RequestHandler
 */
class TorrentsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('RequestHandler');

/**
 * index method
 *
 * @return void
 */
	/* public function index() { */
	/* 	$this->Torrent->recursive = 0; */
	/* 	$this->set('torrents', $this->paginate()); */
	/* } */

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Torrent->id = $id;
		if (!$this->Torrent->exists()) {
			throw new NotFoundException(__('Invalid torrent'));
		}
		$torrent = $this->Torrent->read(['id', 'name', 'promotion_time_type', 'promotion_until', 'sp_state', 'pos_state', 'picktype', 'oday', 'pos_state_until'], $id);
#		echo h(json_encode($torrent));
		$this->set('torrent', $torrent);
		$this->set('_serialize', 'torrent');
	}

/**
 * add method
 *
 * @return void
 */
/*	public function add() {
		if ($this->request->is('post')) {
			$this->Torrent->create();
			if ($this->Torrent->save($this->request->data)) {
				$this->Session->setFlash(__('The torrent has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The torrent could not be saved. Please, try again.'));
			}
		}
		$tcategories = $this->Torrent->Tcategory->find('list');
		$this->set(compact('tcategories'));
		}*/

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Torrent->id = $id;
		if (!$this->Torrent->exists()) {
			throw new NotFoundException(__('Invalid torrent'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		  $data = $this->request->data;
		  $d = ['Torrent' => ['id' => $data['Torrent']['id']],
			'Tcategory' => ['Tcategory' => $data['Tcategory']['Tcategory']]];
		  $catEdit = isset($data['Tcategory']['Tcategory']);

		  $keys = [];
		  $modEdit = false;
		  if (checkPrivilege(['Torrent', 'oday'])) {
		    $keys[] = 'oday';
		  }

		  if (checkPrivilege(['Torrent', 'pr'])) {
		    array_push($keys, 'sp_state', 'promotion_time_type', 'promotion_until');
		  }

		  if (checkPrivilege(['Torrent', 'sticky'])) {
		    array_push($keys, 'pos_state', 'pos_state_until');
		  }

		  foreach ($keys as $key) {
		    if (isset($data['Torrent'][$key])) {
		      $modEdit = true;
		      $d['Torrent'][$key] = $data['Torrent'][$key];
		    }
		  }

			if ($this->Torrent->save($d)) {
			  $data = $this->Torrent->read(null, $id);
			  global $CURUSER;
			  if ($modEdit) {
			    write_log("Torrent " . $this->Torrent->id . ' (' . $data['Torrent']['name'] . ") was edited by " . $CURUSER['username'] . ", Promotion Mod Edit", 'normal');
			  }
			  if ($catEdit) {
			    write_log("Torrent " . $this->Torrent->id . ' (' . $data['Torrent']['name'] . ") was edited by " . $CURUSER['username'] . ", Category Edit", 'normal');
			  }


			  $tcategories = [];
			  foreach ($data['Tcategory'] as $tc) {
			    $tc = $this->Torrent->Tcategory->read(null, $tc['id'])['Tcategory'];
			    $tcategories[] = ['id' => $tc['id'], 'name' => $tc['name'], 'showName' => $tc['showName'], 'hidden' => $tc['hidden']];
			  }
			  
			  $result = ['success' => true, 'messgae' => __('The torrent has been saved'), 'tcategories' =>$tcategories];
			  $this->Session->setFlash(__('The torrent has been saved'));
#			  $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The torrent could not be saved. Please, try again.'));
				$result = ['success' => false, 'message' => __('The torrent could not be saved. Please, try again.')];
			}
		} else {
			$this->request->data = $this->Torrent->read(null, $id);
			$result = ['success' => false, 'messgae' => __('GET method not allowed')];
		}
		$tcategories = $this->Torrent->Tcategory->find('list');
		$this->set(['tcategories' => $tcategories,
			    'result' => $result]);

		$this->set('_serialize', 'result');
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
	  global $CURUSER, $self_deletion_before_torrent;
	  include(get_langfile_path('delete.php'));
	  if (!$this->request->is('delete')) {
	    throw new MethodNotAllowedException();
	  }
	  $this->Torrent->id = $id;
	  if (!$this->Torrent->exists()) {
	    throw new NotFoundException(__('Invalid torrent'));
	  }

	  $data = $this->request->data;
	  if (!checkPrivilege(['Torrent', 'delete'], $id)) {
	    $result = ['success' => false, 'message' => __('You can\'t delete this torrent.')];
	  }

	  $rt = 0 + $data["reasonType"];

	  if (!is_int($rt) || $rt < 0 || $rt > 4) {
	    $result = ['success' => false, 'message' => $lang_delete['std_invalid_reason']."$rt."];	  
	  }
	  else {
	    if (isset($data["reasonDetail"])) {
	      $reason = trim($data["reasonDetail"]);
	      if ($reason == '') {
		unset($reason);
	      }
	    }

	    if ($rt == 0) {
	      $reasonstr = "Dead: 0 seeders, 0 leechers = 0 peers total";
	    }
	    elseif ($rt == 1) {
	      $reasonstr = "Dupe" . (isset($reason) ? (": " . $reason) : "!");
	    }
	    elseif ($rt == 2) {
	      $reasonstr = "Nuked" . (isset($reason) ? (": " . $reason) : "!");
	    }
	    elseif ($rt == 3) {
	      if (!isset($reason)) {
		$result = ['success' => false, 'message' => $lang_delete['std_describe_violated_rule']."$rt."];
	      }
	      $reasonstr = $SITENAME." rules broken: " . $reason;
	    }
	    else {
	      if (!isset($reason)) {
		$result = ['success' => false, 'message' => $lang_delete['std_enter_reason']."$rt."];
	      }
	      $reasonstr = $reason;
	    }
	    $this->Torrent->reason = $reasonstr;
	  }

	  if (!isset($result)) {
	    if ($this->Torrent->delete()) {
	      #	    $this->Session->setFlash(__('Torrent deleted'));
	      #	    $this->redirect(array('action' => 'index'));
	      $result = ['success' => true, 'message' => __('Torrent deleted')];
	    }
	    else {
	      $result = ['success' => false, 'message' => __('Torrent was not deleted')];
	      /* $this->Session->setFlash(__('Torrent was not deleted')); */
	      /* $this->redirect(array('action' => 'index')); */
	    }
	  }
	  $this->set(compact('result'));
	  $this->set('_serialize', 'result');
	}
}
