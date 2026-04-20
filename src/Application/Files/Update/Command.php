<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files\Update;

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
        $file = $this->repo->loadById($req->id);
        $file['origin'  ] = $req->origin;
        $file['username'] = $req->username;
        if ($req->file) {
            $file['filename' ]  = $req->filename;
            $file['mime_type']  = mime_content_type($req->file);
            $file['md5'      ]  =          md5_file($req->file);
        }

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
            else { $file[$f] = null; }
        }

        $errors = FilesRepository::validate($file);
        if ($errors) {
            return new Response(errors: $errors);
        }

        try {
            /** @throws \PDOException */
            $id = $this->repo->save($file);
            if ($req->file) {
                $s = rename($req->file, SITE_HOME.'/files'.$file['internalFilename']);
                if (!$s) { throw new \Exception('files/badServerPermissions'); }
            }
        }
        catch (\Exception $e) {
            return new Response(errors: [$e->getMessage()]);
        }

        return new Response(id: $id);
    }
}
