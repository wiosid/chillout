<?php

/**
 * Finoit Technologies custom model defined
 * Developer Ramakant Gangwar, Finoit Technologies (gangwar.ramji@gmail.com).
 * 
 * Zend Framework (http://framework.zend.com/)
 *
 */

namespace Rest\Model;

use Swagger\Annotations\Operation;
use Swagger\Annotations\Operations;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Parameters;
use Swagger\Annotations\AllowableValues;
use Swagger\Annotations\Api;
use Swagger\Annotations\ErrorResponse;
use Swagger\Annotations\ErrorResponses;
use Swagger\Annotations\Resource;
use ZendService\Apple\Apns\Client\Message as Client;
use ZendService\Apple\Apns\Message;
use ZendService\Apple\Apns\Message\Alert;
use ZendService\Apple\Apns\Response\Message as Response;
use ZendService\Apple\Apns\Exception\RuntimeException;
//use Zend\Mail\Message as SendEmail;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
//use Zend\Mail\Exception;
use Zend\Mail\Message as SendEmail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Json\Json;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api/users",
 *      resourcePath="/user"
 * )
 */
class OAuthRest extends BaseRest {

    public $fldOauthToken;
    public $fldUserId;
    public $fldDisplayName;
    public $fldUserName;
    public $fldCollege;
    public $fldFriends = array();
    public $fldInterests = array();

    /**
     * Constructor defined for assigning db object
     * 
     * @param type $param
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db) {
        parent::__construct($db, $this);
        $this->db_return_type = \PDO::FETCH_ASSOC;
    }

    public function getFldOauthToken() {
        return $this->fldOauthToken;
    }

    public function setFldOauthToken($fldOauthToken) {
        $this->fldOauthToken = $fldOauthToken;
    }

    public function getFldUserId() {
        return $this->fldUserId;
    }

    public function setFldUserId($fldUserId) {
        $this->fldUserId = $fldUserId;
    }

    public function getFldDisplayName() {
        return $this->fldDisplayName;
    }

    public function setFldDisplayName($fldDisplayName) {
        $this->fldDisplayName = $fldDisplayName;
    }

    public function getFldUserName() {
        return $this->fldUserName;
    }

    public function setFldUserName($fldUserName) {
        $this->fldUserName = $fldUserName;
    }

    public function getFldCollege() {
        return $this->fldCollege;
    }

    public function setFldCollege($fldCollege) {
        $this->fldCollege = $fldCollege;
        return $this;
    }

    public function getFldFriends() {
        return $this->fldFriends;
    }

    public function setFldFriends($fldFriends) {
        $this->fldFriends = $fldFriends;
        return $this;
    }
    
    public function getFldInterests() {
        return $this->fldInterests;
    }

    public function setFldInterests($fldInterests) {
        $this->fldInterests = $fldInterests;
        return $this;
    }

    
    /**
     * Function isValidOauth() Implemented for 
     * checking whether oauth token exists or not
     * and also related to which user
     * also set user id, token and email id 
     *  
     * @param type 
     * 
     * @return true if exists or false as boolean
     */
    public function isValidOauth($fldOauthToken = NULL) {
        if ($fldOauthToken && !empty($fldOauthToken)) {
            $tokenExistsQuery = $this->getDb()->query('
                SELECT usr.user_id, usr.fld_name, usr.username, usr.fld_college
                FROM users usr 
                INNER JOIN tbl_user_tokens ut ON (usr.user_id=ut.fld_user_id)
                WHERE ut.fld_oauth_token="' . addcslashes(stripslashes($fldOauthToken), "'") . '"
                AND usr.fld_is_blocked = 0
            ');
            $tokenExistsArray = $tokenExistsQuery->execute()->getResource()->fetch($this->db_return_type);
            if (!empty($tokenExistsArray)) {
                $this->setFldOauthToken($fldOauthToken);
                $this->setFldUserId($tokenExistsArray['user_id']);
                $this->setFldDisplayName($tokenExistsArray['fld_name']);
                $this->setFldUserName($tokenExistsArray['username']);
                $this->setFldCollege($tokenExistsArray['fld_college']);
                try {
                    $friends = Json::decode($tokenExistsArray['fld_friends'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }
                $this->setFldFriends((array) $friends);
                try {
                    $interests = Json::decode($tokenExistsArray['fld_interests'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }
                $this->setFldInterests((array) $interests);
                $tokenLastAccessedDateTimeUpdate = $this->getDb()->query('
                    UPDATE tbl_user_tokens ut
                    INNER JOIN users usr ON (ut.fld_user_id = usr.user_id)
                    SET fld_last_accessed_datetime="' . time() . '",
                    fld_updated_datetime="' . time() . '",
                    fld_messages_notification_count = 0
                    WHERE fld_user_id="' . $tokenExistsArray['user_id'] . '" AND fld_oauth_token="' . $fldOauthToken . '"
                ');
                $tokenLastAccessedDateTimeUpdate->execute();
                return $tokenExistsArray['user_id'];
            }
        }
        return false;
    }

}
