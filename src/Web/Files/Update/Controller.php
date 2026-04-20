<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Update;

use Application\Files\Update\Command;
use Application\Files\Update\Request;
use Application\Files\FilesRepository;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        $repo = new FilesRepository();
        $file = $repo->loadById((int)$params['id']);
        if ($file) {
            $req           = Request::fromArray($file);
            $req->username = $_SESSION['USER']['username'];

            if (isset($_POST['origin'])) {
                $req->origin = $_POST['origin'];
                foreach (FilesRepository::FIELDS_OPTIONAL as $f) {
                    if (!empty($_POST[$f])) {
                        switch ($f) {
                            case 'date':
                                try { $req->$f = new \DateTime($_POST[$f]); }
                                catch (\Exception $e) {
                                    $_SESSION['errorMessages'][] = 'invalidDate';
                                    return new View($req);
                                }
                            break;

                            case 'origin_id':
                                $req->$f = (int)$_POST['origin_id'];
                            break;

                            default:
                                $req->$f = $_POST[$f];
                        }
                    }
                    else { $req->$f = null; }
                }

                if (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE) {
                    $req->filename = basename($_FILES['file']['name']);
                    $req->file     =          $_FILES['file']['tmp_name'];
                }

                $update = new Command();
                $res    = $update($req);
                if (!$res->errors) {
                    $url = \Web\View::generateUrl('home.info', ['id'=>$req->id]);
                    header("Location: $url");
                    exit();
                }
                $_SESSION['errorMessages'] = $res->errors;
            }

            return new View($req);
        }
        return new \Web\Views\NotFoundView();
    }
}
