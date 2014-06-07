<?php

namespace Backend\Controller;

use Zend\Cache\Storage\Event;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ReportAbusesController extends AbstractActionController {

    public function indexAction() {

        if (!$this->isUserLoggedIn()) {
            return $this->redirect()->toUrl(BASE_URL . '/user/login');
        }

        $db = $this->getDB();
        $_tblReportAbuses = $this->getServiceLocator()->get('Models\Model\ReportAbusesTable');
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            $response = $flashMessenger->getMessages();
        }
        $reportAbusesArray = $_tblReportAbuses->fetchAll();
        foreach ($reportAbusesArray as $rAbKey => $rAbValue) {
            $reportAbusesArray[$rAbKey]['user'] = $this->customPlugin($db)->getUserObeject($rAbValue['fld_user_id']);
            $reportAbusesArray[$rAbKey]['other_user'] = $this->customPlugin($db)->getUserObeject($rAbValue['fld_other_user_id']);
            
        }
        return new ViewModel(array(
            'controller' => 'report-abuses',
            'action' => 'index',
            'reportAbuses' => array_values($reportAbusesArray),
            'response' => $response[0]
        ));
    }


    private function getDB() {
        return $this->getServiceLocator()->get('db');
    }

    protected function isUserLoggedIn() {
        return $this->zfcUserAuthentication()->hasIdentity();
    }

}
