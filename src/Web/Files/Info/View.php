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
            'file'        => $file,
            'actionLinks' => self::actionLinks($file)
        ];
    }

    public function render(): string
    {
        return $this->twig->render('html/files/info.twig', $this->vars);
    }

    private static function actionLinks(array $file): array
    {
        $l = [];
        if (parent::isAllowed('files', 'update')) {
            $l[] = [
                'url'   => parent::generateUri('files.update', ['id'=>$file['id']]),
                'label' => 'Edit File',
                'class' => 'edit'
            ];


        }
        if (parent::isAllowed('files', 'delete')) {
            $l[] = [
                'url'   => parent::generateUri('files.delete', ['id'=>$file['id']]),
                'label' => 'Delete File',
                'class' => 'delete'
            ];
        }
        return $l;
    }
}
