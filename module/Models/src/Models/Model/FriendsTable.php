<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class FriendsTable {

    protected $tableGateway;
    protected $reportAbuseTable;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
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

    public function totalCount() {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function addFriend($data) {
        $data['fld_datetime'] = time();
        $data['fld_last_chat_datetime'] = date('Y-m-d H:i:s', time());
        $this->tableGateway->insert($data);
        $id = $this->tableGateway->lastInsertValue;
        return $id;
    }

    public function isAlreadyFriend($fldUserId, $fldOtherUserId) {
        $select = new Select($this->tableGateway->table);
        $select
                ->columns(array('id'));
        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('fld_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                        new Predicate\Operator('fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                            ), Predicate\PredicateSet::COMBINED_BY_AND
                    )
                )
        )->where(
                array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\Operator('fld_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                new Predicate\Operator('fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                    ), Predicate\PredicateSet::COMBINED_BY_AND
            )
                ), \Zend\Db\Sql\Where::OP_OR
        );
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->current()->id;
    }

    public function getFriendList($fldUserId, $length = 10, $datetime = 0, $direction = "prev") {
        if (empty($length)) {
            $length = 10;
        }
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('fld_datetime'));
        $onCondition = new \Zend\Db\Sql\Predicate\Expression('( ' . $this->tableGateway->table . '.fld_user_id = users.user_id AND ' . $this->tableGateway->table . '.fld_other_user_id=' . $fldUserId . ') OR ( ' . $this->tableGateway->table . '.fld_other_user_id = users.user_id AND ' . $this->tableGateway->table . '.fld_user_id=' . $fldUserId . ')');
        $select->join('users', $onCondition, array('id' => 'user_id',
            'fld_name' => 'fld_name',
            'username' => 'username',
            'fld_profile_photo' => 'fld_profile_photo',
            'fld_profile_photo_width' => 'fld_profile_photo_width',
            'fld_profile_photo_height' => 'fld_profile_photo_height',
            'fld_age' => 'fld_age',
            'fld_gender' => 'fld_gender',
            'fld_location' => 'fld_location',
            'fld_bio' => 'fld_bio',
            'fld_updated_datetime' => 'fld_updated_datetime',
            'fld_college' => 'fld_college'
        ));
        $blockonCondition = new \Zend\Db\Sql\Predicate\Expression('( ' . $this->tableGateway->table . '.fld_user_id = tbl_block_users.fld_user_id AND tbl_block_users.fld_other_user_id=' . $this->tableGateway->table . '.fld_other_user_id) OR ( ' . $this->tableGateway->table . '.fld_user_id = tbl_block_users.fld_other_user_id AND tbl_block_users.fld_user_id=' . $this->tableGateway->table . '.fld_other_user_id)');
        $select->join('tbl_block_users', $blockonCondition, array(), Select::JOIN_LEFT);


        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.fld_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                        new Predicate\Operator($this->tableGateway->table . '.fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                            ), Predicate\PredicateSet::COMBINED_BY_OR
                    )
                )
        );
        if ($direction == 'prev') {
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

        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\IsNull('tbl_block_users.id'),
                            )
                    )
                )
        );
        $select->where(array('users.fld_is_blocked' => 0));
        $select->order($this->tableGateway->table . '.fld_datetime DESC');
        $select->group("users.user_id");
//        $select->limit($length);
//        echo $select->getSqlString();

        $result = $this->tableGateway->selectWith($select);
        return $result;
    }

    public function delete($fldUserId, $fldOtherUserId) {
        $this->tableGateway->delete(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\Operator('fld_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                    new Predicate\Operator('fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                        ), Predicate\PredicateSet::COMBINED_BY_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\Operator('fld_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldOtherUserId),
                    new Predicate\Operator('fld_other_user_id ', Predicate\Operator::OPERATOR_EQUAL_TO, $fldUserId),
                        ), Predicate\PredicateSet::COMBINED_BY_AND
                ),
                    ), Predicate\PredicateSet::COMBINED_BY_OR
            )
        ));
    }

}
