<?php
$config = array(
    'baseurlpath' => '/',
    'debug' => true,
    'logging.level' => SimpleSAML\Logger::DEBUG,
    'logging.handler' => 'stderr',

    'auth.adminpassword' => 'integrationtest',

    // currently: ignored 'templatedir' => dirname(dirname(dirname(__DIR__)))

    'trusted.url.domains' => array(),

);
