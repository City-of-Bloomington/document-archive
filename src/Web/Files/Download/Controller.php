<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Download;

use Application\Files\FilesRepository;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        $repo = new FilesRepository();
        $file = $repo->loadById((int)$params['id']);
        if ($file) {
            $mime     = $file['mime_type'];
            $filename = $file['filename' ];

            header('Expires: 0');
            header('Pragma: cache');
            header('Cache-Control: private');
            header("Content-type: $mime");
            header("Content-Disposition: inline; filename=\"$filename\"");

            readfile(SITE_HOME.'/files'.$file['internalFilename']);
            exit();
        }

        return new \Web\Views\NotFoundView();
    }
}
