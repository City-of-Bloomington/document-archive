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
    private FilesRepository $files;

    public function __construct(array  $files,
                                array  $params,
                                string $sort,
                                int    $total,
                                int    $itemsPerPage,
                                int    $currentPage)
    {
        parent::__construct();

        $this->files = new FilesRepository();

        $this->vars = [
            'files'        => $files,
            'params'       => $params,
            'sort'         => $sort,
            'total'        => $total,
            'itemsPerPage' => $itemsPerPage,
            'currentPage'  => $currentPage,
            'origins'      => self::origins(),
            'departments'  => $this->departments(),
            'types'        => $this->types(),
            'committees'   => $this->committees()
        ];
    }

    public function render(): string
    {
        return $this->twig->render('html/files/list.twig', $this->vars);
    }

    private static function origins(): array
    {
        $opts = [['value' => '']];
        foreach (FilesRepository::$origins as $o) { $opts[] = ['value'=>$o, 'label'=>parent::_($o)]; }
        return $opts;
    }

    private function departments(): array
    {
        $opts = [['value'=>'']];
        foreach ($this->files->departments() as $d) { $opts[] = ['value'=>$d]; }
        return $opts;
    }

    private function types(): array
    {
        $opts = [['value'=>'']];
        foreach ($this->files->types() as $d) { $opts[] = ['value'=>$d]; }
        return $opts;
    }

    private function committees(): array
    {
        $opts = [['value'=>'']];
        foreach ($this->files->committees() as $d) { $opts[] = ['value'=>$d]; }
        return $opts;
    }
}
