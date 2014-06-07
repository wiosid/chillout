<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;

class UserResponsesTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function saveUserResponse($data) {
        $data['fld_datetime'] = time();
        $id = $data['id'];
        if (!empty($id)) {
            $this->tableGateway->update($data, array('id' => $id));
        } else {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }
        return $id;
    }

    public function isAlreadySubmitted($fldUserId, $fldQuestionId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id'))->where(array('fld_user_id' => $fldUserId, 'fld_question_id' => $fldQuestionId));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current()->id;
    }

    /**
     * Function getUserSubmittedQuestions() Implemented for 
     * getting user questions
     * 
     */
    public function getUserSubmittedQuestionCount($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_question_id'))->where(array('fld_user_id' => $fldUserId));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
        return array_map(function($i) {
                    return $i['fld_question_id'];
                }, $resultSet->toArray());
    }

    /**
     * Function getUserSubmittedAnswers() Implemented for 
     * getting user answers
     * 
     */
    public function getUserSubmittedAnswers($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_answer_id'))->where(array('fld_user_id' => $fldUserId));
        $resultSet = $this->tableGateway->selectWith($select);
        return array_map(function($i) {
                    return $i['fld_answer_id'];
                }, $resultSet->toArray());
    }

    /**
     * Function getUsersWhoSubmittedQuestion() Implemented for 
     * getting users who submitted questions
     * 
     */
    public function getUsersWhoSubmittedQuestion($fldQuestionId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_user_id'))->where(array('fld_question_id' => $fldQuestionId));
        $resultSet = $this->tableGateway->selectWith($select);
        return array_map(function($i) {
                    return $i['fld_user_id'];
                }, $resultSet->toArray());
    }

}

