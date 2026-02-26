<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Download;

class View extends \Web\View
{
    public function __construct(array $file)
    {
        // Do not call parent::__construct
        // This does not use Twig templating
        $this->vars = ['file'=>$file];
    }

    public function render(): string
    {
        header('Expires: 0');
        header('Pragma: cache');
        header('Cache-Control: private');
        header('Content-type: '.$this->vars['file']['mime_type']);
        header("Content-Disposition: inline; filename=\"{$this->vars['file']['filename']}\"");

        readfile(SITE_HOME.'/files'.$this->vars['file']['internalFilename']);
        exit();
    }
}
