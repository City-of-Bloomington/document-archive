<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Info;

use Application\Files\FilesRepository;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        $repo = new FilesRepository();
        $file = $repo->loadById((int)$params['id']);
        if ($file) {
            return new View($file);
        }

        return new \Web\Views\NotFoundView();
    }
}
