<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Backend\Controller\Index' => 'Backend\Controller\IndexController',
            'Backend\Controller\Users' => 'Backend\Controller\UsersController',
            'Backend\Controller\ReportAbuses' => 'Backend\Controller\ReportAbusesController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
        )
    ),
    /*
      'view_helpers' => array(
      'invokables' => array(
      'navigation' => 'Backend\View\Helper\Navigation',
      ),
      ),
     */
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Backend\Controller',
                        'controller' => 'Users',
                        'action' => 'index',
                    ),
                ),
            ),
            'Backend' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/admin',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Backend\Controller',
                        'controller' => 'Users',
                        'action' => 'index'
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
                            'route' => '/[:controller[/:action]][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*',
                            ),
                            'defaults' => array(
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
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'layout/header' => __DIR__ . '/../view/layout/_header.phtml',
            'layout/sidebar' => __DIR__ . '/../view/layout/_sidebar.phtml',
            'layout/footer' => __DIR__ . '/../view/layout/_footer.phtml',
            'backend/index/index' => __DIR__ . '/../view/backend/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'error/unauth' => __DIR__ . '/../view/error/unauthorized.phtml',
            'zfc-user/user/login' => __DIR__ . '/../view/zfc-user/user/login.phtml',
            'zfc-user/user/register' => __DIR__ . '/../view/zfc-user/user/register.phtml',
        ),
        'template_path_stack' => array(
            'Backend' => __DIR__ . '/../view',
        ),
    ),
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                'Backend' => __DIR__ . '/../public',
            ),
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
