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
use Models\Model\ReportAbusesTable;
use Models\Model\UsersTable;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/report-abuses"
 * )
 */
class ReportAbuseRest extends BaseRest {

    protected $reportAbuseTable;
    protected $userTable;

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
     *   path="/report-abuses/add",
     *   description="Implemented for reporting an object",
     *   @operations(
     *     @operation(
     *       httpMethod="POST",
     *       summary="Implemented for reporting an object",
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
     *           name="fld_reason",
     *           description="Reason",
     *           paramType="path",
     *           required= true,
     *           defaultValue= "",
     *           @allowableValues(valueType="LIST",values="['Report for inappropriate content','Abuse','Fake User','Other']"),allowMultiple= true,
     *           dataType= "string"
     *         ),
     *         @parameter(
     *           name="fld_text",
     *           description="Description why you are reporting",
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
    public function cAdd($param = Null, $query = array()) {
        //  http://sly.local/api/report-abuses/add
        $userId = new Input('fld_user_id');
        $userId->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $text = new Input('fld_text');
        $text->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($userId)->add($text)->setData($param);

        if ($inputFilter->isValid()) {
            $loggedInUserId = $this->getOauth()->getFldUserId();
            $fldUserId = $param['fld_user_id'];
            $data['fld_user_id'] = $loggedInUserId;
            $data['fld_other_user_id'] = $fldUserId;
            $data['fld_reason'] = (string) $param['fld_reason'];
            $data['fld_text'] = $param['fld_text'];

            if ($this->getUserTable()->getUserById($fldUserId)) {
                $reportAbuseTable = $this->getReportAbuseTable();
                $reportAbuseTable->addReport($data);
                $results['data']['message'] = 'User reported successfully.';
            } else {
                $results['errors'] = array(array('key' => 'fld_user_id', 'message' => 'Invalid fld_user_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

}