<?php
App::uses('AppController', 'Controller');

loggedinorreturn();
checkPrivilegePanel('wechatreplies');
/**
 * WechatReplies Controller
 *
 * @property WechatReply $WechatReply
 */
class WechatRepliesController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->WechatReply->recursive = 0;
		$this->set('wechatReplies', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->WechatReply->exists($id)) {
			throw new NotFoundException(__('Invalid wechat reply'));
		}
		$options = array('conditions' => array('WechatReply.' . $this->WechatReply->primaryKey => $id));
		$this->set('wechatReply', $this->WechatReply->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->WechatReply->create();
			if (!$this->request->data["WechatReply"]['string']) {
			  $this->request->data["WechatReply"]['regexp'] = '/' . preg_quote($this->request->data["WechatReply"]['regexp'], '/') . '/i';
			}

			if (preg_match($this->request->data["WechatReply"]['regexp'], '') === false) {
			  throw new BadRequestException('Error in regexp "'. $this->request->data["WechatReply"]['regexp'] . '"');
			}
			else if ($this->WechatReply->save($this->request->data)) {
			  global $Cache;
			  $Cache->delete_value('wechat_autoreply');
				$this->flash(__('Wechatreply saved.'), array('action' => 'index'));
			} else {
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->WechatReply->exists($id)) {
			throw new NotFoundException(__('Invalid wechat reply'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
		  if (!$this->request->data["WechatReply"]['string']) {
		    $this->request->data["WechatReply"]['regexp'] = '/' . preg_quote($this->request->data["WechatReply"]['regexp'], '/') . '/i';
		  }

		  if (preg_match($this->request->data["WechatReply"]['regexp'], '') === false) {
		    throw new BadRequestException('Error in regexp "'. $this->request->data["WechatReply"]['regexp'] . '"');
		  }
		  else if ($this->WechatReply->save($this->request->data)) {
		    global $Cache;
		    $Cache->delete_value('wechat_autoreply');

		    $this->flash(__('The wechat reply has been saved.'), array('action' => 'index'));
		  } else {
		  }
		} else {
			$options = array('conditions' => array('WechatReply.' . $this->WechatReply->primaryKey => $id));
			$this->request->data = $this->WechatReply->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->WechatReply->id = $id;
		if (!$this->WechatReply->exists()) {
			throw new NotFoundException(__('Invalid wechat reply'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->WechatReply->delete()) {
		  global $Cache;
		  $Cache->delete_value('wechat_autoreply');

			$this->flash(__('Wechat reply deleted'), array('action' => 'index'));
		}
		$this->flash(__('Wechat reply was not deleted'), array('action' => 'index'));
		$this->redirect(array('action' => 'index'));
	}
}
