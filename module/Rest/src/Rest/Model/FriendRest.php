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
use Models\Model\ReportAbusesTable;
use Models\Model\ArchiveMessagesTable;
use Models\Model\ArchiveCollectionsTable;
use Models\Model\BlockUsersTable;
use Zend\Json\Json;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/friend"
 * )
 */
class FriendRest extends BaseRest {

    protected $userTable;
    protected $photoTable;
    protected $hitTable;
    protected $friendTable;
    protected $reportAbuseTable;
    protected $archiveMessageTable;
    protected $archiveCollectionTable;
    protected $blockUserTable;

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
     * Return ReportAbusesTable Model
     *
     * @return ReportAbusesTable
     */
    public function getReportAbuseTable() {
        return $this->reportAbuseTable;
    }

    public function setReportAbuseTable(ReportAbusesTable $reportAbuseTable) {
        $this->reportAbuseTable = $reportAbuseTable;
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
     *
     * @Api(
     *   path="/friends/list",
     *   description="Implemented for getting friend list",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting friend list",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="list",
     *       @parameters(
     *         @parameter(
     *           name="length",
     *           description="Number of friends you want to fetch <b>by default value = 10</b>",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="datetime",
     *           description="friend object's datetime of whose respect you want to fetch friends",
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
    public function gList($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/friends/list
        $length = (int) $query['length'];
        $datetime = (int) $query['datetime'];
        $direction = $query['direction'];
        if (empty($datetime)) {

            $friendIds = $this->getFriendTable()->getFriendList($this->getOauth()->getFldUserId(), $length, $datetime, $direction);
            foreach ($friendIds as $fKey => $friend) {
                $friendArray[$fKey] = $friend;
                $friendArray[$fKey]['fld_datetime'] = $friend->fld_datetime;
                $friendArray[$fKey]['is_blocked'] = (bool) $this->getBlockUserTable()->isBlocked($this->getOauth()->getFldUserId(), $friend->id);
                $friendArray[$fKey]['last_active_ago'] = $this->timeStampToRelativeTime($friend->fld_updated_datetime);
                $friendArray[$fKey]['match_ago'] = $this->timeStampToRelativeTime($friend->fld_datetime);

                try {
                    $interests = (array) Json::decode($friendArray[$fKey]['fld_interests'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }
                try {
                    $friends = (array) Json::decode($friendArray[$fKey]['fld_friends'], Json::TYPE_ARRAY);
                } catch (\Exception $e) {
                    
                }

//            $friendArray[$fKey]['interests'] = (array) $interests;
                $friendArray[$fKey]['shared_friends'] = (array) array_values(array_intersect_key((array) $interests, $this->getOauth()->getFldInterests()));
                $friendArray[$fKey]['mutual_friends'] = (array) array_values(array_intersect_key((array) $friends, $this->getOauth()->getFldFriends()));
                unset($friendArray[$fKey]['fld_interests']);
                unset($friendArray[$fKey]['fld_friends']);

//            $imageData = getimagesize($friendArray[$fKey]['fld_profile_photo']);
//            $friendArray[$fKey]['fld_profile_photo_width'] = (int) $imageData[0];
//            $friendArray[$fKey]['fld_profile_photo_height'] = (int) $imageData[1];

                $photos = $this->getPhotoTable()->getUserAllPhotos($friend->id);
//            foreach ($photos as $pKey => $photo) {
//                $photoData = getimagesize($photo['fld_name']);
//                $photos[$pKey]['width'] = (int) $photoData[0];
//                $photos[$pKey]['height'] = (int) $photoData[1];
//            }
                $friendArray[$fKey]['photos'] = $photos;
                $chatMessagesArray = $this->getArchiveMessageTable()->getChatMessages($this->getOauth()->getFldUserName(), $friend->username);
                $chatMessagesArray = array_reverse($chatMessagesArray);
                $friendArray[$fKey]['isExistMessage'] = ((count($chatMessagesArray) > 0) ? 1 : 0);
                foreach ($chatMessagesArray as $key => $value) {
                    //$chatMessagesArray[$key]['updated_on'] = (int) strtotime($value['updated_on']);
                    $chatMessagesArray[$key]['direction'] = ($value['with_user'] == $this->getOauth()->getFldUserName()) ? 0 : 1;
                    unset($chatMessagesArray[$key]['with_user']);
                }
                $friendArray[$fKey]['message'] = $chatMessagesArray;
//            $friendArray[$fKey]['match_status'] = $this->getUserTable()->getUserMatchStatus($this->getOauth()->getFldUserId(), $friend->id);
            }
        }
        $results['data']['friends'] = (array) $friendArray;
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/friends/requests",
     *   description="Implemented for getting friend requests",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting friend requests",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="list",
     *       @parameters(
     *         @parameter(
     *           name="length",
     *           description="Number of friend requests you want to fetch <b>by default value = 10</b>",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="datetime",
     *           description="friend requests object's datetime of whose respect you want to fetch friend requests",
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
    public function gRequests($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/friends/requests
        $length = (int) $query['length'];
        $datetime = (int) $query['datetime'];
        $direction = $query['direction'];
        $requestIds = $this->getHitTable()->getFriendRequestsList($this->getOauth()->getFldUserId(), $length, $datetime, $direction);
        foreach ($requestIds as $rKey => $request) {
            $requestArray[$rKey] = $this->getUserObeject($request->id);
            $requestArray[$rKey]['fld_datetime'] = $request->fld_datetime;
//            $requestArray[$rKey]['match_status'] = $this->getUserTable()->getUserMatchStatus($this->getOauth()->getFldUserId(), $request->id);
        }
        $results['data']['requests'] = (array) $requestArray;
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/friends/delete/{fld_user_id}",
     *   description="Implemented for deleting friends",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for deleting friends",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="delete",
     *       @parameters(
     *         @parameter(
     *           name="fld_user_id",
     *           description="friends User Id",
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
    public function dDelete($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/friends/delete
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
                $friendUserName = $this->getUserName($param);
                //$userExistsArray['fld_gender'] = $genderArray[$userExistsArray['fld_gender']];
                //delete all friend collection and messgaes
                $friendCollectionIds = (array) $this->getArchiveCollectionTable()->getUserAndFriendCollectionIds($this->getOauth()->getFldUserName(), $friendUserName);
                $this->getArchiveMessageTable()->deleteCollectionMessages($friendCollectionIds);
                $this->getArchiveCollectionTable()->deleteUserAndFriendCollectionIds($this->getOauth()->getFldUserName(), $friendUserName);
                
                $this->getFriendTable()->delete($this->getOauth()->getFldUserId(), $param);
                $this->getHitTable()->delete($this->getOauth()->getFldUserId(), $param);
                $results['data']['messgae'] = "Your friend is deleted successfully.";
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

}
