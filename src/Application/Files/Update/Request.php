<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files\Update;

final class Request
{
    public function __construct(
        public  int       $id,
        public ?string    $origin     = null,
        public ?string    $file       = null, // Full path to uploaded file
        public ?string    $filename   = null,
        public ?string    $username   = null,

        public ?int       $origin_id  = null,
        public ?string    $department = null,
        public ?string    $committee  = null,
        public ?string    $type       = null,
        public ?\DateTime $date       = null,
        public ?string    $title      = null
    ) { }

    public static function fromArray(array $f): static {
        return new static(
            id:        $f['id'        ] ?? null,
            origin:    $f['origin'    ] ?? null,
            file:      $f['file'      ] ?? null,
            filename:  $f['filename'  ] ?? null,
            username:  $f['username'  ] ?? null,
            origin_id: $f['origin_id' ] ?? null,
            department:$f['department'] ?? null,
            committee: $f['committee' ] ?? null,
            type:      $f['type'      ] ?? null,
            title:     $f['title'     ] ?? null,
            date:      $f['date'] ? new \DateTime($f['date']) : null,
        );
    }
}
