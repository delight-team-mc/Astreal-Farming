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

use astreal\libraries\vanilla\util\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class DamageAbsorptionComponent implements ItemComponent
{
    private array $absorbableCauses;

    public function __construct(array $absorbableCauses)
    {
        $this->absorbableCauses = $absorbableCauses;
    }

    public function getName(): string
    {
        return "minecraft:damage_absorption";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()->setTag("absorbable_causes", NBT::getArrayTag($this->absorbableCauses));
    }

    public function isProperty(): bool
    {
        return false;
    }
}
