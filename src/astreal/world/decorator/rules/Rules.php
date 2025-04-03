<?php

namespace astreal\world\decorator\rules;

use pocketmine\block\Block;

abstract class Rules
{

    abstract public function canPlaceOn(Block $soil): bool;
}
