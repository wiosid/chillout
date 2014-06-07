<?php

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
require_once 'config.php';

chdir(dirname(__DIR__));

function _pr($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function _pre($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die();
}

function my_exec($cmd, $input = '') {
    putenv("HOME=/tmp");
    $proc = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
    fwrite($pipes[0], $input);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $rtn = proc_close($proc);
    return array('stdout' => $stdout,
        'stderr' => $stderr,
        'return' => $rtn
    );
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
