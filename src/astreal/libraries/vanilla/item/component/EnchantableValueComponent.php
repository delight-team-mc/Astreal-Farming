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

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;

final class EnchantableValueComponent implements ItemComponent
{
    // Armor Enchantability
    public const ARMOR_LEATHER = 15;
    public const ARMOR_CHAIN = 12;
    public const ARMOR_IRON = 9;
    public const ARMOR_GOLD = 25;
    public const ARMOR_DIAMOND = 10;
    public const ARMOR_TURTLE = 9;
    public const ARMOR_NETHERITE = 15;
    public const ARMOR_OTHER = 1;
    // Tool Enchantability
    public const TOOL_WOOD = 15;
    public const TOOL_STONE = 5;
    public const TOOL_IRON = 14;
    public const TOOL_GOLD = 22;
    public const TOOL_DIAMOND = 10;
    public const TOOL_NETHERITE = 15;
    public const TOOL_OTHER = 1;
    private int $value;

    public function __construct(int $value = 1)
    {
        $this->value = $value;
    }
    public function getName(): string
    {
        return "enchantable_value";
    }
    public function getValue(): Tag
    {
        return new IntTag($this->value);
    }
    public function isProperty(): bool
    {
        return true;
    }
}
