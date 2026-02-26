<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
define('DRUPAL_HOME', '/srv/data/drupal');
define('SITE_HOME', $_SERVER['SITE_HOME']);
include SITE_HOME.'/site_config.php';

$drupal  = db_connect($DATABASES['drupal' ]);
$archive = db_connect($DATABASES['default']);

$fields = [
           'internalFilename',
           'filename',
           'mime_type',
           'md5',
           'uploaded',
           'username',
           'department',
           'origin',
           'origin_id'
           ];
$col    = implode(',', $fields);
$par    = implode(',', array_map(fn($f): string => ":$f", $fields));
$insert = $archive->prepare("insert into files ($col) values($par)");

$sql     = "select f.fid,
                   f.filename,
                   f.filemime,
                   replace(f.uri, 'public:/', '') as path,
                   from_unixtime(f.created)       as created,
                   du.name                        as username,
                   wu.department
            from file_managed      f
            join users_field_data du on f.uid=du.uid
            join wave.users       wu on du.name=wu.username
            where f.filemime like 'application/pdf'";
$query   = $drupal->query($sql);
$files   = $query->fetchAll(\PDO::FETCH_ASSOC);
foreach ($files as $f) {
    echo "$f[fid] $f[path]\n";

    $original  = DRUPAL_HOME.'/files'.$f['path'];
    $md5       = md5_file($original);

    $uploaded  = new \DateTime($f['created']);
    $ym        = $uploaded->format('Y/m');
    $internal  = "/$ym/".uniqid();

    $directory = SITE_HOME.'/files';
    if (!is_dir("$directory/$ym")) {
        mkdir  ("$directory/$ym", 0775, true);
    }
    copy($original, $directory.$internal);

    $d = [
        'origin'           => 'drupal',
        'internalFilename' => $internal,
        'md5'              => $md5,
        'origin_id'        => $f['fid'       ],
        'filename'         => $f['filename'  ],
        'mime_type'        => $f['filemime'  ],
        'uploaded'         => $f['created'   ],
        'username'         => $f['username'  ],
        'department'       => $f['department'],
    ];
    $insert->execute($d);
}

function db_connect(array $config): \PDO {
    $pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

