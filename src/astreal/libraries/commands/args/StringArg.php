<?php

namespace astreal\libraries\commands\args;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class StringArg extends BaseArg
{

    public function getType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }
}
