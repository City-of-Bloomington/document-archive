<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\List;

use Application\Files\FilesRepository;

class View extends \Web\View
{
    public function __construct(array  $files,
                                array  $params,
                                string $sort,
                                int    $total,
                                int    $itemsPerPage,
                                int    $currentPage)
    {
        parent::__construct();

        $this->vars = [
            'files'        => $files,
            'params'       => $params,
            'sort'         => $sort,
            'total'        => $total,
            'itemsPerPage' => $itemsPerPage,
            'currentPage'  => $currentPage,
            'origins'      => self::origins(),
            'departments'  => self::departments()
        ];
    }

    public function render(): string
    {
        return $this->twig->render('html/files/list.twig', $this->vars);
    }

    private static function origins(): array
    {
        return [
            ['value'=>''],
            ['value'=>'drupal']
        ];
    }

    private static function departments(): array
    {
        $t    = new FilesRepository();
        $opts = [['value'=>'']];
        foreach ($t->departments() as $d) { $opts[] = ['value'=>$d['name']]; }
        return $opts;
    }
}
