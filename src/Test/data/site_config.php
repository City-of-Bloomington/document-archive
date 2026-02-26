<?php
define('APPLICATION_NAME','archive');

define('BASE_URI' , '/archive');
define('BASE_HOST', 'localhost');
define('BASE_URL' , 'https://'.BASE_HOST.BASE_URI);
define('USWDS_URL', '/static/uswds/dist');


/**
 * Database Setup
 * Refer to the PDO documentation for DSN sytnax for your database type
 * http://www.php.net/manual/en/pdo.drivers.php
 */
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
