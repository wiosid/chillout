<?php
/**
 * GoalioMailService Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
// $settings = array(

//     /**
//      * Transport Class
//      *
//      * Name of Zend Transport Class to use
//      */
//     'transport_class' => 'Zend\Mail\Transport\File',
    
//     'options_class' => 'Zend\Mail\Transport\FileOptions',
    
//     'options' => array(
// 	    'path'              => 'data/mail/',
// 	    'callback'  => function (\Zend\Mail\Transport\File $transport) {
// 	        return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
// 	    },
// 	),

//     /**
//      * End of GoalioMailService configuration
//      */
// );

//$settings = array(
//    'transport_class' => 'Zend\Mail\Transport\Smtp',
//
//    'options_class' => 'Zend\Mail\Transport\SmtpOptions',
//
//    'options' => array(
//        'host' => 'eleanor.websitewelcome.com',
//        'connection_class' => 'login',
//        'connection_config' => array(
//            'ssl' => 'tls',
//            'username' => 'krishna@jhopadi.com',
//            'password' => '123456'
//        ),
//        'port' => 587
//    )
//);
$settings = array(
    'transport_class' => 'Zend\Mail\Transport\Smtp',

    'options_class' => 'Zend\Mail\Transport\SmtpOptions',

    'options' => array(
        'host' => 'smtp.gmail.com',
        'connection_class' => 'login',
        'connection_config' => array(
            'ssl' => 'tls',
//            'username' => 'creativegroup1988@gmail.com',
//            'password' => 'Jj13@jj.',
            'username' => 'dev@8bitinc.com',
            'password' => 'devbit123'
        ),
        'port' => 587
    )
);

/**
 * You do not need to edit below this line
 */
return array(
    'goaliomailservice' => $settings,
);
