<?php
define('APPLICATION_HOME', realpath(__DIR__.'/../../'));
define('VERSION', trim(file_get_contents(APPLICATION_HOME.'/VERSION')));
define('SITE_HOME', !empty($_SERVER['SITE_HOME']) ? $_SERVER['SITE_HOME'] : APPLICATION_HOME.'/data');

$loader = require APPLICATION_HOME.'/vendor/autoload.php';
$loader->addPsr4('Site\\', SITE_HOME);

include SITE_HOME.'/site_config.php';
include APPLICATION_HOME.'/src/Web/routes.php';
include APPLICATION_HOME.'/src/Web/access_control.php';

if (defined('GRAYLOG_DOMAIN') && defined('GRAYLOG_PORT')) {
    $graylog = new \Web\GraylogWriter(GRAYLOG_DOMAIN, GRAYLOG_PORT);
             set_error_handler([$graylog, 'error'    ]);
         set_exception_handler([$graylog, 'exception']);
    register_shutdown_function([$graylog, 'shutdown' ]);
}
