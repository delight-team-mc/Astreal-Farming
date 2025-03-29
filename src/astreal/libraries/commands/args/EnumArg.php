<?php

namespace astreal\libraries\commands\args;

use Closure;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

class EnumArg extends BaseArg
{

    public function __construct(string $name, bool $optional, private string $enum_name, private Closure $enum_values, private bool $soft = false)
    {
        parent::__construct($name, $optional);
    }

    public function getParameter(): CommandParameter
    {
        return CommandParameter::enum($this->getName(), $this->getCommandEnum(), 0, $this->isOptional());
    }

    public function getCommandEnum(): CommandEnum
    {
        return new CommandEnum($this->enum_name, ($this->enum_values)(), $this->soft);
    }

    public function getEnumName(): string
    {
        return $this->enum_name;
    }

    public function getEnumValues(): array
    {
        return ($this->enum_values)();
    }

    public function isSoft(): bool
    {
        return $this->soft;
    }

    public function getType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }
}
