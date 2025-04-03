<?php

namespace astreal\world\decorator\rules;

use pocketmine\block\Block;

class TreeRule extends Rules
{

    public function __construct(private array $blocksIds)
    {
    }

    public function canPlaceOn(Block $soil): bool
    {
        return in_array($soil->getTypeId(), $this->blocksIds);
    }
}
