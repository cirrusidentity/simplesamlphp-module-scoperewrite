<?php
$config = [];
// require a vanilla SSP config
require dirname(__DIR__, 2) . "/vendor/simplesamlphp/simplesamlphp/config/config.php.dist";
$config['module.enable']['scoperewrite'] = true;
$config['baseurlpath'] = '/';
$config['logging.handler'] = 'stderr';
$config['logging.level'] = \SimpleSAML\Logger::DEBUG;
