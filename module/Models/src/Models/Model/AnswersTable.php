<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class AnswersTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function fetch($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array(
            'id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function getAnswersList($fldQuestionId) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id', 'fld_text'));
        $select->where(array('fld_question_id' => $fldQuestionId));
        $result = $this->tableGateway->selectWith($select);
        return $result->toArray();
    }

}
