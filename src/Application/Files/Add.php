<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files;

class Add
{
    private $repo;

    public function __construct()
    {
        $this->repo = new FilesRepository();
    }

    public function __invoke(array $req): array
    {
        $ym        = date('Y/m');
        $internal  = "/$ym/".uniqid();
        $directory = SITE_HOME.'/files';

        if (!is_dir("$directory/$ym")) {
            mkdir  ("$directory/$ym", 0775, true);
        }

        $file = [
            'internalFilename' => $internal,
            'filename'   => $req['filename'],
            'mime_type'  => mime_content_type($req['file']),
            'md5'        =>          md5_file($req['file']),
            'origin'     => $req['origin'    ],
            'department' => $req['department'],
            'username'   => $req['username'  ]
        ];
        if (!empty($req['origin_id'])) { $file['origin_id'] = $req['origin_id']; }

        $errors = self::validate($file);
        if ($errors) {
            return ['errors' => $errors];
        }

        rename($req['file'], $directory.$internal);
        if (!is_file($directory.$internal)) {
            throw new \Exception('files/badServerPermissions');
        }

        try {
            /** @throws \PDOException */
            $id = $this->repo->save($file);
        }
        catch (\Exception $e) {
            unlink($directory.$internal);
            return ['errors' => [$e->getMessage()]];
        }
        return ['id'=>$id];
    }

    /**
     * Returns any errors with the file to be saved to the database
     */
    private static function validate(array $file): array
    {
        $errors   = [];
        $required = ['internalFilename', 'filename', 'mime_type', 'md5', 'origin', 'department', 'username'];
        foreach ($required as $f) {
            if (empty($file[$f])) {
                $errors[] = "Missing $f";
            }
        }
        return $errors;
    }
}
