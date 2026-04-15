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
     left join people  u on u.id=f.updated_by
     where f.internalFilename is not null",

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
     left join people      u on u.id=f.updated_by
     where f.internalFilename is not null",

    'reports' =>
    "select f.id, f.internalFilename, f.filename, f.mime_type, f.created,
            f.title,
            f.reportDate as start,
             'Report'    as type,
            c.name       as committee,
            u.username
     from reports f
     join committees c on c.id=f.committee_id
     left join people u on u.id=f.updated_by
     where f.internalFilename is not null"
];


foreach ($queries as $table=>$sql) {
    $query   = $onboard->pdo->query($sql);
    $files   = $query->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($files as $f) {
        echo "$f[id] $f[internalFilename] $f[start]\n";

        $archive_id = $importer->import_file($f, $table);
        echo "archive_id: $archive_id\n";
        $onboard->update_links($f, $archive_id, $table);
        echo "-------------------------------------------------\n";
    }

    $importer->update_committee_departments();
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

    public function update_committee_departments()
    {
        $committees = [
            "Animal Control Commission"                                      => 'Animal Shelter',
            "Banneker Advisory Council"                                      => 'Parks',
            "Bicycle and Pedestrian Safety Commission"                       => 'Planning',
            "BIDAC (Bloomington Industrial Development Advisory Commission)" => 'ESD',
            "Bloomington Arts Commission"                                    => 'ESD',
            "Bloomington Digital Underground Advisory Committee"             => 'ITS',
            "Bloomington/Monroe County Human Rights Commission"              => 'CFRD',
            "Board of Housing Quality Appeals"                               => 'HAND',
            "Board of Park Commissioners"                                    => 'Parks',
            "Board of Public Safety"                                         => 'Police',
            "Board of Public Works"                                          => 'Public Works',
            "Board of Zoning Appeals"                                        => 'Planning',
            "Capital Improvement Board (CIB)"                                => 'Council',
            "Cascades Golf Course Advisory Council"                          => 'Parks',
            "CDBG Funding Citizens Advisory Committee"                       => 'HAND',
            "Citizens' Redistricting Advisory Commission"                    => 'Council',
            "City Council"                                                   => 'Council',
            "City Council Administration Committee"                          => 'Council',
            "City Council Climate Action and Resilience Committee"           => 'Council',
            "City Council Community Affairs Committee"                       => 'Council',
            "City Council Housing Committee"                                 => 'Council',
            "City Council Land Use Committee"                                => 'Council',
            "City Council Public Safety Committee"                           => 'Council',
            "City Council Sustainable Development Committee"                 => 'Council',
            "City Council Transportation Committee"                          => 'Council',
            "City Council Utilities and Sanitation Committee"                => 'Council',
            "City of Bloomington Capital Improvement (CBCI)"                 => 'OOTM',
            "Commission on Aging"                                            => 'CFRD',
            "Commission on Hispanic and Latiné Affairs"                      => 'CFRD',
            "Commission on Sustainability and Resilience"                    => 'ESD',
            "Commission on the Status of Black Males"                        => 'CFRD',
            "Commission on the Status of Children & Youth"                   => 'CFRD',
            "Commission on the Status of Women"                              => 'CFRD',
            "Common Council Budget Task Force"                               => 'Council',
            "Common Council Committee on Council Processes"                  => 'Council',
            "Common Council Fiscal Committee"                                => 'Council',
            "Common Council Sidewalk Committee"                              => 'Council',
            "Community Advisory on Public Safety Commission"                 => 'Council',
            "Council for Community Accessibility"                            => 'CFRD',
            "Dispatch Policy Board"                                          => 'Police',
            "Dr. Martin Luther King Jr. Birthday Commission"                 => 'CFRD',
            "Economic Development Commission"                                => 'ESD',
            "Environmental Commission"                                       => 'Planning',
            "Environmental Resources Advisory Council"                       => 'Parks',
            "Farmers' Market Advisory Council"                               => 'Parks',
            "Firefighters Pension Board"                                     => 'Fire',
            "Hearing Officer"                                                => 'Planning',
            "Historic Preservation Commission"                               => 'HAND',
            "Hospital Re-Use Steering Committee"                             => 'OOTM',
            "Jack Hopkins Social Services Committee"                         => 'Council',
            "Monroe County Domestic Violence Coalition"                      => 'CFRD',
            "MPO Citizens Advisory Committee"                                => 'Planning',
            "MPO Policy Committee"                                           => 'Planning',
            "MPO Technical Advisory Committee"                               => 'Planning',
            "Parking Commission"                                             => 'Parking',
            "Plan Commission"                                                => 'Planning',
            "Plat Committee"                                                 => 'Planning',
            "Public Safety Local Income Tax Committee"                       => 'Council',
            "Redevelopment Commission"                                       => 'HAND',
            "Traffic Commission"                                             => 'Planning',
            "Transportation Commission"                                      => 'Planning',
            "Tree Commission"                                                => 'Parks',
            "Urban Enterprise Association"                                   => 'ESD',
            "Utilities Service Board"                                        => 'Utilities'
        ];

        $update = $this->pdo->prepare('update files set department=? where committee=?');
        foreach ($committees as $c=>$d) {
            $update->execute([$d, $c]);
        }
    }
}
