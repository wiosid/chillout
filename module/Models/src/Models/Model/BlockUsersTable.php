<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class BlockUsersTable {

    protected $tableGateway;
    protected $friendTable;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function addBlockUser($data) {
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

    public function isBlocked($fldUserId, $fldOtherUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id'))->where(array('fld_user_id' => $fldUserId, 'fld_other_user_id' => $fldOtherUserId));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current()->id;
    }

    public function isAnyOfBlockedOther($fldUserId, $fldOtherUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id'))->where(array('fld_user_id' => $fldUserId, 'fld_other_user_id' => $fldOtherUserId));
        $select->where(
                new Predicate\PredicateSet(
                array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator('fld_user_id', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                new Predicate\Operator('fld_other_user_id', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                    )
            ),
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator('fld_user_id', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                new Predicate\Operator('fld_other_user_id', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                    )
            )
                ), Predicate\Predicate::OP_OR
                )
        );
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current()->id;
    }

    public function deleteBlockedUser($fldUserId, $fldOtherUserId) {
        $this->tableGateway->delete(array('fld_user_id' => $fldUserId, 'fld_other_user_id' => $fldOtherUserId));
    }

    public function getBlockedUserIds($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_other_user_id'))->where(array('fld_user_id' => $fldUserId));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return array_map(function($i) {
            return $i['fld_other_user_id'];
        }, $resultSet->toArray());
    }

}
