<?php

namespace Models;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Models\Model\UsersTable;
use Models\Model\PhotosTable;
use Models\Model\HitsTable;
use Models\Model\FriendsTable;
use Models\Model\ReportAbusesTable;
use Models\Model\BlockUsersTable;
use Models\Model\QuestionsTable;
use Models\Model\AnswersTable;
use Models\Model\UserResponsesTable;
use Models\Model\ArchiveMessagesTable;
use Models\Model\ArchiveCollectionsTable;
use Models\Model\UserTokensTable;

class Module implements AutoloaderProviderInterface {

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Models\Model\UsersTable' => function($sm) {
            $tableGateway = $sm->get('UsersTableGateway');
            $table = new UsersTable($tableGateway);
            return $table;
        },
                'UsersTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('users', $dbAdapter);
        },
                'Models\Model\PhotosTable' => function($sm) {
            $tableGateway = $sm->get('PhotosTableGateway');
            $table = new PhotosTable($tableGateway);
            return $table;
        },
                'PhotosTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_photos', $dbAdapter);
        },
                'Models\Model\HitsTable' => function($sm) {
            $tableGateway = $sm->get('HitsTableGateway');
            $table = new HitsTable($tableGateway);
            return $table;
        },
                'HitsTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_user_hits', $dbAdapter);
        },
                'Models\Model\FriendsTable' => function($sm) {
            $tableGateway = $sm->get('FriendsTableGateway');
            $table = new FriendsTable($tableGateway);
            return $table;
        },
                'FriendsTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_friends', $dbAdapter);
        },
                'Models\Model\ReportAbusesTable' => function($sm) {
            $tableGateway = $sm->get('ReportAbusesTableGateway');
            $table = new ReportAbusesTable($tableGateway);
            return $table;
        },
                'ReportAbusesTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_report_abuses', $dbAdapter);
        },
                'Models\Model\BlockUsersTable' => function($sm) {
            $tableGateway = $sm->get('BlockUsersTableGateway');
            $table = new BlockUsersTable($tableGateway);
            return $table;
        },
                'BlockUsersTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_block_users', $dbAdapter);
        },
                'Models\Model\QuestionsTable' => function($sm) {
            $tableGateway = $sm->get('QuestionsTableGateway');
            $table = new QuestionsTable($tableGateway);
            return $table;
        },
                'QuestionsTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_questions', $dbAdapter);
        },
                'Models\Model\AnswersTable' => function($sm) {
            $tableGateway = $sm->get('AnswersTableGateway');
            $table = new AnswersTable($tableGateway);
            return $table;
        },
                'AnswersTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_answers', $dbAdapter);
        },
                'Models\Model\UserResponsesTable' => function($sm) {
            $tableGateway = $sm->get('UserResponsesTableGateway');
            $table = new UserResponsesTable($tableGateway);
            return $table;
        },
                'UserResponsesTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_user_responses', $dbAdapter);
        },
                'Models\Model\ArchiveMessagesTable' => function($sm) {
            $tableGateway = $sm->get('ArchiveMessagesTableGateway');
            $table = new ArchiveMessagesTable($tableGateway);
            return $table;
        },
                'ArchiveMessagesTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('archive_messages', $dbAdapter);
        },
                'Models\Model\ArchiveCollectionsTable' => function($sm) {
            $tableGateway = $sm->get('ArchiveCollectionsTableGateway');
            $table = new ArchiveCollectionsTable($tableGateway);
            return $table;
        },
                'ArchiveCollectionsTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('archive_collections', $dbAdapter);
        },
                'Models\Model\UserTokensTable' => function($sm) {
            $tableGateway = $sm->get('UserTokensTableGateway');
            $table = new UserTokensTable($tableGateway);
            return $table;
        },
                'UserTokensTableGateway' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            return new TableGateway('tbl_user_tokens', $dbAdapter);
        },
                'zfcuser_user_mapper' => function ($sm) {
            $options = $sm->get('zfcuser_module_options');
            $mapper = new \Models\Mapper\AdminUser();
            $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
            $entityClass = $options->getUserEntityClass();
            $mapper->setEntityPrototype(new $entityClass);
            $mapper->setHydrator(new \Models\Mapper\AdminUserHydrator());
            $mapper->setTableName($mapper->getTableName());
            return $mapper;
        },
            ),
        );
    }

}
