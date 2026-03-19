<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

class Drupal
{
    public \PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    function update_links(array $file, int $archive_id)
    {
        echo "Update links to archive_id: $archive_id\n";
        $encoded = rawurlencode($file['filename']);
        $url     = BASE_URL."?origin=drupal&origin_id=$file[fid]&archive_id=$archive_id&filename=$encoded";
        $encoded = str_replace('%', '\%', $encoded);

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

            $this->replace_links_in_html($table, $field, $file['filename'], $url);
            $this->replace_links_in_html($table, $field, $encoded,          $url);
        }
        foreach ($links_tables as $table=>$field) {
            $this->replace_links_in_url_fields($table, $field, $file, $url);
        }
    }

    function replace_links_in_html(string $table, string $field, string $filename, string $url)
    {
        $host  = 'https\:\/\/bloomington\.in\.gov';
        $path  = '\/sites\/default\/files\/[[:digit:]]{4}-[[:digit:]]{2}';

        $regex = "href=\"(($host)?$path\/$filename)\"";
        $sql   = "select entity_id, $field from $table where regexp_like($field, ?)";
        $query = $this->pdo->prepare($sql);
        $query->execute([$regex]);
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $html   = preg_replace("/$regex/", "href=\"$url\"", $r[$field]);
            $sql    = "update $table set $field=? where entity_id=?";
            $update = $this->pdo->prepare($sql);
            echo "Updating $table for entity_id: $r[entity_id]\n";
            $success = $update->execute([$html, $r['entity_id']]);
            if (!$success) {
                echo "Failed: $sql\n";
                exit();
            }
        }
    }

    function replace_links_in_url_fields(string $table, string $field, array $file, string $url)
    {
        $original = 'https://bloomington.in.gov/sites/default/files'.$file['path'];
        $dir      = dirname($original);
        $encoded  = $dir.'/'.str_replace('%', '\%', rawurlencode($file['filename']));
        echo "Replacing $table: $original or $encoded\n";

        $sql      = "update $table set $field=? where $field=? or $field=?";
        $update   = $this->pdo->prepare($sql);
        $success  = $update->execute([$url, $original, $encoded]);
        if (!$success) {
            echo "Failed: $sql\n";
            exit();
        }
    }
}
