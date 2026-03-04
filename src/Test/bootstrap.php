<?php
// -- site_config.php --
define('APPLICATION_NAME','archive');

define('BASE_URI' ,   '/archive');
define('BASE_HOST',   'localhost');
define('BASE_URL' ,   'https://'.BASE_HOST.BASE_URI);
define('USWDS_URL',   '/static/uswds/dist');
define('DRUPAL_SITE', 'https://localhost/drupal');

$DATABASES = [
    'default' => [
        'dsn'  => 'mysql:dbname=archive;host=localhost',
        'user' => 'test',
        'pass' => 'h++pd',
    ],
    'drupal' => [
        'dsn'  => 'mysql:dbname=drupal;host=localhost',
        'user' => 'test',
        'pass' => 'h++pd',
    ]
];

define('DATE_FORMAT', 'n/j/Y');
define('TIME_FORMAT', 'g:i a');
define('DATETIME_FORMAT', 'n/j/Y g:i a');

define('LOCALE', 'en_US');

// -- Bootstrap --
define('APPLICATION_HOME', realpath(__DIR__.'/../../'));
define('VERSION', trim(file_get_contents(APPLICATION_HOME.'/VERSION')));
define('SITE_HOME', __DIR__.'/data');

$loader = require APPLICATION_HOME.'/vendor/autoload.php';
$loader->addPsr4('Site\\', SITE_HOME);

include APPLICATION_HOME.'/src/Web/routes.php';
include APPLICATION_HOME.'/src/Web/access_control.php';

$locale = LOCALE.'.utf8';
putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain('labels',   APPLICATION_HOME.'/language');
bindtextdomain('messages', APPLICATION_HOME.'/language');
bindtextdomain('errors',   APPLICATION_HOME.'/language');
textdomain('labels');

if (defined('GRAYLOG_DOMAIN') && defined('GRAYLOG_PORT')) {
    $graylog = new \Web\GraylogWriter(GRAYLOG_DOMAIN, GRAYLOG_PORT);
             set_error_handler([$graylog, 'error'    ]);
         set_exception_handler([$graylog, 'exception']);
    register_shutdown_function([$graylog, 'shutdown' ]);
}

// phpstan global declarations
$GLOBALS['ROUTES'   ] = $ROUTES;
$GLOBALS['DATABASES'] = $DATABASES;
