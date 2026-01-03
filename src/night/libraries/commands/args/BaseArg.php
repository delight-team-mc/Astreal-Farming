<?php

namespace night\libraries\commands\args;

use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

abstract class BaseArg
{

    public function __construct(private string $name, private bool $optional = false) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function getParameter(): CommandParameter
    {
        return CommandParameter::standard($this->name, $this->getType(), 0, $this->optional);
    }

    abstract public function getType(): int;
}
