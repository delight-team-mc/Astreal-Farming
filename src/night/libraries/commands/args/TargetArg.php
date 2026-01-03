<?php

namespace night\libraries\commands\args;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class TargetArg extends BaseArg
{

    public function getType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }
}
