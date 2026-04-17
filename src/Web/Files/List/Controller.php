<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\List;

use Application\Files\FilesRepository;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        $repo   = new FilesRepository();
        $page   = !empty($_GET['page']) ? (int)$_GET['page'] : 1;

        $params = self::cleanParameters();
        $sort   = self::prepareSort(  $params['sort'] ?? FilesRepository::SORT_DEFAULT );
        $search = self::prepareSearch($params);
        $list   = $repo->search(fields:$search,
                                 order:$sort,
                          itemsPerPage:parent::ITEMS_PER_PAGE,
                           currentPage:$page);

        return new View($list['rows'] ?? [],
                        $params,
                        $sort,
                        $list['total'] ?? 0,
                        parent::ITEMS_PER_PAGE,
                        $page);
    }

    private static function cleanParameters(): array
    {
        $fields = ['filename', 'mime_type', 'origin', 'department', 'type', 'committee', 'date', 'sort'];
        $params = [];
        $regex  = '/[^a-zA-Z0-9_\/\s\-\.\(\)]/';
        foreach ($fields as $f) {
            if (!empty($_GET[$f])) {
                switch ($f) {
                    case 'date':
                        $date       = new \DateTime($_GET['date']);
                        $params[$f] = $date->format('Y-m-d');
                    break;
                    default:
                        $params[$f] = preg_replace($regex, '', $_GET[$f]);
                }
            }
        }
        return $params;
    }

    private static function prepareSearch(array $params): array
    {
        $s = [];
        $fields = ['filename', 'mime_type', 'origin', 'department', 'type', 'committee', 'date'];
        foreach ($fields as $f) {
            if (!empty($params[$f])) { $s[$f] = $params[$f]; }
        }
        return $s;
    }

    private static function prepareSort(string $sort): ?string
    {
        $s = explode(' ', $sort);
        if (in_array($s[0], FilesRepository::FIELDS_SORTABLE)) {
            return (isset($s[1]) && $s[1]=='desc')
                    ? "$s[0] desc"
                    :  $s[0];
        }
        return null;
    }
}
