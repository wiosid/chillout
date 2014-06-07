<?php

namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Models\Model\UsersTable;

class IndexController extends AbstractActionController {

    protected $userTable;
    /**
     * Return UserTable Model
     *
     * @return UsersTable
     */
    public function getUserTable() {
        return $this->getServiceLocator()->get('Models\Model\UsersTable');
    }
    protected function attachDefaultListeners() {
        parent::attachDefaultListeners();
        $events = $this->getEventManager();
        $this->events->attach('dispatch', array($this, 'preDispatch'), 100);
        //$this->events->attach('dispatch', array($this, 'postDispatch'), -100);
    }

    public function preDispatch() {

        die('api setup succesfully');
    }

    public function indexAction() {
        die('api setup succesfully');
    }

    public function verifyCodeAction() {
        $code = $this->getRequest()->getQuery('code');
        if ($fldUserId = $this->getUserTable()->verifyVerificationCode($code)) {
            $userProfileData['id'] = $fldUserId;
            $userProfileData['fld_verification_code'] = "";
            $userProfileData['fld_status'] = 1;
            $this->getUserTable()->saveUser($userProfileData);
            $message = "Your email is verified successfully.";
        } else {
            $message = "Invalid verification code.";
        }

        return new ViewModel(array('message' => $message));
    }

}
