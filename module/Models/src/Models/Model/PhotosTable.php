<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;

class PhotosTable {

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

    public function fetchPhotos(array $ids) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id', 'fld_name'))->where(array('id' => $ids));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }

    public function getUserAllPhotos($fldUserId) {
        $select = new Select($this->tableGateway->table);
        $select->columns(array('id', 'fld_name','width','height'))->where(array('fld_user_id' => $fldUserId));
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }

    public function savePhoto($data) {
//        $data['fld_datetime'] = time();
        $this->tableGateway->insert($data);
        $id = $this->tableGateway->lastInsertValue;
        return $id;
    }

    public function deletePhoto($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function deleteUserPhotos($fldUserId) {
        $this->tableGateway->delete(array('fld_user_id' => $fldUserId));
    }

}

