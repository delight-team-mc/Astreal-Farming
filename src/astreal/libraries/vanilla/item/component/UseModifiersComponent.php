<?php

declare(strict_types=1);
/*
 * 
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║   
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║   
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║   
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝   
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 */

namespace astreal\libraries\vanilla\item\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class UseModifiersComponent implements ItemComponent
{

    private float $useDuration;
    private float $movementModifier;

    public function __construct(float $movementModifier, float $useDuration = 0)
    {
        $this->useDuration = $useDuration;
        $this->movementModifier = $movementModifier;
    }

    public function getName(): string
    {
        return "minecraft:use_modifiers";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()
            ->setFloat("movement_modifier", $this->movementModifier)
            ->setFloat("use_duration", $this->useDuration);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
