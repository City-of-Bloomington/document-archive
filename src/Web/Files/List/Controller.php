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

        $sort   = FilesRepository::SORT_DEFAULT;
        $params = self::cleanParameters();
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
        $fields = ['filename', 'mime_type', 'origin', 'department', 'sort'];
        $params = [];
        $regex  = '/[^a-zA-Z0-9\/\s\\\-]/';
        foreach ($fields as $f) {
            if (!empty($_GET[$f])) {
                $params[$f] = preg_replace($regex, '', $_GET[$f]);
            }
        }
        return $params;
    }

    private static function prepareSearch(): array
    {
        $s = [];
        $fields = ['filename', 'mime_type', 'origin', 'department'];
        foreach ($fields as $f) {
            if (!empty($_GET[$f])) { $s[$f] = $_GET[$f]; }
        }
        return $s;
    }
}
