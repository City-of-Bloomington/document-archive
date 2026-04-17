<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Delete;

use Application\Files\FilesRepository;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        $repo = new FilesRepository();
        $row  = $repo->loadById((int)$params['id']);
        if ($row) {
            $file = SITE_HOME.'/files'.$row['internalFilename'];
            if (is_file($file)) { unlink($file); }
            $repo->delete((int)$row['id']);

            $url = \Web\View::generateUrl('home.index');
            header("Location: $url");
            exit();
        }

        return new \Web\Views\NotFoundView();
    }
}
