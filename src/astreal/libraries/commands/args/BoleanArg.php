<?php

namespace astreal\libraries\commands\args;

use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

class BoleanArg extends BaseArg
{

    public function getParameter(): CommandParameter
    {
        return CommandParameter::enum($this->getName(), new CommandEnum('Bolean', ['true', 'false']), 0, $this->isOptional());
    }

    public function getType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
    }
}
