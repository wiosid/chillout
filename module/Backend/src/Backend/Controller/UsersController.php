<?php

namespace Backend\Controller;

use ZfcRbac\Collector\RbacCollector;
use ZfcUser\View\Helper\ZfcUserDisplayName;
use Models\Model\UsersTable;
use Models\Model\ArchiveMessagesTable;
use Models\Model\FriendsTable;
use Models\Model\HitsTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Backend\Form\UsersForm;
use Models\Model\User;
use ZfcRbac\Service\Rbac;
use Zend\Crypt\Password\Bcrypt;

class UsersController extends AbstractActionController {

    protected $usersTable;
    protected $archiveMessageTable;
    protected $friendTable;
    protected $hitTable;
    protected $loggedInUserId;

    /**
     * List Users
     */
    public function indexAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }
        $users = $this->getUsersTable()->totalUsersCount();
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $msg = $flashMessenger->getMessages();
        }

        return new ViewModel(
                array(
            'users' => $users,
            'todayActiveUsersCount' => $this->getUsersTable()->todayActiveUsersCount(),
            'todayRegisteredUsersCount' => $this->getUsersTable()->todayRegisteredUsersCount(),
            'hitsCount' => $this->getHitTable()->totalCount(),
            'friendsCount' => $this->getFriendTable()->totalCount(),
            'messagesCount' => $this->getArchiveMessageTable()->totalCount(),
            'messages' => $msg
                )
        );
    }

    public function testAction() {
        $connectedUsers = my_exec('/opt/ejabberd-2.1.13/bin/ejabberdctl  --node ejabberd@localhost connected-users-number');
        _pre($connectedUsers);
    }

    public function listAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }


        $tblUserTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $users = $tblUserTable->fetchAll();

        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $msg = $flashMessenger->getMessages();
        }

        return new ViewModel(array('users' => $users, 'messages' => $msg));
    }

    public function addAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        if (!$this->isGranted('user.add')) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/unauth');
            return $viewModel;
        }

        $formManager = $this->serviceLocator->get('FormElementManager');
        $form = $formManager->get('\Backend\Form\UsersForm');
        $form->remove('old_password');
        $form->setValidationGroup(array('user' => array('fld_name', 'email', 'password', 'confirm_password')));

        $user = new User();

        $form->bind($user);

        $request = $this->getRequest();

        $post = $request->getPost();

        $form->setData($post);

        if ($request->isPost()) {
            $user->exchangeArray($post->user);

            if ($form->isValid()) {
                $id = $this->getUsersTable()->saveUser($user);
                $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'User added successfully.'));
                return $this->redirect()->toUrl(BASE_URL . "/admin/users/list");
            }
        }
        return new ViewModel(array('form' => $form));
    }

    public function editAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        $userId = (int) $this->params()->fromRoute('id', 0);
        $roleDisabled = true;

        if (!$loggedInUserId = $this->getLoggedInUserId()) {
            $this->redirect()->toUrl(BASE_URL . '/user/login');
        }



        if (!$userId) {
            return $this->redirect()->toUrl(BASE_URL . "/admin/users/add");
        }

        try {
            $user = $this->getUsersTable()->getUserById($userId);
        } catch (\Exception $ex) {
            return $this->redirect()->toUrl(BASE_URL . "/admin/users/add");
        }


        $formManager = $this->serviceLocator->get('FormElementManager');
        $form = $formManager->get('\Backend\Form\UsersForm');
        $form->get('user')->setUserId($userId);
        //die($uid);
        if ($roleDisabled) {
            $form->remove('roles');
        }
        $form->remove('phone');
        $user = new User();
        $form->bind($user);

        $form->setAttribute('action', BASE_URL . '/admin/users/edit/' . $userId);

        $request = $this->getRequest();
        $post = $request->getPost();



        if ($request->isPost()) {



            if (empty($post->user['password']) || empty($post->user['old_password'])) {
                $form->setValidationGroup(array('user' => array('fld_name', 'email')));
            } else {
                $form->setValidationGroup(array('user' => array('fld_name', 'email', 'password', 'confirm_password')));
            }

            $form->setData($post);
            $user->exchangeArray($post->user);

            if (!empty($post->user['old_password'])) {
                $oldPassword = $post->user['old_password'];
                $password = $this->zfcUserAuthentication()->getIdentity()->getPassword();
                $bcrypt = new Bcrypt;
                $bcrypt->setCost();
                if (!$bcrypt->verify($oldPassword, $password)) {
                    $errors['old_password'] = 'Old password not matched';
                }
            }

            if ($form->isValid() && empty($errors)) {
                $user->setId($userId);
                $id = $this->getUsersTable()->save($user);
                $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'User updated successfully.'));
                return $this->redirect()->toUrl(BASE_URL . "/admin/users/list");
            }
        } else {

            $flashMessenger = $this->flashMessenger();
            if ($flashMessenger->hasMessages()) {
                $msg = $flashMessenger->getMessages();
            }

            $form->setData($post);
            $userFieldset = $form->get('user');
            $user = new User();

            $userTable = $this->getServiceLocator()->get('Models\Model\UsersTable');


            $user = $userTable->getUserById($userId);
//            _pre($user);
            $form->setData(array('user' => $user->getArrayCopy()));
        }

        return new ViewModel(array('form' => $form, 'roleDisabled' => $roleDisabled, 'messages' => $msg, 'errors' => $errors));
    }

    public function deleteAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        $userId = (int) $this->params()->fromRoute('id', 0);
        $userTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $userData = (array) $userTable->getUserById($userId);
        $this->getServiceLocator()->get('Models\Model\ArchiveMessagesTable');
        $userCollectionIds = (array) $this->getServiceLocator()->get('Models\Model\ArchiveCollectionsTable')->getUserAllCollectionIds($userData['username']);
        $this->getServiceLocator()->get('Models\Model\ArchiveMessagesTable')->deleteCollectionMessages($userCollectionIds);
        $this->getServiceLocator()->get('Models\Model\ArchiveCollectionsTable')->deleteUserAllCollectionIds($userData['username']);


        $userTable->deleteUser($userId);

        $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'User deleted successfully.'));

        return $this->redirect()->toUrl(BASE_URL . '/admin/users/list');
    }

    public function blockAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        $userId = (int) $this->params()->fromRoute('id', 0);

        $userTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $userTable->blockUser($userId);

        $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'User blocked successfully.'));

        return $this->redirect()->toUrl(BASE_URL . '/admin/users/list');
    }

    public function unblockAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }


        $userId = (int) $this->params()->fromRoute('id', 0);

        $userTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $userTable->unblockUser($userId);

        $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'User unblocked successfully.'));

        return $this->redirect()->toUrl(BASE_URL . '/admin/users/list');
    }

    public function addRoleAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        if (!$this->isGranted('role.add')) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/unauth');
            return $viewModel;
        }

        $request = $this->getRequest();
        $post = $request->getPost();
        if ($request->isPost()) {
            $tblUserTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
            $data = array(
                'roleId' => '',
                'roleName' => $request->getPost('roleName'),
                'parentRoleId' => $request->getPost('parentRoleId'),
                'permissions' => $request->getPost('permissions')
            );

            $result = $tblUserTable->saveRole($data);

            if ($result['status'] == 'OK') {
                $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'Role added successfully.'));
                $this->redirect()->toUrl(BASE_URL . '/admin/users/roles');
            }
        }
        return new ViewModel(
                array(
            'data' => $data,
            'result' => $result
                )
        );
    }

    public function editRoleAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        if (!$this->isGranted('role.update')) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/unauth');
            return $viewModel;
        }

        $roleId = (int) $this->params()->fromRoute('id', 0);

        $request = $this->getRequest();
        $post = $request->getPost();
        $tblUserTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        if ($request->isPost()) {
            $data = array(
                'roleId' => $roleId,
                'roleName' => $request->getPost('roleName'),
                'parentRoleId' => $request->getPost('parentRoleId'),
                'permissions' => $request->getPost('permissions')
            );
            $result = $tblUserTable->saveRole($data);
            if ($result['status'] == 'OK') {
                $this->flashMessenger()->addMessage(array('status' => 'OK', 'message' => 'Role updated successfully.'));
                $this->redirect()->toUrl(BASE_URL . '/admin/users/roles');
            }
        } else {
            $data = $tblUserTable->getRoleById($roleId);
            $data['permissions'] = array_flip($data['permissions']);
        }

        return new ViewModel(
                array(
            'roleId' => $roleId,
            'data' => $data,
            'result' => $result
                )
        );
    }

    public function deleteRoleAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        if (!$this->isGranted('role.delete')) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/unauth');
            return $viewModel;
        }

        $roleId = (int) $this->params()->fromRoute('id', 0);
        $tblUserTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $msg = $tblUserTable->deleteRole($roleId);
        $this->flashMessenger()->addMessage($msg);
        return $this->redirect()->toUrl(BASE_URL . '/admin/users/roles');
    }

    public function rolesAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        $tblUserTable = $this->getServiceLocator()->get('Models\Model\UsersTable');
        $roles = $tblUserTable->getAllRoles();

        if (!($this->isGranted('role.add') || $this->isGranted('role.update') || $this->isGranted('role.delete'))) {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('error/unauth');
            return $viewModel;
        }

        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $msg = $flashMessenger->getMessages();
        }

        return new ViewModel(array('roles' => $roles, 'messages' => $msg));
    }

    /**
     * Return UsersTable Model
     *
     * @return UsersTable
     */
    public function getUsersTable() {
        if (!$this->usersTable) {
            $sm = $this->getServiceLocator();
            $this->usersTable = $sm->get('Models\Model\UsersTable');
        }
        return $this->usersTable;
    }

    /**
     * Return ArchiveMessagesTable Model
     *
     * @return ArchiveMessagesTable
     */
    public function getArchiveMessageTable() {
        if (!$this->archiveMessageTable) {
            $sm = $this->getServiceLocator();
            $this->archiveMessageTable = $sm->get('Models\Model\ArchiveMessagesTable');
        }
        return $this->archiveMessageTable;
    }

    /**
     * Return FriendsTable Model
     *
     * @return FriendsTable
     */
    public function getFriendTable() {
        if (!$this->friendTable) {
            $sm = $this->getServiceLocator();
            $this->friendTable = $sm->get('Models\Model\FriendsTable');
        }
        return $this->friendTable;
    }

    /**
     * Return HitsTable Model
     *
     * @return HitsTable
     */
    public function getHitTable() {
        if (!$this->hitTable) {
            $sm = $this->getServiceLocator();
            $this->hitTable = $sm->get('Models\Model\HitsTable');
        }
        return $this->hitTable;
    }

    protected function isUserLoggedIn() {
        return $this->zfcUserAuthentication()->hasIdentity();
    }

    protected function getLoggedInUserId() {
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->zfcUserAuthentication()->getIdentity()->getId();
        } else {
            return false;
        }
    }

}
