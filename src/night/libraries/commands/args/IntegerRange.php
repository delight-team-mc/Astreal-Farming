<?php

namespace night\libraries\commands\args;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class IntergeRangeArg extends BaseArg
{

    public function getType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_FULL_INTEGER_RANGE;
    }
}
