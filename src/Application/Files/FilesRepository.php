<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files;

use Application\PdoRepository;

class FilesRepository extends PdoRepository
{
    public const SORT_DEFAULT    = 'filename';
    public const FIELDS_REQUIRED = ['internalFilename', 'filename', 'mime_type', 'md5', 'origin', 'department', 'username'];
    public const FIELDS_OPTIONAL = ['origin_id', 'department', 'committee', 'type', 'date', 'title'];
    public const FIELDS_SORTABLE = ['filename', 'origin', 'uploaded', 'department', 'type', 'committee', 'date'];
    public const ORIGINS         = ['drupal', 'onboard', 'data'];

    public function __construct() { parent::__construct('files'); }

    public function loadById(int $id): ?array
    {
        $sql = 'select * from files where id=?';
        $q   = $this->pdo->prepare($sql);
        $q->execute([$id]);
        $r   = $q->fetchAll(\PDO::FETCH_ASSOC);
        if (count($r)) {
            return $r[0];
        }
        return null;
    }

    public function delete(int $id)
    {
        $del = $this->pdo->prepare('delete from files where id=?');
        $del->execute([$id]);
    }

    public function search(array $fields=[], string $order=self::SORT_DEFAULT, ?int $itemsPerPage=null, ?int $currentPage=null): array
    {
        $select = 'select * from files';
        $joins  = [];
        $where  = [];
        $params = [];

        foreach ($fields as $k=>$v) {
            switch ($k) {
                case 'filename':
                case 'mime_type':
                    $where[]    = "$k like :$k";
                    $params[$k] = "%$v%";
                break;

                case 'date':
                    // The date field is a full date time; however, people will
                    // be querying using only a date portion
                    $where[] = "date(date)=:date";
                    $params[$k] = $v;
                break;

                default:
                    $where[]    = "$k=:$k";
                    $params[$k] = $v;
            }
        }
        $sql = self::buildSql($select, $joins, $where, null, $order);
		return $this->performSelect($sql, $params, $itemsPerPage, $currentPage);
    }

    public function departments(): array
    {
        $q = $this->pdo->query('select name from departments order by name');
        return $q->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function types(): array
    {
        $q = $this->pdo->query('select distinct type from files where type is not null order by type');
        return $q->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function committees(): array
    {
        $q = $this->pdo->query('select distinct committee from files where committee is not null order by committee');
        return $q->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function validate(array $file): array
    {
        $errors   = [];
        foreach (self::FIELDS_REQUIRED as $f) {
            if (empty($file[$f])) { $errors[] = "Missing $f"; }
        }

        if (!in_array($file['origin'], self::ORIGINS)) {
            $errors[] = 'Invalid origin';
        }
        return $errors;
    }
}
