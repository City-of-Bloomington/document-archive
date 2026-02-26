<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Info;

class View extends \Web\View
{
    public function __construct(array $file)
    {
        parent::__construct();

        $this->vars = [
            'file' => $file
        ];
    }

    public function render(): string
    {
        return $this->twig->render('html/files/info.twig', $this->vars);
    }
}
