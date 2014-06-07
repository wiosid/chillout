<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class ArchiveCollectionsTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function getUserAndFriendCollectionIds($me, $otherUser) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id' => 'id'));


        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($otherUser, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $otherUser . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );

        $resultSet = $this->tableGateway->selectWith($select);
        return (array) array_map(function($i) {
                    return $i['id'];
                }, $resultSet->toArray());
    }

    public function deleteUserAndFriendCollectionIds($me, $otherUser) {
        $this->tableGateway->delete(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($otherUser, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $otherUser . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );
    }

    public function getUserAllCollectionIds($me) {
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select();
        $select->from($this->tableGateway->table)->columns(array('id' => 'id'));


        $select->where(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );

        $resultSet = $this->tableGateway->selectWith($select);
        return (array) array_map(function($i) {
                    return $i['id'];
                }, $resultSet->toArray());
    }

    public function deleteUserAllCollectionIds($me) {
        $this->tableGateway->delete(array(
            new Predicate\PredicateSet(
                    array(
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.us', Predicate\Operator::OPERATOR_EQUAL_TO, $me . '@' . CHAT_SERVER_HOST)
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                new Predicate\PredicateSet(
                        array(
                    new Predicate\PredicateSet(
                            array(
                        new Predicate\Operator($this->tableGateway->table . '.with_user', Predicate\Operator::OPERATOR_EQUAL_TO, addcslashes($me, "'")),
                            ), Predicate\PredicateSet::OP_OR
                    ),
                        ), Predicate\PredicateSet::OP_AND
                ),
                    ), Predicate\PredicateSet::OP_OR
            )
                )
        );
    }

}
