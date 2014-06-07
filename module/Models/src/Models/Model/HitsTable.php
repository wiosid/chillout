<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class HitsTable {

    protected $tableGateway;
    protected $friendTable;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
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

    public function totalCount() {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function saveMark($data) {
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

    public function isAlreadyMarked($fldUserId, $fldOtherUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id'))->where(array('fld_user_id' => $fldUserId, 'fld_other_user_id' => $fldOtherUserId));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current()->id;
    }

    public function isOtherUserAlreadyAccepted($fldUserId, $fldOtherUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id'))->where(array('fld_user_id' => $fldOtherUserId, 'fld_other_user_id' => $fldUserId, 'fld_status' => 1));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }

    public function getFriendRequestsList($fldUserId, $length = 10, $datetime = 0, $direction = "prev") {
        if (empty($length)) {
            $length = 10;
        }
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('fld_datetime'));
        $select->join('users', $this->tableGateway->table . '.fld_user_id=users.user_id', array('id' => 'user_id'));
        $friendCondition = new \Zend\Db\Sql\Predicate\Expression('( ' . $this->tableGateway->table . '.fld_user_id = tbl_friends.fld_user_id AND tbl_friends.fld_other_user_id=' . $fldUserId . ') OR ( ' . $this->tableGateway->table . '.fld_user_id = tbl_friends.fld_other_user_id AND tbl_friends.fld_user_id=' . $fldUserId . ')');
        $select->join('tbl_friends', $friendCondition, array(), Select::JOIN_LEFT);
        $blockonCondition = new \Zend\Db\Sql\Predicate\Expression('( ' . $this->tableGateway->table . '.fld_user_id = tbl_block_users.fld_user_id AND tbl_block_users.fld_other_user_id=' . $fldUserId . ') OR ( ' . $this->tableGateway->table . '.fld_user_id = tbl_block_users.fld_other_user_id AND tbl_block_users.fld_user_id=' . $fldUserId . ')');
        $select->join('tbl_block_users', $blockonCondition, array(), Select::JOIN_LEFT);

        $hitsOnCondition = new \Zend\Db\Sql\Predicate\Expression('( ' . $this->tableGateway->table . '.fld_user_id = tbl_user_hits2.fld_other_user_id AND tbl_user_hits2.fld_user_id =' . $fldUserId . ')');
        $select->join(array('tbl_user_hits2' => $this->tableGateway->table), $hitsOnCondition, array(), Select::JOIN_LEFT);

        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                            )
                    )
                )
        );

        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('tbl_user_hits2.fld_status', Predicate\Operator::OPERATOR_NOT_EQUAL_TO, 0),
                            )
                    )
                )
        );

        $select->where(array($this->tableGateway->table . '.fld_status' => 1));

        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\IsNull('tbl_friends.id'),
                            )
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\IsNull('tbl_block_users.id'),
                            )
                    )
                )
        );

        if ($direction == 'next') {
            $operator = Predicate\Operator::OPERATOR_LESS_THAN;
        } else {
            $operator = Predicate\Operator::OPERATOR_GREATER_THAN;
        }
        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator($this->tableGateway->table . '.fld_datetime ', $operator, $datetime)
                    )
            )
                )
        );
        $select->order($this->tableGateway->table . '.fld_datetime DESC')->limit($length);
//        echo $select->getSqlString();
        $result = $this->tableGateway->selectWith($select);
        return $result;
    }

    public function getAlreadyHittedUserIds($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_other_user_id'))->where(array('fld_user_id' => $fldUserId));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return (array) array_map(function($i) {
            return $i['fld_other_user_id'];
        }, $resultSet->toArray());
    }

    public function getWhoDislikedUserIds($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_user_id'))->where(array('fld_other_user_id' => $fldUserId, 'fld_status' => 0));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return (array) array_map(function($i) {
            return $i['fld_other_user_id'];
        }, $resultSet->toArray());
    }

    public function delete($fldUserId, $fldOtherUserId) {
        $this->tableGateway->delete(array('fld_user_id' => $fldUserId, 'fld_other_user_id' => $fldOtherUserId));
    }

}
