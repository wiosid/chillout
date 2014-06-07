<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Bmsbackend for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Backend;

use Backend\View\Helper\Navigation;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Backend\Form\UserRoleFieldset;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

class Module implements AutoloaderProviderInterface, FormElementProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
    	$e->getApplication()->getServiceManager()->get('translator');
    	$e->getApplication()->getServiceManager()->get('viewhelpermanager')->setFactory('isActive', function($sm) use ($e) {
    		$isActive = new View\Helper\IsActive($e->getRouteMatch());
    		$isActive->setServiceLocator($e->getApplication()->getServiceManager());
    		return $isActive;
    	});
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    public function getFormElementConfig()
    {
    	return array(
    			'factories' => array(
    					'UserFieldset' => function($sm){
    						$serviceLocator = $sm->getServiceLocator();
    						$fieldset = new \Backend\Form\UserFieldset();
    						$zfcuserAuthService = $serviceLocator->get('zfcuser_auth_service');
    						if($zfcuserAuthService->hasIdentity()){
    							$dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
    							$id = $zfcuserAuthService->getIdentity()->getId();
    							$fieldset->setUserId($id);
    							$fieldset->setDbAdapter( $dbAdapter );
    						}
    						return $fieldset;
    					},
    					
    					'pageForm' => function ($sm){ 
    						$serviceLocator = $sm->getServiceLocator();
    						$pageTable = $serviceLocator->get('Models\Model\pageTable');
    						$pageCategories = $pageTable->getAllPageCategories();
    						$pageForm = new \Backend\Form\PageForm();
    						$pageForm->setPageCategories($pageCategories);
    						return $pageForm;
    					},
    					'widgetForm' => function ($sm){
    						$widgetForm = new \Backend\Form\WidgetForm();
    						return $widgetForm;
    					}
    			)
    	);
    }
    
   public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'renderAttribute' => function ($serviceManager) {
                	// Get the service locator 
               		 $serviceLocator = $serviceManager->getServiceLocator();
                    return new \Backend\View\Helper\RenderAttribute($serviceLocator);
                },
               'navigation' => function ( $sm ){
                	return new Navigation( $sm );
                }
            )
        );
    }
    
}
