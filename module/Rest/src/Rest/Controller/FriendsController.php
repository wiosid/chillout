<?php

/**
 * Finoit Technologies
 * 
 * o Contributor: * o Ramakant Gangwar, Finoit Technologies (gangwar.ramji@gmail.com).
 * 
 *   Rest Api integration
 *   GET PUT POST DELETE rest api method integrated
 * 
 * Api Secret read from header
 * 
 * Oauth Token read from header
 * 
 * @return Json
 * Json data returned based on different request
 */

namespace Rest\Controller;

use Rest\Controller\BaseController;
use Zend\View\Model\JsonModel;
use Rest\Model\OAuthRest;  //  <-- Model imported
use Rest\Model\FriendRest;  //  <-- Model imported

class FriendsController extends BaseController {

    protected $model;
    private $allowedMethod = array();

    /**
     * Implementation of preDispatch() function
     * 
     * Catch the db object and parameters from the query string
     */
    public function preDispatch() {
        $this->setOauth(new OAuthRest($this->getServiceLocator()->get('db')));
        //  Call the constructor of the model class and initialise the db object
        $friendRestModel = new FriendRest($this->getServiceLocator()->get('db'), $this->getOauth());
        $friendRestModel->setFriendTable($this->getServiceLocator()->get('Models\Model\FriendsTable'));
        $friendRestModel->setUserTable($this->getServiceLocator()->get('Models\Model\UsersTable'));
        $friendRestModel->setHitTable($this->getServiceLocator()->get('Models\Model\HitsTable'));
        $friendRestModel->setArchiveMessageTable($this->getServiceLocator()->get('Models\Model\ArchiveMessagesTable'));
        $friendRestModel->setArchiveCollectionTable($this->getServiceLocator()->get('Models\Model\ArchiveCollectionsTable'));
        $friendRestModel->setBlockUserTable($this->getServiceLocator()->get('Models\Model\BlockUsersTable'));
        $friendRestModel->setPhotoTable($this->getServiceLocator()->get('Models\Model\PhotosTable'));
        $this->setModel($friendRestModel);
        parent::preDispatch();

        try {
            $isValidOauth = $this->getOauth()->isValidOauth($this->getFldOauthToken());
            if (!in_array($this->getMethod(), $this->allowedMethod)) {
                if (!$isValidOauth) {
                    //  Oauth Token matched failed
                    throw new \Exception("Not authenticated");
                }
            }
        } catch (\Exception $e) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('unauthenticated'), 'code_text' => 'unauthenticated'))), TRUE, array('enableJsonExprFinder' => TRUE));
            $this->createLog('errorToken', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
    }

}
