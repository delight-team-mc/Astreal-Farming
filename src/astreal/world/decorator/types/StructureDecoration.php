<?php

namespace astreal\world\decorator\types;

use astreal\world\decorator\rules\Rules;
use pocketmine\math\Facing;

class StructureDecoration
{

    public function __construct(
        readonly public string $structure_path,
        readonly public Rules $structure_rule,
        readonly public int $weight,
        readonly public array $rotations = [Facing::NORTH],
    ) {}
}
