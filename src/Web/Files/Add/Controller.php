<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Files\Add;

use Application\Files\Add;

class Controller extends \Web\Controller
{
    public function __invoke(array $params): \Web\View
    {
        if ( isset($_POST['origin'])) {
            $req = [
                'origin'     => $_POST['origin'    ],
                'origin_id'  => $_POST['origin_id' ],
                'department' => $_POST['department'],
                'username'   => $_SESSION['USER']['username']
            ];


            if (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE) {
                $req['filename']   = basename($_FILES['file']['name']);
                $req['file'    ]   =          $_FILES['file']['tmp_name'];

                $add = new Add();
                $res = $add($req);

                if (!isset($res['errors'])) {
                    $url = \Web\View::generateUrl('home.info', ['id'=>$res['id']]);
                    header("Location: $url");
                    exit();
                }

                $_SESSION['errorMessages'] = $res['errors'];
            }
            else {
                $_SESSION['errorMessages'][] = 'Missing file upload';
            }
            return new View($req);
        }
        return new View();
    }
}
