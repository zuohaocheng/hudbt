<?php
App::uses('AppController', 'Controller');
dbconn();
loggedinorreturn();
parked();

/**
 * Tcategories Controller
 *
 * @property Tcategory $Tcategory
 */

dbconn();
loggedinorreturn();
parked();

class TcategoriesController extends AppController {

  public $components = array('RequestHandler');
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Tcategory->recursive = 0;
		$this->set(['tcategories' => $this->paginate(),
			    'canLock' => checkPrivilege(['Tcategory', 'lock']),
			    'canDelete' => checkPrivilege(['Tcategory', 'delete']),
			    ]);
	}


/**
 * view method
 *
 * @param string $id
 * @return void
 */

	public function view($id = null) {
		$this->Tcategory->id = $id;
		if (!$this->Tcategory->exists()) {
			throw new NotFoundException(__('Invalid tcategory'));
		}

		$tcategory = $this->Tcategory->read(null, $id);

		if(!array_key_exists('noredirect', $this->request->params['named']) && $tcategory['Tcategory']['redirect_to_id']) {
		  $this->redirect(array('action' => 'view', $tcategory['Tcategory']['redirect_to_id']));
		}

		$this->paginate = ['Torrent' => ['joins' => [ 
        [ 
            'table' => 'torrents_tcategories', 
            'alias' => 'TcategoriesTorrent', 
            'type' => 'inner',  
            'conditions'=> ['TcategoriesTorrent.torrent_id = Torrent.id']
        ], 
        [ 
            'table' => 'tcategories', 
            'alias' => 'Tcategory', 
            'type' => 'INNER',  
            'conditions'=> [ 
                'Tcategory.id = TcategoriesTorrent.tcategory_id', 
                'Tcategory.id' => $id 
			     ],
	  ]],
				   'fields' => ['id', 'name']
				   ]];

		$torrents = $this->paginate('Torrent');

		$this->set(['tcategory' => $tcategory,
			    'torrents' => $torrents,
			    'canEdit' => !$tcategory['Tcategory']['locked'] || checkPrivilege(['Tcategory', 'lock']),
			    'canDelete' => checkPrivilege(['Tcategory', 'delete']),
			    ]);
		$this->set('_serialize', 'tcategory');
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Tcategory->create();
			if ($this->Tcategory->save($this->request->data)) {
				$this->Session->setFlash(__('The tcategory has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tcategory could not be saved. Please, try again.'));
			}
		}
		$redirectTos = $this->Tcategory->RedirectTo->find('list');
		$parents = $this->Tcategory->Parent->find('list');
		$torrents = $this->Tcategory->Torrent->find('list');
		$this->set(compact('redirectTos', 'parents', 'torrents'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Tcategory->id = $id;
		if (!$this->Tcategory->exists()) {
			throw new NotFoundException(__('Invalid tcategory'));
		}

		$tcategory = $this->Tcategory->read(null, $id);
		$canLock = checkPrivilege(['Tcategory', 'lock']);
		$canEdit = !$tcategory['Tcategory']['locked'] || $canLock;
		if (!$canEdit) {
		  $this->redirect(array('action' => 'view', $id));
		}
		elseif ($this->request->is('post') || $this->request->is('put')) {
		  $data = $this->request->data;
		  if (!$canLock) {
		    unset($data['Tcategory']['locked']);
		  }

			if ($this->Tcategory->save($data)) {
				$this->Session->setFlash(__('The tcategory has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tcategory could not be saved. Please, try again.'));
			}
		}
		else {
		  $this->request->data = $tcategory;
		}
		
		$redirectTos = $this->Tcategory->RedirectTo->find('list');
		$parents = $this->Tcategory->Parent->find('list');
		$torrents = $this->Tcategory->Torrent->find('list');
		$tcategory = $this->request->data;
		$canDelete = checkPrivilege(['Tcategory', 'delete']);
		$this->set(compact('redirectTos', 'parents', 'torrents', 'tcategory', 'canLock', 'canDelete'));
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
	  if (!checkPrivilege(['Tcategory', 'delete'])) {
	    $this->redirect(array('action' => 'view', $id));
	  }
	  
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Tcategory->id = $id;
		if (!$this->Tcategory->exists()) {
			throw new NotFoundException(__('Invalid tcategory'));
		}
		if ($this->Tcategory->delete()) {
			$this->Session->setFlash(__('Tcategory deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Tcategory was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

	public function search($text = '', $format = 'html') {
	  
	  if (!$text) {
	    $text = $_REQUEST['term'];
	    if (!$text) {
	      $this->redirect(array('action' => 'index'));
	    }
	    else {
	      $this->redirect(array('action' => 'search', $text));
	    }
	  }

	  if (array_key_exists('exact', $this->request->params['named'])) {
	    $this->paginate = array('conditions' => array('Tcategory.name LIKE' => $text));
	  }
	  else {
	    $this->paginate = array('conditions' => array('Tcategory.name LIKE' => '%' . $text . '%'));
	  }
	  $tcategories = $this->paginate('Tcategory');
	  $this->set(['tcategories' => $tcategories,
		      'canLock' => checkPrivilege(['Tcategory', 'lock']),
		      'canDelete' => checkPrivilege(['Tcategory', 'delete']),
		      ]);
	  $this->set('_serialize', 'tcategories');
	}
}
