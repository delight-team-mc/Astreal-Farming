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

final class RarityComponent implements ItemComponent
{

    public const COMMON = "common";
    public const UNCOMMON = "uncommon";
    public const RARE = "rare";
    public const EPIC = "epic";

    private string $rarity;


    public function __construct(string $rarity = self::COMMON)
    {
        $this->rarity = $rarity;
    }

    public function getName(): string
    {
        return "minecraft:rarity";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()->setString("value", $this->rarity);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
