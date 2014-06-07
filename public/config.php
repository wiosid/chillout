<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors',true);

if(is_ip($_SERVER['HTTP_HOST'])){
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/stupid-cupid/public');
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']."/stupid-cupid/public");
}else{
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/stupid-cupid/public');
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']."/stupid-cupid/public");
}

define('DEFAULT_ROLE',2);
define('BASE_URL', SITE_URL);

define('FILE_PATH', BASE_PATH."/uploads/user");
define('FILE_URL', BASE_URL."/uploads/user");
define('PROFILE_PIC_PATH', BASE_PATH."/uploads/profile");
define('PROFILE_PIC_URL', BASE_URL."/uploads/profile");

define('CHAT_FILE_PATH', BASE_PATH."/uploads/chats");
define('CHAT_FILE_URL', BASE_URL."/uploads/chats");

define('PHOTO_PATH', BASE_PATH."/uploads/photos");
define('PROFILE_PIC_URL', BASE_URL."/uploads/photos");
define('UPLOAD_PATH', PROJECT_BASE_PATH.'/assets/uploads');

define('IOS_CERTIFICATE_PATH', BASE_PATH."/certificates/ck.pem");
define('IOS_PASSPHRASE', "krishna@123");

define('MYSQL_DATE_FORMAT','Y-m-d');
define('MYSQL_DATETIME_FORMAT','Y-m-d H:i:s');
define('DATE_FORMAT','Y-m-d');
define('DATETIME_FORMAT','Y-m-d H:i:s');
define('TIME_FORMAT', 'H:i:s');
define('JQUERY_DATE_FORMAT','d-m-Y');
define('ADMIN_EMAIL','gangwar.ramji@gmail.com');
define('FRONTEND_CSS_VERSION','0.0.0');
define('FRONTEND_JS_VERSION','0.0.0');
define('BACKEND_CSS_VERSION','0.0.0');
define('BACKEND_JS_VERSION','0.0.0');


define('ADMIN_CONTACT_EMAIL','gangwar.ramji@gmail.com');
define('NO_REPLY_EMAIL','dev@8bitinc.com');
//define('APPOSCF_UPLOAD_PATH', PROJECT_BASE_PATH.'/assets/uploads');

//define('APP_ID', '726613130691014');                        ///////demo
//define('APP_SECRET', '9980118547e9e2a15c9a3acd882d1416');   ///////demo

define('APP_ID', '1432417143668966');                        ///////demo
define('APP_SECRET', 'bf1b0991613a988340ef2fe707ee6e1f');   ///////demo

define('CHAT_SERVER_HOST', '54.186.168.24');


/* Swagger Configuration */

//define('SWAGGER_DISCOVERY_URL', SITE_URL . '/api-ui/api-docs.json');

function is_ip( $ip ){
    return count(explode('.', $ip)) == '4'? 4 : false ;
}
