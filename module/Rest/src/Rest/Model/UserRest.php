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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;
use Models\Validator\NotInArray as NotInArray;
use Models\Model\UsersTable;
use Models\Model\PhotosTable;
use Models\Model\HitsTable;
use Models\Model\FriendsTable;
use Models\Model\BlockUsersTable;
use Models\Model\QuestionsTable;
use Models\Model\AnswersTable;
use Models\Model\UserResponsesTable;
use Models\Model\ArchiveMessagesTable;
use Models\Model\ArchiveCollectionsTable;
use Models\Model\UserTokensTable;
use Zend\Json\Json;

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
class UserRest extends BaseRest {

    protected $userTable;
    protected $photoTable;
    protected $hitTable;
    protected $friendTable;
    protected $blockUserTable;
    protected $questionTable;
    protected $answerTable;
    protected $userResponseTable;
    protected $archiveMessageTable;
    protected $archiveCollectionTable;
    protected $userTokenTable;
    protected $goalioMailService;
    protected $deviceTypeArray = array(
        'ios',
        'android'
    );
    protected $collegeDoaminArray = array(
        'g.ucla.edu' => 'UCLA',
        'ucla.edu' => 'UCLA',
    );

    /**
     * Return UserTable Model
     *
     * @return UsersTable
     */
    public function getUserTable() {
        return $this->userTable;
    }

    public function setUserTable(UsersTable $userTable) {
        $this->userTable = $userTable;
    }

    /**
     * Return PhotosTable Model
     *
     * @return PhotosTable
     */
    public function getPhotoTable() {
        return $this->photoTable;
    }

    public function setPhotoTable(PhotosTable $photoTable) {
        $this->photoTable = $photoTable;
    }

    /**
     * Return HitsTable Model
     *
     * @return HitsTable
     */
    public function getHitTable() {
        return $this->hitTable;
    }

    public function setHitTable(HitsTable $hitTable) {
        $this->hitTable = $hitTable;
    }

    /**
     * Return FriendsTable Model
     *
     * @return FriendsTable
     */
    public function getFriendTable() {
        return $this->friendTable;
    }

    public function setFriendTable(FriendsTable $friendTable) {
        $this->friendTable = $friendTable;
    }

    /**
     * Return BlockUsersTable Model
     *
     * @return BlockUsersTable
     */
    public function getBlockUserTable() {
        return $this->blockUserTable;
    }

    public function setBlockUserTable(BlockUsersTable $blockUserTable) {
        $this->blockUserTable = $blockUserTable;
    }

    /**
     * Return QuestionsTable Model
     *
     * @return QuestionsTable
     */
    public function getQuestionTable() {
        return $this->questionTable;
    }

    public function setQuestionTable(QuestionsTable $questionTable) {
        $this->questionTable = $questionTable;
    }

    /**
     * Return AnswersTable Model
     *
     * @return AnswersTable
     */
    public function getAnswerTable() {
        return $this->answerTable;
    }

    public function setAnswerTable(AnswersTable $answerTable) {
        $this->answerTable = $answerTable;
    }

    /**
     * Return UserResponsesTable Model
     *
     * @return UserResponsesTable
     */
    public function getUserResponseTable() {
        return $this->userResponseTable;
    }

    public function setUserResponseTable(UserResponsesTable $userResponseTable) {
        $this->userResponseTable = $userResponseTable;
    }

    /**
     * Return \GoalioMailService\Mail\Service\Message
     *
     * @return \GoalioMailService\Mail\Service\Message
     */
    public function getGoalioMailService() {
        return $this->goalioMailService;
    }

    public function setGoalioMailService(\GoalioMailService\Mail\Service\Message $goalioMailService) {
        $this->goalioMailService = $goalioMailService;
        return $this;
    }

    /**
     * Return ArchiveMessagesTable Model
     *
     * @return ArchiveMessagesTable
     */
    public function getArchiveMessageTable() {
        return $this->archiveMessageTable;
    }

    public function setArchiveMessageTable(ArchiveMessagesTable $archiveMessageTable) {
        $this->archiveMessageTable = $archiveMessageTable;
    }

    /**
     * Return ArchiveCollectionsTable Model
     *
     * @return ArchiveCollectionsTable
     */
    public function getArchiveCollectionTable() {
        return $this->archiveCollectionTable;
    }

    public function setArchiveCollectionTable(ArchiveCollectionsTable $archiveMessageTable) {
        $this->archiveCollectionTable = $archiveMessageTable;
    }

    /**
     * Return UserTokensTable Model
     *
     * @return UserTokensTable
     */
    public function getUserTokenTable() {
        return $this->userTokenTable;
    }

    public function setUserTokenTable(UserTokensTable $userTokenTable) {
        $this->userTokenTable = $userTokenTable;
        return $this;
    }

    /**
     * Function unauthorizeAllUsersToThisToken() Implemented for 
     * removing tokens of users for this deviceToken so that notification goes to right device 
     *  
     * @param type 
     * 
     * @return string
     */
    public function unauthorizeAllUsersToThisToken($deviceToken = Null) {
        if (!empty($deviceToken)) {
            $userTokenUpdate = $this->getDb()->query('
                UPDATE tbl_user_tokens
                SET fld_device_token=""
                WHERE fld_device_token="' . addcslashes($deviceToken, '"') . '"
            ');
            $isTokenUpdated = $userTokenUpdate->execute();
        }
    }

    /**
     * Function generateToken() Implemented for 
     * creating generating unique Oauth Token
     *  
     * @param type 
     * 
     * @return unique Oauth Token as string
     */
    public function generateToken() {
        $token = md5(microtime(TRUE) . rand(0, 100000));
        $tokenExistsQuery = $this->getDb()->query('
            SELECT ut.id 
            FROM tbl_user_tokens ut 
            WHERE ut.fld_oauth_token="' . $token . '"
        ');
        $tokenExistsArray = $tokenExistsQuery->execute()->getResource()->fetch($this->db_return_type);
        if (!empty($tokenExistsArray)) {
            return $this->generateToken();
        } else {
            return $token;
        }
    }

    /**
     * Function generateVerificationCode() Implemented for 
     * creating generating unique Verification Code
     *  
     * @param type 
     * 
     * @return unique Verification Code as string
     */
    public function generateVerificationCode() {
        $characterArray = array('a', 'b', 'c', 'd', 'e', 'f', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $verificationCode = (string) ($characterArray[rand(0, 15)] . $characterArray[rand(0, 15)] . $characterArray[rand(0, 15)] . $characterArray[rand(0, 15)] . $characterArray[rand(0, 15)] . $characterArray[rand(0, 15)]);
        $tokenExistsQuery = $this->getDb()->query('
            SELECT usr.user_id 
            FROM users usr 
            WHERE usr.fld_verification_code = "' . $verificationCode . '"
        ');
        $tokenExistsArray = $tokenExistsQuery->execute()->getResource()->fetch($this->db_return_type);
        if (!empty($tokenExistsArray)) {
            return $this->generateVerificationCode();
        } else {
            return $verificationCode;
        }
    }

    /**
     *
     * @Api(
     *   path="/users/facebook",
     *   description="Login with facebook oauth access token",
     *   @operations(
     *     @operation(
     *       httpMethod="post",
     *       summary="Login with facebook oauth access token",
     *       notes="For valid response try valid facebook oauth access token",
     *       responseClass="void",
     *       nickname="facebook",
     *       @parameters(
     *         @parameter(
     *           name="fld_fb_access_token",
     *           description="Facebook access token",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_device_token",
     *           description="Device Token",
     *           paramType="path",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="fld_device_type",
     *           description="Device Type",
     *           paramType="path",
     *           required= false,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['android','ios']"),allowMultiple= true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cFacebook($param = Null) {
        $genderArray = array(
            'male' => 1,
            'female' => 2,
        );

        $lookingForArray = array(
            'male' => 2,
            'female' => 1,
        );
        $name = new Input('fld_fb_access_token');
        $name->getValidatorChain()
                ->addValidator(new Validator\NotEmpty(), true);


        $deviceType = new Input('fld_device_type');
        $deviceTypeValidator = new Validator\InArray(array(
            'haystack' => $this->deviceTypeArray,
            'messages' => array(
                Validator\InArray::NOT_IN_ARRAY => 'Invalid device type.',
            ),
        ));

        $deviceType->setAllowEmpty(TRUE)->getValidatorChain()->addValidator($deviceTypeValidator, true);


        $inputFilter = new InputFilter();
        $inputFilter->add($name)->add($deviceType)->setData($param);

        if ($inputFilter->isValid()) {

            //Creating our application instance
            $facebook = new \Facebook(array(
                'appId' => APP_ID,
                'secret' => APP_SECRET
            ));

            $facebook->setAccessToken($param['fld_fb_access_token']);
            //print_r($facebook);die;
            //Get User ID
            $user = $facebook->getUser();
            if ($user) {
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    $fbProfile = $facebook->api('/me?fields=id,first_name,last_name,gender,birthday,picture.height(960).type(large).width(960),location,albums.fields(photos.fields(source),id,name),bio,friends.fields(id,first_name,picture.height(200).type(large).width(200)),likes.fields(id,name,picture.height(200).type(large).width(200))');
                    foreach ($fbProfile['albums']['data'] as $album) {
                        if ($album['name'] == 'Profile Pictures') {
                            $albumId = $album['id'];
                        }
                    }
                    if (!empty($albumId)) {
                        $fbProfilePhotos = $facebook->api('/' . $albumId . '?fields=photos.fields(source).limit(6)');
                    }
                    $i = 0;
                    foreach ($fbProfilePhotos['photos']['data'] as $userPhoto) {
                        if (current(explode(".jpg", end(explode("/", $userPhoto['source'])))) != current(explode(".jpg", end(explode("/", $fbProfile['picture']['data']['url']))))) {
                            $userPhotos[$i]['image'] = $userPhoto['source'];
                            $userPhotos[$i]['created_on'] = strtotime($userPhoto['created_time']);
                            $i++;
                        }
                    }

                    $interestArray = array();
                    $i = 0;
                    foreach ($fbProfile['likes']['data'] as $iV) {
                        $interestArray[$iV['id']]['name'] = $iV['name'];
                        $interestArray[$iV['id']]['image'] = $iV['picture']['data']['url'];
                        $i++;
                    }

                    $friendsArray = array();
                    foreach ($fbProfile['friends']['data'] as $iV) {
                        $friendsArray[$iV['id']]['id'] = $iV['id'];
                        $friendsArray[$iV['id']]['name'] = $iV['first_name'];
                        $friendsArray[$iV['id']]['pic'] = $iV['picture']['data']['url'];
                    }

                    $imageData = getimagesize($fbProfile['picture']['data']['url']);
                    $interestArray = array();
                    $friendsArray = array();
                    $userProfileData = array(
                        'username' => $fbProfile['id'],
                        'password' => $fbProfile['id'] . "@stupid",
                        'fld_name' => $fbProfile['first_name'],
                        'fld_last_name' => $fbProfile['last_name'],
                        'fld_age' => !empty($fbProfile['birthday']) ? (int) floor((time() - strtotime($fbProfile['birthday'])) / 31556926) : "",
                        'fld_gender' => (int) $genderArray[$fbProfile['gender']],
                        'fld_profile_photo' => (string) $fbProfile['picture']['data']['url'],
                        'fld_profile_photo_width' => (int) $imageData[0],
                        'fld_profile_photo_height' => (int) $imageData[1],
                        'fld_location' => (string) $fbProfile['location']['name'],
                        'fld_bio' => (string) $fbProfile['bio'],
                        'fld_interests' => \Zend\Json\Json::encode((array) array_filter($interestArray)),
                        'fld_friends' => \Zend\Json\Json::encode((array) array_filter($friendsArray)),
                    );
                } catch (\Exception $e) {
                    $userProfileData = null;
                }
            }
            if (isset($userProfileData) && !empty($userProfileData)) {
                $userTable = $this->getUserTable();
                if ($fldUserId = $userTable->isFbUserOnStupidCupid($userProfileData['username'])) {
                    if ($userTable->isValidUser($fldUserId)) {
                        $updatedUserProfileData = array(
                            'user_id' => $fldUserId,
                            'fld_name' => $fbProfile['first_name'],
                            'fld_last_name' => $fbProfile['last_name'],
                            'fld_age' => !empty($fbProfile['birthday']) ? (int) floor((time() - strtotime($fbProfile['birthday'])) / 31556926) : "",
                            'fld_gender' => (int) $genderArray[$fbProfile['gender']],
                            'fld_location' => (string) $fbProfile['location']['name'],
                            'fld_interests' => \Zend\Json\Json::encode((array) array_filter($interestArray)),
                            'fld_friends' => \Zend\Json\Json::encode((array) array_filter($friendsArray)),
                        );
                        $fldUserId = $userTable->saveUser($updatedUserProfileData);
                    } else {
                        $results['errors'] = array(array('key' => 'server', 'message' => "Sorry You've been blocked. Contact support@sly.com for more info.", 'code' => $this->getModel()->getCodeNumber('invalid'), 'code_text' => 'invalid'));
                        return $results;
                    }
                } else {
                    $userProfileData['fld_looking_for'] = (int) $lookingForArray[$fbProfile['gender']];
                    $fldUserId = $userTable->saveUser($userProfileData);
                    foreach ($userPhotos as $pic) {
                        $photoData = getimagesize($pic['image']);
                        $this->getPhotoTable()->savePhoto(array('fld_user_id' => $fldUserId, 'fld_name' => $pic['image'], 'width' => (int) $photoData[0], 'height' => (int) $photoData[1], 'fld_datetime' => $pic['created_on']));
                    }
                }
                if (!$fldUserId) {
                    return $results['errors'] = array(array('key' => 'server', 'message' => 'Error in login with facebook', 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception'));
                }
                $this->getOauth()->setFldUserId($fldUserId);
                $fldOauthToken = $this->generateToken();
                $param['fld_oauth_token'] = $fldOauthToken;
                $this->unauthorizeAllUsersToThisToken($param['fld_device_token']);
                $this->updateOauthAndDevice($param, $fldUserId);
                $userData = $this->gGetMyProfile();
                $userData['data']['profile_details']['fld_oauth_token'] = $fldOauthToken;
                $results = $userData;
            } else {
                $results['errors'] = array(array('key' => 'fld_fb_access_token', 'message' => 'Invalid access token', 'code' => $this->getModel()->getCodeNumber('invalid'), 'code_text' => 'invalid'));
            }
        } else {
            $results['errors'] = $this->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/logout",
     *   description="Implemented for updating user oauth token to null so that user can only access services after next login",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for updating user oauth token to null so that user can only access services after next login",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="logout",
     *       @parameters(
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function dLogout($param = Null) {
        //  http://localhost/stupid-cupid/public/api/users/logout
        $userUpdate = $this->getDb()->query('
                DELETE FROM tbl_user_tokens
                WHERE fld_user_id="' . $this->getOauth()->getFldUserId() . '" AND fld_oauth_token="' . $this->getOauth()->getFldOauthToken() . '"
            ');
        $isUserUpdated = $userUpdate->execute();
        $rowsAffected = $isUserUpdated->count();
        if ($rowsAffected >= 0) {
            $results['data']['message'] = 'Logged Out successfully.';
        } else {
            $results['errors'] = array(array('key' => 'server', 'message' => 'Server Error', 'code' => $this->getModel()->getCodeNumber('exception'), 'code_text' => 'exception'));
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/delete",
     *   description="Implemented for deleting user account",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for deleting user account",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="logout",
     *       @parameters(
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function dDelete($param = Null) {
        //  http://localhost/stupid-cupid/public/api/users/delete
        //delete all user collection and messgaes
        $userCollectionIds = (array) $this->getArchiveCollectionTable()->getUserAllCollectionIds($this->getOauth()->getFldUserName());
        $this->getArchiveMessageTable()->deleteCollectionMessages($userCollectionIds);
        $this->getArchiveCollectionTable()->deleteUserAllCollectionIds($this->getOauth()->getFldUserName());

        $this->getUserTable()->deleteUser($this->getOauth()->getFldUserId());
        $results['data']['message'] = 'User account deleted successfully.';
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/get-user/{fld_user_id}",
     *   description="Implemented for getting user profile details by user id",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting user profile details",
     *       notes="For valid response try valid fld_user_id and fld_oauth_token",
     *       responseClass="void",
     *       nickname="getUser",
     *       @parameters(
     *         @parameter(
     *           name="fld_user_id",
     *           description="User Id",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gGetUser($param = Null) {
        //  http://localhost/stupid-cupid/public/api/users/getUser/1
        $genderArray = array(
            1 => 'male',
            2 => 'female',
        );
        $user = new Input('fld_user_id');
        $user->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($user)->setData(array('fld_user_id' => $param));

        if ($inputFilter->isValid()) {
            $fldUserId = intval($param);
            $userExistsArray = $this->getUserTable()->getUserProfileDetails($fldUserId);
            if (empty($userExistsArray)) {
                $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            } else {
                //$userExistsArray['fld_gender'] = $genderArray[$userExistsArray['fld_gender']];
                try {
                    $interests = (array) Json::decode($userExistsArray['fld_interests'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }
                try {
                    $friends = (array) Json::decode($userExistsArray['fld_friends'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }

//                $userExistsArray['interests'] = (array) $interests;
                $userExistsArray['shared_friends'] = (array) array_values(array_intersect_key((array) $interests, $this->getOauth()->getFldInterests()));
                $userExistsArray['mutual_friends'] = (array) array_values(array_intersect_key((array) $friends, $this->getOauth()->getFldFriends()));
                unset($userExistsArray['fld_interests']);
                unset($userExistsArray['fld_friends']);
//                $imageData = getimagesize($userExistsArray['fld_profile_photo']);
//                $userExistsArray['fld_profile_photo_width'] = (int) $imageData[0];
//                $userExistsArray['fld_profile_photo_height'] = (int) $imageData[1];

                $results['data']['profile_details'] = $userExistsArray;

                $results['data']['profile_details']['is_blocked'] = (bool) $this->getBlockUserTable()->isBlocked($this->getOauth()->getFldUserId(), $fldUserId);
                $results['data']['profile_details']['last_active_ago'] = $this->timeStampToRelativeTime($userExistsArray['fld_updated_datetime']);
//                $results['data']['profile_details']['match_status'] = $this->getUserTable()->getUserMatchStatus($this->getOauth()->getFldUserId(), $fldUserId);
                $photos = $this->getPhotoTable()->getUserAllPhotos($fldUserId);
//                foreach ($photos as $pKey => $photo) {
//                    $photoData = getimagesize($photo['fld_name']);
//                    $photos[$pKey]['width'] = (int) $photoData[0];
//                    $photos[$pKey]['height'] = (int) $photoData[1];
//                }
                $results['data']['profile_details']['photos'] = $photos;
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/get-my-profile",
     *   description="Implemented for getting user's own profile details",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting user's own profile details",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="getMyProfile",
     *       @parameters(
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gGetMyProfile() {
        //  http://localhost/stupid-cupid/public/api/users/getMyProfile
        $genderArray = array(
            1 => 'male',
            2 => 'female',
        );
        $fldUserId = $this->getOauth()->getFldUserId();
        $userExistsArray = $this->getUserTable()->getUserDetails($fldUserId);
        if (empty($userExistsArray)) {
            $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
        } else {
//            $imageData = getimagesize($userExistsArray['fld_profile_photo']);
//            $userExistsArray['fld_profile_photo_width'] = (int) $imageData[0];
//            $userExistsArray['fld_profile_photo_height'] = (int) $imageData[1];

            $results['data']['profile_details'] = $userExistsArray;


            $results['data']['profile_details']['last_active_ago'] = $this->timeStampToRelativeTime($userExistsArray['fld_updated_datetime']);
            $settings = $this->getUserTable()->getUserSettings($fldUserId);
            $results['data']['settings'] = $settings;
            $photos = $this->getPhotoTable()->getUserAllPhotos($fldUserId);
//            foreach ($photos as $pKey => $photo) {
//                $photoData = getimagesize($photo['fld_name']);
//                $photos[$pKey]['width'] = (int) $photoData[0];
//                $photos[$pKey]['height'] = (int) $photoData[1];
//            }
            $userExistsArray['fld_interests'] = $this->getOauth()->getFldInterests();
            $userExistsArray['fld_friends'] = $this->getOauth()->getFldFriends();
            $results['data']['profile_details']['photos'] = $photos;
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/update-profile-details",
     *   description="Implemented for updating user profile details",
     *   @operations(
     *     @operation(
     *       httpMethod="PUT",
     *       summary="Implemented for updating user profile details",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="update-profile-details",
     *       @parameters(
     *         @parameter(
     *           name="fld_profile_photo",
     *           description="User Profile Photo Facebook url",
     *           paramType="path",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="fld_photos[0]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_photos[1]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_photos[2]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_photos[3]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_photos[4]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_photos[5]",
     *           description="User facebook profile photos url",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_bio",
     *           description="User Bio",
     *           paramType="path",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function uUpdateProfileDetails($param = Null) {
        //  http://localhost/stupid-cupid/public/api/users/updateProfileDetails
        $userProfileData['user_id'] = $this->getOauth()->getFldUserId();
        $param['fld_profile_photo'] = str_replace("\u0026", "&", $param['fld_profile_photo']);
        $userProfileData['fld_profile_photo'] = (string) $param['fld_profile_photo'];
        $profilePicData = getimagesize($userProfileData['fld_profile_photo']);
        $userProfileData['fld_profile_photo_width'] = (int) (!empty($profilePicData[0]) ? $profilePicData[0] : 320);
        $userProfileData['fld_profile_photo_height'] = (int) (!empty($profilePicData[1]) ? $profilePicData[1] : 360);
        $userProfileData['fld_bio'] = (string) $param['fld_bio'];
        $this->getUserTable()->saveUser($userProfileData);
        $this->getPhotoTable()->deleteUserPhotos($this->getOauth()->getFldUserId());
        foreach ($param['fld_photos'] as $pic) {
            $pic = str_replace("\u0026", "&", $pic);
            $photoData = getimagesize($pic);
            $this->getPhotoTable()->savePhoto(array('fld_user_id' => $this->getOauth()->getFldUserId(), 'fld_name' => $pic, 'width' => (int) (!empty($photoData[0]) ? $photoData[0] : 320), 'height' => (int) (!empty($photoData[1]) ? $photoData[1] : 360), 'fld_datetime' => time()));
        }
        $results = $this->gGetMyProfile();
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/update-settings",
     *   description="Implemented for updating user settings",
     *   @operations(
     *     @operation(
     *       httpMethod="PUT",
     *       summary="Implemented for updating user settings",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="update-settings",
     *       @parameters(
     *         @parameter(
     *           name="fld_gender",
     *           description="User gender : male or female",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['male','female']"),
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="fld_search_distance",
     *           description="In miles",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="fld_min_age",
     *           description="default 18",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="fld_max_age",
     *           description="",
     *           paramType="path",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="fld_looking_for",
     *           description="User looking for : 1-male or 2-female or 0-both",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['','1','2']"),
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function uUpdateSettings($param = Null) {
        //  http://localhost/stupid-cupid/public/api/users/update-settings
//        $notificationArray = array(
//            0,
//            1,
//        );
//        $notificationStatus = new Input('fld_notification_status');
//        $notificationStatusValidator = new Validator\InArray(array(
//            'haystack' => $notificationArray,
//            'messages' => array(
//                Validator\InArray::NOT_IN_ARRAY => 'Invalid fld_notification_status.',
//            ),
//        ));
//
//        $notificationStatus->getValidatorChain()->addValidator($notificationStatusValidator, true);


        $genderArray = array(
            '0' => 0,
            '1' => 1,
            '2' => 2,
        );
        $gender = new Input('fld_gender');
        $genderValidator = new Validator\InArray(array(
            'haystack' => array_keys($genderArray),
            'messages' => array(
                Validator\InArray::NOT_IN_ARRAY => 'Invalid gender.',
            ),
        ));

        $gender->getValidatorChain()->addValidator($genderValidator, true);


        $lookingFor = new Input('fld_looking_for');
        $lookingForValidator = new Validator\InArray(array(
            'haystack' => array_keys($genderArray),
            'messages' => array(
                Validator\InArray::NOT_IN_ARRAY => 'Invalid fld_looking_for.',
            ),
        ));

        $lookingFor->setAllowEmpty(TRUE)->getValidatorChain()->addValidator($lookingForValidator, true);


        $inputFilter = new InputFilter();
        $inputFilter->add($gender)->add($lookingFor)->setData($param);

        if ($inputFilter->isValid()) {
            $userProfileData['user_id'] = $this->getOauth()->getFldUserId();
            $userProfileData['fld_gender'] = (int) $genderArray[$param['fld_gender']];
            $userProfileData['fld_looking_for'] = (int) $genderArray[$param['fld_looking_for']];
            $userProfileData['fld_search_distance'] = !empty($param['fld_search_distance']) ? (int) $param['fld_search_distance'] : 50;
            $userProfileData['fld_min_age'] = !empty($param['fld_min_age']) ? (int) $param['fld_min_age'] : 18;
            $userProfileData['fld_max_age'] = (int) $param['fld_max_age'];

//            $userProfileData['fld_notification_status'] = $param['fld_notification_status'];
            $this->getUserTable()->saveUser($userProfileData);
            $results = $this->gGetMyProfile();
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }

        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/mark",
     *   description="Implemented for marking users as accepted or denied",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for marking users as accepted or denied",
     *       notes="",
     *       responseClass="void",
     *       nickname="add",
     *       @parameters(
     *         @parameter(
     *           name="fld_user_id",
     *           description="User id",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="fld_status",
     *           description="Accept or Deny 1- accept, 0- deny",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cMark($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/users/mark
        $user = new Input('fld_user_id');
        $user->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $status = new Input('fld_status');
        $statusValidator = new Validator\InArray(array(
            'haystack' => array(0, 1),
            'messages' => array(
                Validator\InArray::NOT_IN_ARRAY => 'Invalid fld_status.',
            ),
        ));

        $status->getValidatorChain()->addValidator($statusValidator, true);

        $inputFilter = new InputFilter();
        $inputFilter->add($user)->setData($param);

        if ($inputFilter->isValid()) {
            $fldUserId = $param['fld_user_id'];
            $loggedInUserId = $this->getOauth()->getFldUserId();
            if ($this->getUserTable()->getUserById($fldUserId)) {
                $isFriend = FALSE;
                if ($fldUserId != $loggedInUserId) {
                    $id = 0;
                    if ($id = $this->getHitTable()->isAlreadyMarked($loggedInUserId, $fldUserId)) {
                        
                    } else {
                        $this->getUserTable()->updateUserAcceptCount($fldUserId);
                    }
                    $this->getHitTable()->saveMark(array('id' => $id, 'fld_user_id' => $loggedInUserId, 'fld_other_user_id' => $fldUserId, 'fld_status' => $param['fld_status']));
                    if ($param['fld_status'] == 1) {
                        if ($this->getHitTable()->isOtherUserAlreadyAccepted($loggedInUserId, $fldUserId)) {
//                        if (!$this->getFriendTable()->isAlreadyFriend($loggedInUserId, $fldUserId)) {
                            $isFriend = $this->getFriendTable()->addFriend(array('fld_user_id' => $loggedInUserId, 'fld_other_user_id' => $fldUserId));
//                            if (!$this->getBlockUserTable()->isAnyOfBlockedOther($loggedInUserId, $fldUserId)) {
                            $iosDeviceTokensArray = $this->getUserTokenTable()->getDeviceTokensByUserId($fldUserId, 'ios');
                            if (!empty($iosDeviceTokensArray)) {
                                $this->getUserTable()->increaseUserFriendsNotification($fldUserId);
                            }
                            foreach ($iosDeviceTokensArray as $iosDeviceToken) {
                                $this->sendIosPushNotification($iosDeviceToken, "You and " . $this->getOauth()->getFldDisplayName() . " are now friends.", $this->getOauth()->getFldUserId(), $fldUserId, 2);
                            }
//                            }
//                        } else {
//                            $isFriend = TRUE;
//                        }
                        }
                    }
                }
                $results['data']['message'] = "Successfully Marked.";
                $results['data']['is_friend'] = (bool) $isFriend;
            } else {
                $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/submit-answer",
     *   description="Implemented for submitting answer",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for submitting answer",
     *       notes="",
     *       responseClass="void",
     *       nickname="add",
     *       @parameters(
     *         @parameter(
     *           name="fld_answer_id",
     *           description="Answer id",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cSubmitAnswer($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/users/submit-answer
        $classified = new Input('fld_answer_id');
        $classified->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($classified)->setData($param);

        if ($inputFilter->isValid()) {
            $fldAnswerId = $param['fld_answer_id'];
            $loggedInUserId = $this->getOauth()->getFldUserId();
            if ($row = $this->getAnswerTable()->fetch($fldAnswerId)) {
                $fldQuestionId = $row->fld_question_id;
                if ($this->getQuestionTable()->fetch($fldQuestionId)) {
                    $userResponseTable = $this->getUserResponseTable();
                    $id = (int) $userResponseTable->isAlreadySubmitted($loggedInUserId, $fldQuestionId);
                    $userResponseTable->saveUserResponse(array('id' => $id, 'fld_user_id' => $loggedInUserId, 'fld_question_id' => $fldQuestionId, 'fld_answer_id' => $fldAnswerId));
                    $this->updateUserQuestionAnswer($loggedInUserId, $fldQuestionId, $fldAnswerId);
//                    $this->updateUserRanks($fldQuestionId);
                }
            }
            $results['data']['message'] = "Successfully submitted the answer.";
            $results['data']['fld_question_id'] = $fldQuestionId;
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/matches",
     *   description="Implemented for getting best matches",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting best matches",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="list",
     *       @parameters(
     *         @parameter(
     *           name="fld_gender",
     *           description="male, female or both (default)",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['both','male','female']"),
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="fld_age_from",
     *           description="age from",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="fld_age_to",
     *           description="age to",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="length",
     *           description="Number of matches you want to fetch <b>by default value = 10</b>",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gMatches($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/users/matches
        $genderArray = array(
            1 => 'male',
            2 => 'female',
        );
        $rank = (int) $query['rank'];
        $loggedInUserId = $this->getOauth()->getFldUserId();
        $myId = array($loggedInUserId);
        $blockedUserIds = $this->getBlockUserTable()->getBlockedUserIds($loggedInUserId);
        $alreadyViewedUserIds = $this->getHitTable()->getAlreadyHittedUserIds($loggedInUserId);
        $whoDislikedUserIds = $this->getHitTable()->getWhoDislikedUserIds($loggedInUserId);
        $notInUserIds = array_unique(array_filter(array_merge_recursive($myId, $blockedUserIds, $alreadyViewedUserIds, $whoDislikedUserIds)));
        $userArray = $this->getUserTable()->getMatchesList($this->getOauth()->getFldUserId(), $query, $notInUserIds);
        foreach ($userArray as $uKey => $user) {
//            $userArray[$uKey]['rank'] = ++$rank;
            $normalizedRank = $user['normalized_rank'];
            $userArray[$uKey]['fld_gender'] = $genderArray[$user['fld_gender']];
            $userArray[$uKey]['is_blocked'] = (bool) $this->getBlockUserTable()->isBlocked($this->getOauth()->getFldUserId(), $user['id']);
            $userArray[$uKey]['is_like'] = (bool) $this->getHitTable()->isOtherUserAlreadyAccepted($this->getOauth()->getFldUserId(), $user['id']);
            $userArray[$uKey]['last_active_ago'] = $this->timeStampToRelativeTime($user['fld_updated_datetime']);
            $userArray[$uKey]['fld_datetime'] = $user['fld_updated_datetime'];

            try {
                $interests = (array) Json::decode($userArray[$uKey]['fld_interests'], Json::TYPE_ARRAY);
            } catch (\Exception $e) {
                
            }
            try {
                $friends = (array) Json::decode($userArray[$uKey]['fld_friends'], Json::TYPE_ARRAY);
            } catch (\Exception $e) {
                
            }

//            $userArray[$uKey]['interests'] = (array) $interests;
            $userArray[$uKey]['shared_friends'] = (array) array_values(array_intersect_key((array) $interests, $this->getOauth()->getFldInterests()));
            $userArray[$uKey]['mutual_friends'] = (array) array_values(array_intersect_key((array) $friends, $this->getOauth()->getFldFriends()));
            unset($userArray[$uKey]['fld_interests']);
            unset($userArray[$uKey]['fld_friends']);

//            switch (true) {
//                case ($normalizedRank >= 70):
//                    $userArray[$uKey]['match_status'] = 2;
//                    break;
//                case ($normalizedRank >= 30 && $normalizedRank < 70):
//                    $userArray[$uKey]['match_status'] = 1;
//                    break;
//                default:
//                    $userArray[$uKey]['match_status'] = 0;
//                    break;
//            }
//            $imageData = getimagesize($userArray[$uKey]['fld_profile_photo']);
//            $userArray[$uKey]['fld_profile_photo_width'] = (int) $imageData[0];
//            $userArray[$uKey]['fld_profile_photo_height'] = (int) $imageData[1];
            $photos = $this->getPhotoTable()->getUserAllPhotos($user['id']);
//            foreach ($photos as $pKey => $photo) {
//                $photoData = getimagesize($photo['fld_name']);
//                $photos[$pKey]['width'] = (int) $photoData[0];
//                $photos[$pKey]['height'] = (int) $photoData[1];
//            }
            $userArray[$uKey]['photos'] = $photos;
            unset($userArray[$uKey]['normalized_rank']);
        }
        $results['data']['users'] = (array) $userArray;
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/email-verification",
     *   description="Implemented for email-verification",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for email-verification",
     *       notes="",
     *       responseClass="void",
     *       nickname="email-verification",
     *       @parameters(
     *         @parameter(
     *           name="email",
     *           description="Email id",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cEmailVerification($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/users/email-verification
        $email = new Input('email');


        $eValidator1 = new Validator\EmailAddress(array('useDomainCheck' => FALSE));
        $eValidator1->setMessage('Email format is not correct', Validator\EmailAddress::INVALID_FORMAT);
        $eValidator1->setMessage('Email format is not correct', Validator\EmailAddress::QUOTED_STRING);
        $eValidator1->setMessage('Email format is not correct', Validator\EmailAddress::DOT_ATOM);

        $select = new \Zend\Db\Sql\Select();
        $select->from('users')
        ->where->equalTo('email', $param['email'])
        ->where->notEqualTo('user_id', $this->getOauth()->getFldUserId());

        $eValidator3 = new Validator\Db\NoRecordExists($select);
        $eValidator3->setAdapter($this->getDb());
        $eValidator3->setMessage('email already exists.', Validator\Db\NoRecordExists::ERROR_RECORD_FOUND);

        $email->getValidatorChain()
                ->addValidator(new Validator\NotEmpty(), true)
                ->addValidator($eValidator1, true)
                ->addValidator($eValidator3, true);


        $inputFilter = new InputFilter();
        $inputFilter->add($email)->setData($param);

        if ($inputFilter->isValid()) {
            $verificationCode = $this->generateVerificationCode();
            $mailService = $this->getGoalioMailService();
            if ($mailService instanceof \GoalioMailService\Mail\Service\Message) {
                $userProfileData['user_id'] = $this->getOauth()->getFldUserId();
                if (strpos($param['email'], ".edu") === FALSE) {
                    $param['email'].=".edu";
                }
                if (strpos($param['email'], "apple") !== FALSE) {
                    $userProfileData['fld_status'] = 1;
                }
                $userProfileData['email'] = $param['email'];
                $collegeDomain = end(explode("@", $param['email']));
                $college = $this->collegeDoaminArray[$collegeDomain];
//                $userProfileData['fld_college'] = strtoupper(rtrim(end(explode("@", $param['email'])), ".edu"));
                $userProfileData['fld_college'] = $college;
                $userProfileData['fld_verification_code'] = $verificationCode;
                $this->getUserTable()->saveUser($userProfileData);
                if (strpos($param['email'], '@ucla.edu') !== FALSE || strpos($param['email'], '@g.ucla.edu') !== FALSE) {
                    $message = $mailService->createTextMessage(NO_REPLY_EMAIL, $param['email'], "Email Verification of your Sly Account", "mailtemplate/user/email-verification-text", array('verification_code' => $verificationCode));
                    $mailService->send($message);
                } else {
                    $message = $mailService->createTextMessage(NO_REPLY_EMAIL, $param['email'], "Sly activation!", "mailtemplate/user/email-launch-text");
                    $mailService->send($message);
                }
                $results['data']['message'] = "A verification mail has been sent to your email.";
            } else {
                throw new Exception("Set Mail Service.");
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/verify-code",
     *   description="Implemented for verify-code",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for verify-code",
     *       notes="",
     *       responseClass="void",
     *       nickname="verify-code",
     *       @parameters(
     *         @parameter(
     *           name="code",
     *           description="verifiction code",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cVerifyCode($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/users/verify-code


        $code = new Input('code');


        $code->getValidatorChain()
                ->addValidator(new Validator\NotEmpty(), true);


        $inputFilter = new InputFilter();
        $inputFilter->add($code)->setData($param);

        if ($inputFilter->isValid()) {
            if ($fldUserId = $this->getUserTable()->verifyVerificationCode($param['code'])) {
                $userProfileData['user_id'] = $fldUserId;
                $userProfileData['fld_verification_code'] = "";
                $userProfileData['fld_status'] = 1;
                $this->getUserTable()->saveUser($userProfileData);

                $results = $this->gGetMyProfile();
                return $results;
            } else {
                $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid code', 'code' => $this->getModel()->getCodeNumber('invalid'), 'code_text' => 'invalid'));
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/chat",
     *   description="Implemented for upload image",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for upload image",
     *       notes="",
     *       responseClass="void",
     *       nickname="chat",
     *       @parameters(
     *         @parameter(
     *           name="image",
     *           description="image",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cChat($param = Null) {


        $photo = $param['image'];
        if (isset($photo) && isset($photo['name']) && $photo['name']) {
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $originalFilename = pathinfo($photo['name']);
            $newProfileFileName = $this->generateUniqueFileName(PHOTO_PATH, 'file-' . str_replace(" ", "_", $originalFilename['filename']), $originalFilename['extension']);
            $adapter->addFilter('Rename', $newProfileFileName);
            $adapter->addValidator('Extension', false, 'jpg', 'jpeg', 'png', 'gif');
            $adapter->setDestination(PHOTO_PATH);
            $extension = strtolower($originalFilename['extension']);
            if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                if ($adapter->receive($photo['name'])) {
                    $uploadedPhotosArray[] = $newProfileFileName;
                } else {
                    $error[0]['key'] = "fld_photo[$pKey]";
                    $error[0]['code'] = $this->getModel()->getCodeNumber('fileUpload');
                    $error[0]['code_text'] = "fileUpload";
                    $error[0]['message'] = "Error in upload.";
                }
            } else {
                $error[0]['key'] = "image";
                $error[0]['code'] = $this->getModel()->getCodeNumber('fileExtension');
                $error[0]['code_text'] = "fileExtension";
                $error[0]['message'] = "only 'jpg', 'jpeg', 'png', 'gif' extension supported for uploading image.";
            }
        }
        if (count($error[0]) < 1) {
            $data['data']['path'] = BASE_URL . '/uploads/photos/' . $newProfileFileName;
        } else {
            $data['data'] = $error[0];
        }

        return $data;
    }

    /**
     *
     * @Api(
     *   path="/users/upload-chat-file",
     *   description="Implemented for uploading chat file",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for uploading chat file",
     *       notes="For valid response try valid oauth_token",
     *       responseClass="void",
     *       nickname="upload-chat-file",
     *       @parameters(
     *         @parameter(
     *           name="file",
     *           description="Any File",
     *           paramType="body",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="file"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cUploadChatFile($param = Null) {
        //  http://192.168.22.49/hire-me/public/api/users/upload-chat-file
        $loggedInUserId = $this->getOauth()->getFldUserId();

        if (isset($param['file']) && isset($param['file']['name']) && $param['file']['name']) {
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $originalFilename = pathinfo($param['file']['name']);
            $newProfileFileName = $this->generateUniqueFileName(CHAT_FILE_PATH, 'file-' . str_replace(" ", "_", $originalFilename['filename']), $originalFilename['extension']);
            $adapter->addFilter('Rename', $newProfileFileName);
            $adapter->addValidator('Extension', false, 'jpg', 'jpeg', 'png', 'gif');
            $adapter->setDestination(CHAT_FILE_PATH);
            $extension = strtolower($originalFilename['extension']);
            if ($adapter->receive($param['file']['name'])) {
                $chatFileURL = CHAT_FILE_URL . "/" . $newProfileFileName;
                $results['data']['file_url'] = $chatFileURL;
                return $results;
            } else {
                $error['key'] = "file";
                $error['code'] = $this->getModel()->getCodeNumber('fileUpload');
                $error['code_text'] = "fileUpload";
                $error['message'] = "Error in photo upload.";
                $i++;
            }
        } else {
            $error['key'] = "file";
            $error['code'] = $this->getModel()->getCodeNumber(Validator\NotEmpty::IS_EMPTY);
            $error['code_text'] = Validator\NotEmpty::IS_EMPTY;
            $error['message'] = "Please upload file.";
            $i++;
        }
        $results['errors'] = $error;
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/get-user-chat-messages",
     *   description="Implemented for getting user chat messages",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting user chat messages",
     *       notes="For valid response try valid oauth_token",
     *       responseClass="void",
     *       nickname="get-user-chat-messages",
     *       @parameters(
     *         @parameter(
     *           name="user_id",
     *           description="User Id of chat between you and the user",
     *           paramType="query",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="message_id",
     *           description="with respect to message",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="length",
     *           description="Number of messages you want to fetch <b>by default value = 10</b>",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="datetime",
     *           description="message's datetime of whose respect you want to fetch messages <br/><i style='font-style: italic; font-size: 20px;'>→Note : Use updated_on key of message</i>",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="datetime"
     *         ),
     *         @parameter(
     *           name="direction",
     *           description="Direction prev (for older) or next (for newer)",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['prev','next']"),
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gGetUserChatMessages($param = Null, $query = array()) {
        //  http://192.168.22.49/hire-me/public/api/users/get-user-chat-messages/{user_id}
        $userId = new Input('user_id');
        $userId->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($userId)->setData($query);
        if ($inputFilter->isValid()) {
            $fldUserId = intval($query['user_id']);
            $userExistsArray = $this->getUserObeject($fldUserId);
            if (empty($userExistsArray)) {
                $results['errors'] = array(array('key' => 'user_id', 'message' => 'Invalid user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            } else {
                $chatUsersArray = $this->getArchiveMessageTable()->getChatMessages($this->getOauth()->getFldUserName(), $userExistsArray['username'], $query);
                $chatUsersArray = array_reverse($chatUsersArray);
                foreach ($chatUsersArray as $key => $value) {
//                    $chatUsersArray[$key]['updated_on'] = (int) strtotime($value['updated_on']);
                    $chatUsersArray[$key]['direction'] = ($value['with_user'] == $this->getOauth()->getFldUserName()) ? 0 : 1;
                    unset($chatUsersArray[$key]['with_user']);
                }
                $results['data']['chat_messages'] = $chatUsersArray;
            }
            $userExistsArray['isExistMessage'] = (count($chatUsersArray) > 0) ? 1 : 0;
            $results['data']['user'] = $userExistsArray;
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    protected function updateOauthAndDevice(array $param, $fldUserId) {
        $data = $this->getDb()->query('
                INSERT INTO tbl_user_tokens
                SET
                fld_user_id="' . $fldUserId . '",
                fld_device_token="' . addcslashes($param['fld_device_token'], "'") . '",
                fld_device_type="' . addcslashes($param['fld_device_type'], "'") . '",
                fld_oauth_token="' . $param['fld_oauth_token'] . '",
                fld_last_accessed_datetime="' . time() . '"
            ');
        $isUserUpdated = $data->execute();
    }

    protected function updateUserQuestionAnswer($fldUserId, $fldQuestionId, $fldAnswerId) {
        $userResponseTable = $this->getUserResponseTable();
//        $questionsArray = $userResponseTable->getUserSubmittedQuestions($fldUserId);
//        $questionsArray[] = $fldQuestionId;
//        $questionsArray = array_filter(array_unique($questionsArray));
//        $answersArray = $userResponseTable->getUserSubmittedAnswers($fldUserId);
//        $answersArray[] = $fldAnswerId;
//        $answersArray = array_filter(array_unique($answersArray));
//
//        $questionsString = implode(" ", $questionsArray);
//        $answersString = implode(" ", $answersArray);
        $data['id'] = $fldUserId;
//        $data['fld_questions'] = $questionsString;
//        $data['fld_answers'] = $answersString;
        $data['fld_questions_count'] = $userResponseTable->getUserSubmittedQuestionCount($fldUserId);
        $fldUserId = $this->getUserTable()->saveUser($data);
    }

    protected function updateUserRanks($fldQuestionId) {
        $userResponseTable = $this->getUserResponseTable();
        $fldUserIds = $userResponseTable->getUsersWhoSubmittedQuestion($fldQuestionId);
        $this->getUserTable()->updateUserRanks($fldUserIds);
    }

    /**
     *
     * @Api(
     *   path="/users/send-chat-notification",
     *   description="Implemented for sending chat notification",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for submitting answer",
     *       notes="",
     *       responseClass="void",
     *       nickname="send-chat-notification",
     *       @parameters(
     *         @parameter(
     *           name="fld_user_id",
     *           description="User id",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "int"
     *         ),
     *         @parameter(
     *           name="message",
     *           description="text what to send in notification less than 50 characters",
     *           paramType="path",
     *           required= true,
     *           allowMultiple= false,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cSendChatNotification($param = Null, $query = array()) {
        //  http://dev.jubba.com/api/users/send-chat-notification

        $user = new Input('fld_user_id');
        $user->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);


        $message = new Input('message');
        $message->getValidatorChain()
                ->addValidator(new Validator\NotEmpty(), true)
                ->addValidator(new Validator\StringLength(array('max' => 50)), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($user)->setData($param);

        if ($inputFilter->isValid()) {
            $fldUserId = $param['fld_user_id'];
            $message = ((strlen($param['message']) > 80) ? substr($param['message'], 0, 80) : $param['message']);
            $message = str_replace("(null)", $this->getOauth()->getFldDisplayName(), $message);
            $userExistsArray = $this->getUserTable()->getUserProfileDetails($fldUserId);
            if (empty($userExistsArray)) {
                $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            } else {
                if ($this->getFriendTable()->isAlreadyFriend($this->getOauth()->getFldUserId(), $fldUserId)) {
                    if (!$this->getBlockUserTable()->isAnyOfBlockedOther($this->getOauth()->getFldUserId(), $fldUserId)) { // Check Notification Setting
                        $iosDeviceTokensArray = $this->getUserTokenTable()->getDeviceTokensByUserId($fldUserId, 'ios');
                        if (!empty($iosDeviceTokensArray)) {
                            $this->getUserTable()->increaseUserMessagesNotification($fldUserId);
                        }
                        foreach ($iosDeviceTokensArray as $iosDeviceToken) {
                            $notBody = $this->sendIosPushNotification($iosDeviceToken, $message, $this->getOauth()->getFldUserId(), $fldUserId, 2);
                        }
                    }
                    $results['data']['message'] = "Notification sent successfully.";
                    $results['data']['fld_user_id'] = $this->getOauth()->getFldUserId();
                    $results['data']['is_friend'] = (bool) $this->getFriendTable()->isAlreadyFriend($this->getOauth()->getFldUserId(), $fldUserId);
                    $results['data']['not_body'] = $notBody;
                } else {
                    $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'This user is not your friend.', 'code' => $this->getModel()->getCodeNumber('invalid'), 'code_text' => 'invalid'));
                }
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/reset-chat-notification",
     *   description="Implemented for resetting chat notification",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for resetting chat notification",
     *       notes="",
     *       responseClass="void",
     *       nickname="reset-chat-notification",
     *       @parameters(
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function dResetChatNotification($param = Null, $query = array()) {
        //  http://dev.jubba.com/api/users/reset-chat-notification
        $this->getUserTable()->resetUserMessagesNotification($this->getOauth()->getFldUserId());
        $results['data']['message'] = "Notification count reset successfully.";
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/get-server-time",
     *   description="",
     *   @operations(
     *     @operation(
     *       httpMethod="GET",
     *       summary="",
     *       notes="",
     *       responseClass="void",
     *       nickname="get-server-time",
     *       @parameters(
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gGetServerTime($param = Null, $query = array()) {
        $results['data']['message'] = time();
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/set-last-active-time",
     *   description="Implemented for updating user last active time",
     *   @operations(
     *     @operation(
     *       httpMethod="PUT",
     *       summary="Implemented for updating user last active time",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="set-last-active-time",
     *       @parameters(
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function uSetLastActiveTime($param = Null) {
        //  http://dev.jubba.com/api/users/set-last-active-time
        // user last time updated while checking oauth token
        // no need to update again
        $results['data']['message'] = "User last active time updated successfully.";
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/users/get-last-active-time/{fld_user_id}",
     *   description="Implemented for getting user last active time",
     *   @operations(
     *     @operation(
     *       httpMethod="GET",
     *       summary="Implemented for getting user last active time",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="get-last-active-time",
     *       @parameters(
     *         @parameter(
     *           name="fld_user_id",
     *           description="User Id",
     *           paramType="path",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function gGetLastActiveTime($param = Null) {
        //  http://dev.jubba.com/api/users/get-last-active-time
        $results['data']['message'] = (int) $this->getUserTable()->getUserLastActiveTime($param);
        return $results;
    }

}
