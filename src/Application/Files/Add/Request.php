<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Application\Files\Add;

final class Request
{
    public function __construct(
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
    )
    {
    }
}
