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

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use \Exception;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class BaseController extends AbstractRestfulController {

    protected $db;
    protected $model;
    protected $method;
    protected $length;
    protected $datetime;
    protected $direction;
    protected $query;
    protected $page;
    protected $token;
    protected $secretKey;
    protected $fldOauthToken;
    protected $apiSecretKeyVal = 'krishna@123'; //  Api Secret Key defined
    protected $oauth;

    /**
     * Listeners defined attachDefaultListeners() for
     * calling predispatch and postdispatch
     */
    protected function attachDefaultListeners() {
        parent::attachDefaultListeners();
        $events = $this->getEventManager();
        $this->events->attach('dispatch', array($this, 'preDispatch'), 100);
        //$this->events->attach('dispatch', array($this, 'postDispatch'), -100);
    }

    public function getDb() {
        return $this->db;
    }

    public function setDb(Zend\Db\Adapter\Adapter $db) {
        $this->db = $db;
    }

    public function getModel() {
        return $this->model;
    }

    public function setModel($model) {
        $this->model = $model;
    }

    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getLength() {
        return $this->length;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function getDatetime() {
        return $this->datetime;
    }

    public function setDatetime($datetime) {
        $this->datetime = $datetime;
    }

    public function getDirection() {
        return $this->direction;
    }

    public function setDirection($direction) {
        $this->direction = $direction;
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery($query) {
        $this->query = $query;
    }

    public function getPage() {
        return $this->page;
    }

    public function setPage($page) {
        $this->page = $page;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function getSecretKey() {
        return $this->secretKey;
    }

    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
    }

    public function getFldOauthToken() {
        return $this->fldOauthToken;
    }

    public function setFldOauthToken($fldOauthToken) {
        $this->fldOauthToken = $fldOauthToken;
    }

    public function getApiSecretKeyVal() {
        return $this->apiSecretKeyVal;
    }

    public function getOauth() {
        return $this->oauth;
    }

    public function setOauth($oauth) {
        $this->oauth = $oauth;
    }

    /**
     * Implementation of preDispatch() function
     * 
     * Catch the db object and parameters from the query string
     */
    public function preDispatch() {

        //  catch the query string data
        $filter = new \Zend\Filter\Word\DashToCamelCase();
        $this->setMethod($filter->filter($this->params('method')));
        $this->setQuery($this->getRequest()->getQuery()->toArray());

        //  get the headers
        $headerHTTP = $this->getRequest()->getHeaders()->toArray();

        $this->setSecretKey($headerHTTP['Apikey']);

        $this->setFldOauthToken($headerHTTP['Fld-Oauth-Token']);

        try {
            if ($this->getSecretKey() != $this->getApiSecretKeyVal()) {
                //  Api secret key matched failed
                throw new \Exception("Wrong Api secret key.");
            }
        } catch (\Exception $e) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception'))), TRUE, array('enableJsonExprFinder' => TRUE));
            $this->createLog('errorApiKey', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
    }

    /**
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function getList() {
        $methodStatus = is_callable(array($this->getModel(), 'g' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorGL', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }

        $param = '';
        //  Model called
        try {
            $data = $this->getModel()->{'g' . $this->getMethod()}($param, $this->getQuery());
//        if (!isset($data['data'])) {
            $this->createLog('getList', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
//        }
        } catch (\Exception $e) {
            $data = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($data);
    }

    public function notFoundAction() {
        print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Invalid Url.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
        $this->createLog('errorNF', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
        exit();
    }

    /**
     * 
     * @param type $id
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id) {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'g' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorG', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
        //  Model called
        try {
            //  Model called
            $data = $model->{'g' . $this->getMethod()}($id, $this->getQuery());
//            if (!isset($data['data'])) {
            $this->createLog('get', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
//            }
        } catch (\Exception $e) {
            $data = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
        }

        return new JsonModel($data);
    }

    /**
     * POST
     * 
     * @param type $data
     * @return \Zend\View\Model\JsonModel
     * 
     */
    public function create($data) {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'c' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorC', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
        $post = array_merge_recursive(
                $data, $this->getRequest()->getFiles()->toArray()
        );
        try {
            //  Model called
            $response = $model->{'c' . $this->getMethod()}($post, $this->getQuery());
//            if (!isset($response['data'])) {
            $this->createLog('create', \Zend\Json\Json::encode($post, TRUE, array('enableJsonExprFinder' => TRUE)), \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
//            }
        } catch (\Exception $e) {
            $response = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($response);
    }

    /**
     * PUT
     * 
     * @param type $id
     * @param type $data
     * @return \Zend\View\Model\JsonModel
     * 
     */
    public function update($id, $data) {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'u' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorU', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }

        $tmpData['id'] = $id;
        $tmpData['postVal'] = $data;
        //  Model called
        try {
            $response = $model->{'u' . $this->getMethod()}($tmpData, $this->getQuery());
//        if (!isset($response['data'])) {
            $this->createLog('update', \Zend\Json\Json::encode($tmpData, TRUE, array('enableJsonExprFinder' => TRUE)), \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
//        }
        } catch (\Exception $e) {
            $response = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($response);
    }

    /**
     * REPLACE LIST
     * 
     * @param type $data
     * @return \Zend\View\Model\JsonModel
     * 
     */
    public function replaceList($post) {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'u' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorRL', '', \Zend\Json\Json::encode($data, TRUE, array('enableJsonExprFinder' => TRUE)));
            exit();
        }
        try {
            //  Model called
            $response = $model->{'u' . $this->getMethod()}($post, $this->getQuery());
//            if (!isset($response['data'])) {
            $this->createLog('replaceList', \Zend\Json\Json::encode($post, TRUE, array('enableJsonExprFinder' => TRUE)), \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
//            }
        } catch (\Exception $e) {
            $response = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($response);
    }

    /**
     * DELETE
     * 
     * @param type $id
     * @return \Zend\View\Model\JsonModel
     *  
     */
    public function delete($id) {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'd' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorD', '', \Zend\Json\Json::encode($data, TRUE));
            exit();
        }
        //  Model called
        try {
            $response = $model->{'d' . $this->getMethod()}($id, $this->getQuery());
//        if (!isset($response['data'])) {
            $this->createLog('delete', 'id=' . $id, \Zend\Json\Json::encode($response, TRUE));
//        }
        } catch (\Exception $e) {
            $response = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($response);
    }

    /**
     * Delete List
     * 
     * @param type $id
     * @return \Zend\View\Model\JsonModel
     *  
     */
    public function deleteList() {
        $model = $this->getModel();
        $methodStatus = is_callable(array($model, 'd' . $this->getMethod()));
        if (!$methodStatus) {
            print $data = \Zend\Json\Json::encode(array("errors" => array(array('key' => 'server', 'message' => 'Method does not exist.', 'code' => $this->getModel()->getCodeNumber('notFound'), 'code_text' => 'notFound'))));
            $this->createLog('errorDL', '', \Zend\Json\Json::encode($data, TRUE));
            exit();
        }
        //  Model called
        try {
            $response = $model->{'d' . $this->getMethod()}('', $this->getQuery());
//        if (!isset($response['data'])) {
            $this->createLog('deleteList', '', \Zend\Json\Json::encode($response, TRUE));
//        }
        } catch (\Exception $e) {
            $response = array("errors" => array(array('key' => 'server', 'message' => $e->getMessage(), 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception')));
            $this->createLog('errorServer', '', \Zend\Json\Json::encode($response, TRUE, array('enableJsonExprFinder' => TRUE)));
        }
        return new JsonModel($response);
    }

    public function createLog($reqType = Null, $request = Null, $response = Null) {
        $logdir = BASE_PATH . "/logs/" . date('Y-m-d') . "/";
        if (!file_exists($logdir)) {
            mkdir($logdir);
        }
        $stream = fopen($logdir . "logs" . date('H') . ".log", 'a', false);
        $writer = new Stream($stream);
        $logger = new Logger();
        $logger->addWriter($writer);

        $message = "\n------------------------------------------------------------------------------------------------------
                    \nUrl :" . $_SERVER['REQUEST_URI'] . "\n";
        $message .= "Method :" . $this->getMethod() . "\n";
        $message .= "IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        $message .= "Request Type :" . $reqType . "\n";
        $message .= "Headers :\n" . \Zend\Json\Json::prettyPrint(\Zend\Json\Json::encode($this->getRequest()->getHeaders()->toArray(), TRUE, array('enableJsonExprFinder' => TRUE))) . "\n";
        $message .= "Request :\n" . \Zend\Json\Json::prettyPrint($request) . "\n";
        $message .= "Response :\n" . \Zend\Json\Json::prettyPrint($response) . "
            \n------------------------------------------------------------------------------------------------------";
        $logger->info($message);
    }

}
