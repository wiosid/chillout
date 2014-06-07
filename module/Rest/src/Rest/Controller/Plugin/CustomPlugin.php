<?php

namespace Rest\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Validator;
use Rest\Model\BaseRest;

class CustomPlugin extends AbstractPlugin {

    protected $_db;
    public $_codeNumbers = array(
        "unauthenticated" => 401,
        "invalidLogin" => 402,
        "notFound" => 404,
        "exception" => 500,
        Validator\NotEmpty::IS_EMPTY => 1001,
        "invalid" => 1002,
        Validator\EmailAddress::INVALID_FORMAT => 1002,
        Validator\EmailAddress::INVALID => 1002,
        Validator\EmailAddress::INVALID_FORMAT => 1002,
        Validator\EmailAddress::INVALID_HOSTNAME => 1002,
        Validator\EmailAddress::INVALID_MX_RECORD => 1002,
        Validator\EmailAddress::INVALID_SEGMENT => 1002,
        Validator\EmailAddress::DOT_ATOM => 1002,
        Validator\EmailAddress::QUOTED_STRING => 1002,
        Validator\EmailAddress::INVALID_LOCAL_PART => 1002,
        Validator\EmailAddress::LENGTH_EXCEEDED => 1002,
        Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 1003,
        Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND => 1004,
        Validator\Identical::NOT_SAME => 1005,
        Validator\StringLength::TOO_SHORT => 1006,
        Validator\StringLength::TOO_LONG => 1007,
        Validator\InArray::NOT_IN_ARRAY => 1008,
        \Models\Validator\NotInArray::IN_ARRAY => 1009,
        "fileUpload" => 1010,
        "fileExtension" => 1011,
        Validator\Date::FALSEFORMAT => 1012,
        Validator\Digits::NOT_DIGITS => 1013,
        Validator\Uri::NOT_URI => 1014,
    );

    public function __construct($db) {

        $this->_db = $db;
        $this->db_return_type = \PDO::FETCH_ASSOC;
    }

    public function __invoke($db) {

        $this->_db = $db;
        $this->db_return_type = \PDO::FETCH_ASSOC;
        return $this;
    }

    public function __call($name, $arguements) {
        $baseRest = new BaseRest($this->_db);
        return $baseRest->{$name}($arguements[0],$arguements[1],$arguements[2],$arguements[3],$arguements[4]);
    }

}
