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
use Models\Model\BlockUsersTable;
use Models\Model\UsersTable;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/block-user"
 * )
 */
class BlockUserRest extends BaseRest {

    protected $blockUserTable;
    protected $userTable;

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
     * Return UsersTable Model
     *
     * @return UsersTable
     */
    public function getUserTable() {
        return $this->userTable;
    }

    public function setUserTable(UsersTable $userTable) {
        $this->userTable = $userTable;
        return $this;
    }

    /**
     *
     * @Api(
     *   path="/block-users/add",
     *   description="Implemented for blocking users",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for blocking users",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="logout",
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
    public function cAdd($param = Null) {
        //  http://localhost/stupid-cupid/public/api/block-users/add
        $userId = new Input('fld_user_id');
        $userId->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($userId)->setData($param);

        if ($inputFilter->isValid()) {

            $loggedInUserId = $this->getOauth()->getFldUserId();
            $fldUserId = $param['fld_user_id'];
            $data['fld_user_id'] = $loggedInUserId;
            $data['fld_other_user_id'] = $fldUserId;
            if ($this->getUserTable()->getUserById($fldUserId)) {
                $blockUserTable = $this->getBlockUserTable();
                $data['id'] = (int) $blockUserTable->isBlocked($loggedInUserId, $fldUserId);
                $blockUserTable->addBlockUser($data);
                $results['data']['message'] = 'User blocked successfully.';
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
     *   path="/block-users/delete/{fld_user_id}",
     *   description="Implemented for unblocking users",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for unblocking users",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="logout",
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
        //  http://localhost/stupid-cupid/public/api/block-users/delete/{fld_user_id}
        $loggedInUserId = $this->getOauth()->getFldUserId();
        $fldUserId = $param;
        $data['fld_user_id'] = $loggedInUserId;
        $data['fld_other_user_id'] = $fldUserId;
        if ($this->getUserTable()->getUserById($fldUserId)) {
            $blockUserTable = $this->getBlockUserTable();
            $blockUserTable->deleteBlockedUser($loggedInUserId, $fldUserId);
            $results['data']['message'] = 'User unblocked successfully.';
        } else {
            $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
        }

        return $results;
    }

}