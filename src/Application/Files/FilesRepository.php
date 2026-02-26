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
    public const SORT_DEFAULT = 'filename';
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
        $q = $this->pdo->query('select * from departments');
        return $q->fetchAll(\PDO::FETCH_ASSOC);
    }
}
