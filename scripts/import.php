<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
define('DRUPAL_HOME', '/srv/data/drupal');
define('SITE_HOME', $_SERVER['SITE_HOME']);
include SITE_HOME.'/site_config.php';

$importer = new Importer($DATABASES);

$sql     = "select f.fid,
                   f.filename,
                   f.filemime,
                   replace(f.uri, 'public:/', '') as path,
                   from_unixtime(f.created)       as created,
                   du.name                        as username,
                   wu.department
            from      file_managed         f
                 join users_field_data    du on  f.uid=du.uid
                 join wave.users          wu on du.name=wu.username
            left join wave.grackle_results g on replace(f.uri, 'public:/', '')=replace(g.url, 'https://bloomington.in.gov/sites/default/files', '')
            where f.filemime like 'application/pdf'
              and (g.score is null or g.score<90)";
$query   = $importer->drupal->query($sql);
$files   = $query->fetchAll(\PDO::FETCH_ASSOC);
foreach ($files as $f) {
    echo "$f[fid] $f[path]\n";

    $archive_id = $importer->import_file($f);
    echo "archive_id: $archive_id\n";
    $importer->update_links($f, $archive_id);
    echo "\n";
}

class Importer
{
    public \PDO $drupal;
    public \PDO $archive;

    public \PDOStatement $insert;

    public function __construct(array $config)
    {
        $this->drupal  = $this->db_connect($config['drupal' ]);
        $this->archive = $this->db_connect($config['default']);

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
        $this->insert = $this->archive->prepare("insert into files ($col) values($par)");
    }

    private function db_connect(array $config): \PDO {
        $pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
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
        $archive_id = (int)$this->archive->lastInsertId();
        return $archive_id;
    }

    function update_links(array $file, int $archive_id)
    {
        echo "Update links to archive_id: $archive_id\n";
        $encoded = str_replace('%', '\%', rawurlencode($file['filename']));

        $content_tables = [
            'node__body'                 => 'body_value',
            'node__field_aside'          => 'field_aside_value',
            'node__field_details'        => 'field_details_value',
        ];
        $links_tables = [
            'node__field_related_links'  => 'field_related_links_uri',
            'node__field_call_to_action' => 'field_call_to_action_uri',
            'paragraph__field_info_link' => 'field_info_link_uri'
        ];

        foreach ($content_tables as $table=>$field) {
            $this->replace_links_in_html($table, $field, $file['filename'], (int)$file['fid'], $archive_id);
            $this->replace_links_in_html($table, $field, $encoded,          (int)$file['fid'], $archive_id);
        }
    }

    /**
     * Search and replace links to filename and urlencoded filename in HTML
     */
    function replace_links_in_html(string $table, string $field, string $filename, int $origin_id, int $archive_id)
    {
        $host  = 'https\:\/\/bloomington\.in\.gov';
        $path  = '\/sites\/default\/files\/[[:digit:]]{4}-[[:digit:]]{2}';

        $regex = "href=\"(($host)?$path\/$filename)\"";
        $sql   = "select entity_id, $field from $table where regexp_like($field, ?)";
        $query = $this->drupal->prepare($sql);
        $query->execute([$regex]);
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $regex  = "/href=\"(($host)?$path\/$filename)\"/";
            $html   = preg_replace($regex, "href=\"https://aoi.bloomington.in.gov/archive?origin_id=$origin_id&archive_id=$archive_id\"", $r[$field]);
            $sql    = "update $table set $field=? where entity_id=?";
            $update = $this->drupal->prepare($sql);
            $update->execute([$html, $r['entity_id']]);
            print_r($r);
            echo $html."\n";
        }
    }

    function replace_links_in_url_fields(string $table, string $field, string $filename, int $origin_id, int $archive_id)
    {
    }
}
