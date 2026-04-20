<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files\Add;

use Application\Files\FilesRepository;
use Application\Files\Response;

class Command
{
    private $repo;

    public function __construct()
    {
        $this->repo = new FilesRepository();
    }

    public function __invoke(Request $req): Response
    {
        $ym        = date('Y/m');
        $internal  = "/$ym/".uniqid();
        $directory = SITE_HOME.'/files';

        if (!is_dir("$directory/$ym")) {
            mkdir  ("$directory/$ym", 0775, true);
        }

        $file = [
            'internalFilename' => $internal,
            'filename'   => $req->filename,
            'mime_type'  => mime_content_type($req->file),
            'md5'        =>          md5_file($req->file),
            'origin'     => $req->origin,
            'username'   => $req->username
        ];
        foreach (FilesRepository::FIELDS_OPTIONAL as $f) {
            if ($req->$f) {
                switch ($f) {
                    case 'date':
                        $file[$f] = $req->$f->format(FilesRepository::DB_DATETIME);
                    break;

                    default:
                        $file[$f] = $req->$f;
                }
            }
        }

        $errors = FilesRepository::validate($file);
        if ($errors) {
            return new Response(errors: $errors);
        }

        try {
            $s = rename($req->file, $directory.$internal);
            if (!$s) { throw new \Exception('files/badServerPermissions'); }

            /** @throws \PDOException */
            $id = $this->repo->save($file);
        }
        catch (\Exception $e) {
            unlink($directory.$internal);
            return new Response(errors: [$e->getMessage()]);
        }
        return new Response(id: $id);
    }
}
