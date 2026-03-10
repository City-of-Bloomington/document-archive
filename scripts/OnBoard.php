<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

class OnBoard
{
    public \PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new \PDO($config['dsn'], $config['user'], $config['pass']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function update_links(array $file, int $archive_id, string $table)
    {
        echo "Update links to archive_id: $archive_id\n";
        $encoded = rawurlencode($file['filename']);
        $url     = "/archive?origin_id=$file[id]&archive_id=$archive_id&filename=$encoded";
        $encoded = str_replace('%', '\%', $encoded);

        $sql = "update $table set url=? where id=?";
        $up  = $this->pdo->prepare($sql);
        $s   = $up->execute([$url, $file['id']]);
        if (!$s) {
            echo "Failed: $sql\n";
            exit();
        }
    }
}
