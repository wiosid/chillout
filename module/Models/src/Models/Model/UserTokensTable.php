<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class UserTokensTable {

    protected $tableGateway;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }

    public function save($data) {
        $id = $data['id'];
        if (!empty($id)) {
            $this->tableGateway->update($data, array('id' => $id));
        } else {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        }
        return $id;
    }

    public function isValidOauth($fldOauthToken = NULL) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array());
        $select->join('users', $this->tableGateway->table . '.fld_user_id = users.user_id', array('user_id', 'fld_name', 'username'));

        $select->where(array('fld_oauth_token' => $fldOauthToken));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return array_map(function($i) {
            return $i['fld_device_token'];
        }, $resultSet->toArray());
    }

    public function updateLastAccessedTime($fldOauthToken) {
        $this->tableGateway->update(array('fld_last_accessed_datetime' => time()), array('fld_oauth_token' => $fldOauthToken));
    }

    public function getDeviceTokensByUserId($fldUserId, $deviceType) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('fld_device_token'))->where(array('fld_user_id' => $fldUserId, 'fld_device_type' => $deviceType));
//         echo $select->getSqlString();
        $resultSet = $this->tableGateway->selectWith($select);
        return array_filter(
                array_unique(
                        array_map(function($i) {
                            return $i['fld_device_token'];
                        }, $resultSet->toArray())
                )
        );
    }

}
