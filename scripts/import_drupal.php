<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
define('DRUPAL_HOME', '/srv/data/drupal');
define('SITE_HOME', $_SERVER['SITE_HOME']);
include SITE_HOME.'/site_config.php';
include './Drupal.php';

$importer = new Import($DATABASES['default']);
$drupal   = new Drupal($DATABASES['drupal' ]);

$sql     = "select f.fid,
                   f.filename,
                   f.filemime,
                   replace(f.uri, 'public:/', '') as path,
                   from_unixtime(f.created)       as created,
                   du.name                        as username,
                   wu.department
            from      file_managed         f
                 join users_field_data    du on  f.uid=du.uid
                 join webscan.users       wu on du.name=wu.username
            left join webscan.grackle_results g on replace(f.uri, 'public:/', '')=replace(g.url, 'https://bloomington.in.gov/sites/default/files', '')
            where f.filemime like 'application/pdf'
              and (g.score is null or g.score<90)";
$query   = $drupal->pdo->query($sql);
$files   = $query->fetchAll(\PDO::FETCH_ASSOC);
foreach ($files as $f) {
    echo "$f[fid] $f[path]\n";

    $archive_id = $importer->import_file($f);
    echo "archive_id: $archive_id\n";
    $drupal->update_links($f, $archive_id);
    echo "-------------------------------------------------\n";
}

class Import
{
    public \PDO          $pdo;
    public \PDOStatement $insert;

    public function __construct(array $config)
    {
        $this->pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

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
        $this->insert = $this->pdo->prepare("insert into files ($col) values($par)");
    }

    public function import_file(array $file): int
    {
        $original  = DRUPAL_HOME.'/files'.$file['path'];
        $md5       = md5_file($original);
        if (!$md5) {
            echo "No such file: $original\n";
            exit();
        }

        $uploaded  = new \DateTime($file['created']);
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
            'origin_id'        => $file['fid'       ],
            'filename'         => $file['filename'  ],
            'mime_type'        => $file['filemime'  ],
            'uploaded'         => $file['created'   ],
            'username'         => $file['username'  ],
            'department'       => $file['department'],
        ];
        $this->insert->execute($d);
        print_r($d);
        $archive_id = (int)$this->pdo->lastInsertId();
        return $archive_id;
    }
}
