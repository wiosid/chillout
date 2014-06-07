<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class ArchiveMessagesTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }
    
    public function totalCount() {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table);
        $select->where(
                array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.dir', Predicate\Operator::OPERATOR_EQUAL_TO, 1),
                            )
                    )
                )
        );
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->count();
    }

    public function getLastMessage($me, $otherUser, $query) {
        $direction = $query['direction'];
        $messageId = (int) $query['message_id'];
        $datetime = $query['datetime'];
        if (empty($adId)) {
            $adId = "";
        }
        if (empty($datetime)) {
            $direction = "";
            $datetime = 0;
        } else {
//            $datetime = date("Y-m-d H:i:s", $query['datetime']);
        }
        $length = (int) $query['length'];
        if (empty($length)) {
            $length = 10;
        }
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id' => 'id', 'body' => 'body', 'updated_on' => new \Zend\Db\Sql\Expression('UNIX_TIMESTAMP(' . $this->tableGateway->table . '.utc)')));

        $select->join(array('ac' => 'archive_collections'), $this->tableGateway->table . '.coll_id = ac.id', array('with_user'));

        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($otherUser, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.us', Predicate\Operator::OPERATOR_EQUAL_TO, $otherUser . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );
//        $select->where("ac.with_user='" . addcslashes($otherUser, "'") . "'");
        $select->where(array('dir' => 1));
        if ($direction == 'prev') {
            $datetimeOperator = Predicate\Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO;
            $messageOperator = Predicate\Operator::OPERATOR_LESS_THAN;
        } else {
            $datetimeOperator = Predicate\Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO;
            $messageOperator = Predicate\Operator::OPERATOR_GREATER_THAN;
        }
        if (!empty($datetime)) {
            $select->where(array(
                'UNIX_TIMESTAMP(' . $this->tableGateway->table . '.utc) ' . $datetimeOperator . $datetime
                    )
            );
        }
        if (!empty($messageId)) {
            $select->where(array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\Operator($this->tableGateway->table . '.id ', $messageOperator, $messageId)
                        )
                )
                    )
            );
        }

        $select->order($this->tableGateway->table . '.utc DESC');
        $select->limit(1);
//        echo $select->getSqlString();die;
        $result = $this->tableGateway->selectWith($select)->toArray();
        return $result[0];
    }

    public function getChatMessages($me, $otherUser, $query) {
        $direction = $query['direction'];
//        $adId = (int) $query['ad_id'];
        $messageId = (int) $query['message_id'];
        $datetime = $query['datetime'];
        if (empty($datetime)) {
            $direction = "";
            $datetime = 0;
        } else {
//            $datetime = date("Y-m-d H:i:s", strtotime($query['datetime']));
        }
        $length = (int) $query['length'];
        if (empty($length)) {
            $length = 10;
        }
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id' => 'id', 'body' => 'body', 'updated_on' => new \Zend\Db\Sql\Expression('UNIX_TIMESTAMP(' . $this->tableGateway->table . '.utc)')));

        $select->join(array('ac' => 'archive_collections'), $this->tableGateway->table . '.coll_id = ac.id', array('with_user'));

        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@54.186.168.24')
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($otherUser, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.us', Predicate\Operator::OPERATOR_EQUAL_TO, $otherUser . '@54.186.168.24')
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator('ac.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );
//        $select->where("ac.with_user='" . addcslashes($otherUser, "'") . "'");
        $select->where(array('dir' => 1));
        if ($direction == 'prev') {
            $datetimeOperator = Predicate\Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO;
            $messageOperator = Predicate\Operator::OPERATOR_LESS_THAN;
        } else {
            $datetimeOperator = Predicate\Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO;
            $messageOperator = Predicate\Operator::OPERATOR_GREATER_THAN;
        }
        if (!empty($datetime)) {
            $select->where(array(
                'UNIX_TIMESTAMP(' . $this->tableGateway->table . '.utc) ' . $datetimeOperator . $datetime
                    )
            );
        }
        if (!empty($messageId)) {
            $select->where(array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\Operator($this->tableGateway->table . '.id ', $messageOperator, $messageId)
                        )
                )
                    )
            );
        }

        $select->order($this->tableGateway->table . '.utc DESC');
        $select->limit($length);
//        echo $select->getSqlString();die;
        $result = $this->tableGateway->selectWith($select);
        return $result->toArray();
    }

    public function deleteCollectionMessages($collectionIds) {
        $collectionIds = (array) array_unique(array_filter($collectionIds));
        if (!empty($collectionIds)) {
            $this->tableGateway->delete(array(
                new Predicate\In($this->tableGateway->table . '.coll_id', $collectionIds)
                    )
            );
        }
    }

}
