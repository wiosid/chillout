<?php

namespace Models\Model;

use Zend\Form\Annotation\Object;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\Model\ViewModel;
use Zend\Cache\Storage\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Predicate;

class ReportAbusesTable {

    protected $tableGateway;
    protected $reportAbuseTable;
    protected $db;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $this->db = $tableGateway->getAdapter()->getDriver()->getConnection()->getResource();
    }
    
    public function fetchAll() {
        $select = new Select($this->tableGateway->table);
        $select->order('fld_datetime DESC');
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet->toArray();
    }
    
    public function addReport($data) {
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
    
}

