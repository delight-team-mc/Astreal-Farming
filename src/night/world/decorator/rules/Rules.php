<?php

namespace night\world\decorator\rules;

use pocketmine\block\Block;

abstract class Rules
{

    abstract public function canPlaceOn(Block $soil): bool;
}
