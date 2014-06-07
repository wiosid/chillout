<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class QuestionsTable {

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
            'id' => $id, 'fld_status' => 0));
        $row = $rowset->current();
        return $row;
    }

    public function getQuestionList($datetime = 0) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id', 'fld_datetime'));
        $select->join('tbl_answers', $this->tableGateway->table . '.id = tbl_answers.fld_question_id', array());

        $operator = Predicate\Operator::OPERATOR_GREATER_THAN;
        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator('fld_datetime ', $operator, $datetime)
                    )
            )
                )
        );
        $select->where(array(
            'fld_status' => 0
                )
        );
        $select->group($this->tableGateway->table . '.id');
        $select->order('fld_datetime ASC');
//        echo $select->getSqlString();

        $result = $this->tableGateway->selectWith($select);
        return $result->toArray();
    }
    
    public function getDeletedQuestionList($datetime = 0) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id'));
        $select->join('tbl_answers', $this->tableGateway->table . '.id = tbl_answers.fld_question_id', array());

        $operator = Predicate\Operator::OPERATOR_GREATER_THAN;
        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator('fld_datetime ', $operator, $datetime)
                    )
            )
                )
        );
        $select->where(array(
            'fld_status' => 1
                )
        );
        $select->group($this->tableGateway->table . '.id');
        $select->order('fld_datetime ASC');
//        echo $select->getSqlString();

        $result = $this->tableGateway->selectWith($select);
        return array_map(function($i) {
                    return $i['id'];
                }, $result->toArray());
    }

}

