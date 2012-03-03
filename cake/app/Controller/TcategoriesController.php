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

$s = smarty();
$s->addTemplateDir('../../../templates');
$s->setCompileDir('../../../templates_c');
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
		$this->set('tcategories', $this->paginate());
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
            'type' => 'inner',  
            'conditions'=> [ 
                'Tcategory.id = TcategoriesTorrent.tcategory_id', 
                'Tcategory.id' => $id 
			     ],
	  ]],
				   'fields' => ['id', 'name']
				   ]];

		$torrents = $this->paginate('Torrent');

		$tcategory = $this->Tcategory->read(null, $id);
		$this->set(['tcategory' => $tcategory,
			    'torrents' => $torrents]);
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
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Tcategory->save($this->request->data)) {
				$this->Session->setFlash(__('The tcategory has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tcategory could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Tcategory->read(null, $id);
		}
		$redirectTos = $this->Tcategory->RedirectTo->find('list');
		$parents = $this->Tcategory->Parent->find('list');
		$torrents = $this->Tcategory->Torrent->find('list');
		$tcategory = $this->request->data;
		$this->set(compact('redirectTos', 'parents', 'torrents', 'tcategory'));
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
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
	  $this->set(compact('tcategories'));
	  $this->set('_serialize', 'tcategories');
	}
}
