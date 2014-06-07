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
use Rest\Model\MessageRest;  //  <-- Model imported
use \Exception;

class MessagesController extends BaseController {

    protected $model;
    private $allowedMethod = array('SendPublicMessage');

    /**
     * Implementation of preDispatch() function
     * 
     * Catch the db object and parameters from the query string
     */
    public function preDispatch() {
        parent::preDispatch();

        //  Call the constructor of the model class and initialise the db object
        $this->model = new MessageRest($this->dbObj, $this->oauth);
        try {
            $isValidOauth = $this->getOauth()->isValidOauth($this->getFldOauthToken());
            if (!in_array($this->getMethod(), $this->allowedMethod)) {
                if (!$isValidOauth) {
                    //  Oauth Token matched failed
                    throw new \Exception("Not authenticated");
                }
            }
        } catch (\Exception $e) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->_customPlugin->_codeNumbers['unauthenticated'], 'code_text' => 'unauthenticated'))), TRUE, array('enableJsonExprFinder' => TRUE));
            $this->createLog('errorToken', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
    }

}