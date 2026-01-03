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

namespace night\libraries\vanilla\item\component;

use night\libraries\vanilla\util\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\block\Block;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

final class EntityPlacerComponent implements ItemComponent
{
    public function __construct(private string $entity, private array $use_on = [], private array $dispense_on = []) {}

    public function getName(): string
    {
        return "minecraft:entity_placer";
    }

    public function getValue(): Tag
    {
        return NBT::getTagType([
            'entity' => $this->entity,
            'dispense_on' => $this->dispense_on,
            'use_on' => $this->use_on
        ]);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
