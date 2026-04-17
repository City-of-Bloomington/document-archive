<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Add;

use Application\Files\FilesRepository;

class View extends \Web\View
{
    private FilesRepository $repo;

    public function __construct(?array $request=[])
    {
        parent::__construct();

        $this->repo = new FilesRepository();
        list($maxSize, $maxBytes) = self::maxUpload();

        $this->vars = [
             'origin'      => $request['origin'] ?? '',
             'origins'     => self::origins(),
             'departments' => self::options('departments'),
             'committees'  => self::options('committees'),
             'types'       => self::options('types'),
             'accept'      => '.pdf',
             'maxBytes'    => $maxBytes,
             'maxSize'     => $maxSize
        ];
        foreach (FilesRepository::FIELDS_OPTIONAL as $f) { $this->vars[$f] = $request[$f] ?? null; }
    }

    public function render(): string
    {
        return $this->twig->render('html/files/add.twig', $this->vars);
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

    /**
     * Return the max size upload allowed in PHP ini
     *
     * This returns both a human readable size string as well as the raw
     * number of bytes.
     */
    public static function maxUpload(): array
    {
        $upload_max_size  = ini_get('upload_max_filesize');
        $post_max_size    = ini_get('post_max_size');
        $upload_max_bytes = self::bytes($upload_max_size);
        $post_max_bytes   = self::bytes(  $post_max_size);

        if ($upload_max_bytes < $post_max_bytes) {
            $maxSize  = $upload_max_size;
            $maxBytes = $upload_max_bytes;
        }
        else {
            $maxSize  = $post_max_size;
            $maxBytes = $post_max_bytes;
        }
        return [$maxSize, $maxBytes];
    }

    public static function bytes(string $size): int
    {
        switch (substr($size, -1)) {
            case 'M': return (int)$size * 1048576;
            case 'K': return (int)$size * 1024;
            case 'G': return (int)$size * 1073741824;
            default:  return (int)$size;
        }
    }
}
