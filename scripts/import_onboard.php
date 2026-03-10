<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
define('ONBOARD_HOME', '/srv/data/onboard');
define('SITE_HOME', $_SERVER['SITE_HOME']);
include SITE_HOME.'/site_config.php';
include './OnBoard.php';

$importer = new  Import($DATABASES['default']);
$onboard  = new OnBoard($DATABASES['onboard']);

$queries = [
    'meetingFiles' =>
    "select f.id, f.type, f.internalFilename, f.filename, f.mime_type, f.created,
            f.title,
            m.start,
            c.name  as committee,
            u.username
     from meetingFiles f
     join meetings     m on m.id=f.meeting_id
     join committees   c on c.id=m.committee_id
     left join people  u on u.id=f.updated_by",

    'legislationFiles' =>
    "select f.id, f.internalFilename, f.filename, f.mime_type, f.created,
            null   as title,
            null   as start,
            t.name as type,
            c.name as committee,
            u.username
     from legislationFiles f
     join legislation      l on l.id=f.legislation_id
     join legislationTypes t on t.id=l.type_id
     join committees       c on c.id=l.committee_id
     left join people      u on u.id=f.updated_by",

    'reports' =>
    "select f.id, f.internalFilename, f.filename, f.mime_type, f.created,
            f.title,
            f.reportDate as start,
             'Report'    as type,
            c.name       as committee,
            u.username
     from reports f
     join committees c on c.id=f.committee_id
     left join people u on u.id=f.updated_by"
];


foreach ($queries as $table=>$sql) {
    $query   = $onboard->pdo->query($sql);
    $files   = $query->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($files as $f) {
        echo "$f[id] $f[internalFilename]\n";

        $archive_id = $importer->import_file($f, $table);
        echo "archive_id: $archive_id\n";
        $onboard->update_links($f, $archive_id, $table);
        echo "-------------------------------------------------\n";
    }
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
            'origin',
            'origin_id',
            'committee',
            'type',
            'date',
            'title'
        ];
        $col    = implode(',', $fields);
        $par    = implode(',', array_map(fn($f): string => ":$f", $fields));
        $this->insert = $this->pdo->prepare("insert into files ($col) values($par)");
    }

    public function import_file(array $file, string $table): int
    {
        $original  = ONBOARD_HOME."/$table/".$file['internalFilename'];
        $md5       = md5_file($original);
        if (!$md5) {
            echo "No such file: $original\n";
            exit();
        }

        $uploaded  = new \DateTime($file['created']);
        $ym        = $uploaded->format('Y/m');
        $internal  = "/$ym/".basename($file['internalFilename']);

        $directory = SITE_HOME.'/files';
        if (!is_dir("$directory/$ym")) {
            mkdir  ("$directory/$ym", 0775, true);
        }
        copy($original, $directory.$internal);

        $d = [
            'origin'           => 'onboard',
            'internalFilename' => $internal,
            'md5'              => $md5,
            'origin_id'        => $file['id'        ],
            'filename'         => $file['filename'  ],
            'mime_type'        => $file['mime_type' ],
            'uploaded'         => $file['created'   ],
            'username'         => $file['username'  ],
            'committee'        => $file['committee' ],
            'type'             => $file['type'      ],
            'date'             => $file['start'     ],
            'title'            => $file['title'     ]
        ];
        $this->insert->execute($d);
        print_r($d);
        $archive_id = (int)$this->pdo->lastInsertId();
        return $archive_id;
    }
}
