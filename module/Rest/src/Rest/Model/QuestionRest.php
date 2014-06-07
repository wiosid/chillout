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
use Models\Model\QuestionsTable;
use Models\Model\AnswersTable;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/question"
 * )
 */
class QuestionRest extends BaseRest {

    protected $questionTable;
    protected $answerTable;

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
     *
     * @Api(
     *   path="/questions/list",
     *   description="Implemented for getting questions list",
     *   @operations(
     *     @operation(
     *       httpMethod="get",
     *       summary="Implemented for getting questions list",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="list",
     *       @parameters(
     *         @parameter(
     *           name="datetime",
     *           description="questions object's datetime of whose respect you want to fetch questions",
     *           paramType="query",
     *           required=false,
     *           allowMultiple=false,
     *           dataType="datetime"
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
        //  http://localhost/stupid-cupid/public/api/questions/list
        $datetime = (int) $query['datetime'];
        $questionTable=$this->getQuestionTable();
        $updated = $questionTable->getQuestionList($datetime);
        foreach ($updated as $qKey => $question) {
            $updated[$qKey]['answers'] = $this->getAnswerTable()->getAnswersList($question['id']);
        }
        $results['data']['updated'] = $updated;
        $results['data']['deleted'] = $questionTable->getDeletedQuestionList($datetime);
        return $results;
    }

}