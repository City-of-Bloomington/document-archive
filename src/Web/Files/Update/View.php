<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Update;

use Application\Files\Update\Request;
use Application\Files\FilesRepository;

class View extends \Web\View
{
    private FilesRepository $repo;

    public function __construct(Request $req)
    {
        parent::__construct();

        $this->repo = new FilesRepository();
        list($maxSize, $maxBytes) = parent::maxUpload();

        $this->vars = [
            'origin'      => $req->origin,
            'origins'     => self::origins(),
            'departments' => self::options('departments'),
            'committees'  => self::options('committees'),
            'types'       => self::options('types'),
            'accept'      => '.pdf',
            'maxBytes'    => $maxBytes,
            'maxSize'     => $maxSize
        ];
        foreach (FilesRepository::FIELDS_OPTIONAL as $f) { $this->vars[$f] = $req->$f; }
    }

    public function render(): string
    {
        return $this->twig->render('html/files/update.twig', $this->vars);
    }

    private static function origins(): array
    {
        $opts = [['value'=>'']];
        foreach (FilesRepository::ORIGINS as $o) { $opts[] = ['value'=>$o, 'label'=>parent::_($o)]; }
        return $opts;
    }

    private function options(string $field): array
    {
        $opts = [['value'=>'']];
        foreach ($this->repo->$field() as $o) { $opts[] = ['value'=>$o]; }
        return $opts;
    }
}
