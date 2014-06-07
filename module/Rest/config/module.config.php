<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Rest\Controller\Index' => 'Rest\Controller\IndexController',
            'Rest\Controller\Default' => 'Rest\Controller\DefaultController',
            'Rest\Controller\Users' => 'Rest\Controller\UsersController',
            'Rest\Controller\Photos' => 'Rest\Controller\PhotosController',
            'Rest\Controller\Friends' => 'Rest\Controller\FriendsController',
            'Rest\Controller\Questions' => 'Rest\Controller\QuestionsController',
            'Rest\Controller\BlockUsers' => 'Rest\Controller\BlockUsersController',
            'Rest\Controller\ReportAbuses' => 'Rest\Controller\ReportAbusesController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'customPlugin' => 'Rest\Controller\Plugin\CustomPlugin',
        )
    ),
    'router' => array(
        'routes' => array(
//            'home' => array(
//                'type' => 'Literal',
//                'options' => array(
//                    // Change this to something specific to your module
//                    'route' => '/',
//                    'defaults' => array(
//                        // Change this value to reflect the namespace in which
//                        // the controllers for your module are found
//                        '__NAMESPACE__' => 'Rest\Controller',
//                        'controller' => 'Default',
//                         'action' => 'index',
//                    ),
//                ),
//                ),
            'verify-code' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/verify-code',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
//                        '__NAMESPACE__' => 'Rest\Controller',
                        'controller' => 'Rest\Controller\Default',
                         'action' => 'verify-code',
                    ),
                ),
                ),
            'Rest' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/api',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Rest\Controller',
                        'controller' => 'Rest\Controller\Index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:method]][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'method' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                // Change this value to reflect the namespace in which
                                // the controllers for your module are found
                                '__NAMESPACE__' => 'Rest\Controller',
                                'controller' => 'Rest\Controller\Index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'mailtemplate/user/email-verification' => __DIR__ . '/../view/user/email-verification.phtml',
            'mailtemplate/user/email-launch' => __DIR__ . '/../view/user/email-launch.phtml',
            'mailtemplate/user/email-verification-text' => __DIR__ . '/../view/user/email-verification-text.phtml',
            'mailtemplate/user/email-launch-text' => __DIR__ . '/../view/user/email-launch-text.phtml',
        ),
    ),
    'PATH_PROJECT' => realpath(dirname(__FILE__)) . '/..',
    'SITE_URL' => $_SERVER['HTTP_HOST'],
    // Doctrine config
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        )
    )
);
