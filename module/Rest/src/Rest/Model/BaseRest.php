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
use Rest\Model\OAuthRest AS OAuthRest;
use Models\Validator\NotInArray;
use Zend\Validator;
use Zend\InputFilter\Input;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/user"
 * )
 */
class BaseRest {

    protected $db_return_type;
    protected $db;
    protected $oauth;
    public $codeNumbers = array(
        "unauthenticated" => 401,
        "invalidLogin" => 402,
        "notFound" => 404,
        "exception" => 500,
        Validator\NotEmpty::IS_EMPTY => 1001,
        "invalid" => 1002,
        Validator\EmailAddress::INVALID_FORMAT => 1002,
        Validator\EmailAddress::INVALID => 1002,
        Validator\EmailAddress::INVALID_FORMAT => 1002,
        Validator\EmailAddress::INVALID_HOSTNAME => 1002,
        Validator\EmailAddress::INVALID_MX_RECORD => 1002,
        Validator\EmailAddress::INVALID_SEGMENT => 1002,
        Validator\EmailAddress::DOT_ATOM => 1002,
        Validator\EmailAddress::QUOTED_STRING => 1002,
        Validator\EmailAddress::INVALID_LOCAL_PART => 1002,
        Validator\EmailAddress::LENGTH_EXCEEDED => 1002,
        Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 1003,
        Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND => 1004,
        Validator\Identical::NOT_SAME => 1005,
        Validator\StringLength::TOO_SHORT => 1006,
        Validator\StringLength::TOO_LONG => 1007,
        Validator\InArray::NOT_IN_ARRAY => 1008,
        NotInArray::IN_ARRAY => 1009,
        "fileUpload" => 1010,
        "fileExtension" => 1011,
        Validator\Date::FALSEFORMAT => 1012,
        Validator\Digits::NOT_DIGITS => 1013,
        Validator\Uri::NOT_URI => 1014,
    );

    /**
     * Constructor defined for assigning db object
     * 
     * @param type $param
     */
    public function __construct(\Zend\Db\Adapter\Adapter $db, $oauth) {
        $this->setDb($db);
        $this->db_return_type = \PDO::FETCH_ASSOC;
        if ($oauth instanceof OAuthRest) {
            $this->setOauth($oauth);
        }
    }

    /**
     * Return \Zend\Db\Adapter\Adapter
     *
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDb() {
        return $this->db;
    }

    public function setDb(\Zend\Db\Adapter\Adapter $db) {
        $this->db = $db;
    }

    /**
     * Return OAuthRest Model
     *
     * @return OAuthRest
     */
    public function getOauth() {
        return $this->oauth;
    }

    public function getModel() {
        return $this;
    }

    public function setOauth(OAuthRest $oauth) {
        $this->oauth = $oauth;
    }

    public function getCodeNumber($code) {
        if (!$this->codeNumbers[$code]) {
            return "XXXXXXX";
        }
        return $this->codeNumbers[$code];
    }

    /**
     * Function generateUniqueFileName() Implemented for 
     * generating unique file name with respect to the directory
     * so that no file overwrite other
     *  
     * @param type $param 
     * 
     * @return unique file as string
     */
    public function generateUniqueFileName($path, $fileName, $ext) {
        $fileName = str_replace(array(" ", "%20"), "+", $fileName);
        $fileName = (strlen($fileName) > 80) ? substr($fileName, 0, 80) : $fileName;
        $randomFileName = $fileName . uniqid() . '.' . $ext;
        if (file_exists($path . "/" . $randomFileName . $ext)) {
            return $this->generateUniqueFileName($path, uniqid() . $fileName, $ext);
        } else {
            return $randomFileName;
        }
    }

    /**
     * Function formatValidationErrors() Implemented for 
     * formatting errors
     *  
     * @param type $param 
     * 
     * @return unique file as string
     */
    public function formatValidationErrors($invalidInput) {
        $i = 0;
        foreach ($invalidInput as $key => $error) {
            if ($error instanceof Input) {
                foreach ($error->getMessages() as $eKey => $eValue) {
                    $responseError[$i]['key'] = $key;
                    if ($eKey == 'inArray' && $key == 'fld_email_id') {
                        $eKey = 'recordFound';
                    }
                    $code = $this->getCodeNumber($eKey);
                    $responseError[$i]['code'] = $code;
                    $responseError[$i]['code_text'] = $eKey;
                    $responseError[$i]['message'] = str_replace(array('The input', 'Value is required and'), $key, $eValue);
                    $i++;
                    break;
                }
            } else {
                throw new \Exception('Invalid type validation input');
            }
        }
        return $responseError;
    }

    /**
     *   Function to get the user object
     */
    public function getUserObeject($userId) {
        $sql = "SELECT `user_id` as id, `fld_name`,`username`, `fld_profile_photo`, `fld_location`, `fld_age`
                FROM `users`
                WHERE `user_id` = '$userId'";
        $dat = $this->getDb()->query($sql);
        $res = $dat->execute()->getResource()->fetch($this->db_return_type);
        return $res;
    }
    
    /**
     *   Function to get the user username
     */
    public function getUserName($userId) {
        $sql = "SELECT `username`
                FROM `users`
                WHERE `user_id` = '$userId'";
        $dat = $this->getDb()->query($sql);
        $res = $dat->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
        return (string) $res;
    }

    /**
     * Function getUserPhotoPath() Implemented for 
     * getting user's image (profile or cover)
     *  
     * @param type $param 
     * 
     * @return user all details in array 
     */
    public function getUserPhotoPath() {
        $photoQuery = $this->getDb()->query('
                    Select 
                    fld_profile_photo
                    FROM users 
                    WHERE user_id=' . $this->getOauth()->getFldUserId() . '
                ');
        $photoArray = $photoQuery->execute()->getResource()->fetch($this->db_return_type);
        return (!empty($photoArray) && !empty($photoArray['profile_photo'])) ? $photoArray['profile_photo'] : FALSE;
    }

    public function sendIosPushNotification($deviceID, $message, $senderId, $recepientUserId, $type) {

        $ctx = stream_context_create();
        // its a seller, use seller pem file
        stream_context_set_option($ctx, 'ssl', 'local_cert', IOS_CERTIFICATE_PATH);

        stream_context_set_option($ctx, 'ssl', 'passphrase', IOS_PASSPHRASE);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
//                'ssl://gateway.sandbox.push.apple.com:2195'
                'ssl://gateway.push.apple.com:2195'
                , $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        /* if (!$fp)
          exit("Failed to connect: $err $errstr" . PHP_EOL); */

        //echo 'Connected to APNS' . PHP_EOL;
        // Create the payload body
        $notificationsCount = $this->getUserNotificationsCount($recepientUserId);
        $body['aps'] = array(
            'alert' => $message,
            'data' => $senderId,
            'badge' => (int) $notificationsCount['fld_messages_notification_count'],
            'type' => $type,
            'sound' => 'default'
        );
//        _pre($this->getUserNotificationsCount($recepientUserId));
        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceID) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));


        /*          if (!$result)
          echo 'Message not delivered' . PHP_EOL;
          else
          echo 'Message successfully delivered' . PHP_EOL;
         */
        // Close the connection to the server
        fclose($fp);
        return $body;
    }

    /**
     * Function array_map_recursive() Implemented for 
     * getting mapped array with callback function
     *  
     * @param type $param 
     * 
     * @return array 
     */
    public function array_map_recursive($callback, $array) {
        foreach ($array as $key => $value) {
            if (is_array($array[$key])) {
                $array[$key] = $this->array_map_recursive($callback, $array[$key]);
            } else {
                $array[$key] = call_user_func($callback, $array[$key]);
            }
        }
        return $array;
    }

    /**
     * Function timeStampToRelativeTime() Implemented for 
     * getting time ago when comment was posted
     * @param type $param 
     * 
     * @return time ago as string
     */
    function timeStampToRelativeTime($dt) {
        $precision = 1;
        $times = array(365 * 24 * 60 * 60 => "year",
            30 * 24 * 60 * 60 => "month",
            7 * 24 * 60 * 60 => "week",
            24 * 60 * 60 => "day",
            60 * 60 => "hour",
            60 => "minute",
            1 => "second");

        $passed = time() - $dt;

        if ($passed < 5) {
            $output = 'just now';
        } else {
            $output = array();
            $exit = 0;
            foreach ($times as $period => $name) {
                if ($exit >= $precision || ($exit > 0 && $period < 60))
                    break;
                $result = floor($passed / $period);

                if ($result > 0) {
                    $output[] = $result . ' ' . $name . ($result == 1 ? '' : 's');
                    $passed-=$result * $period;
                    $exit++;
                } else if ($exit > 0)
                    $exit++;
            }
            $output = implode(' and ', $output) . ' ago';
        }

        return $output;
    }

    /**
     * Function getUserNotificationsCount() Implemented for 
     * getting user's Notifications Count
     *  
     * @param type $userId 
     * 
     * @return user Notifications Count
     */
    public function getUserNotificationsCount($userId) {
        $userNotificationQuery = $this->getDb()->query('
                    Select 
                    fld_messages_notification_count
                    FROM users 
                    WHERE user_id=' . $userId . '
                ');
        return $userNotificationQuery->execute()->getResource()->fetch($this->db_return_type);
    }

}
