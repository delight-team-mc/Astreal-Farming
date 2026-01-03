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
use pocketmine\item\Item;
use pocketmine\nbt\tag\Tag;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class RepairableComponent implements ItemComponent
{
    private array $repair_items = [];

    public function __construct(array $repair_items) {}
    public function getName(): string
    {
        return "minecraft:repairable";
    }

    public function getValue(): Tag
    {
        return NBT::getTagType(['repair_items' => $this->repair_items]);
    }

    public function isProperty(): bool
    {
        return false;
    }

    public function withItems(int $repair_amount, Item ...$items): RepairableComponent
    {
        $this->repair_items[] = [
            'items' => array_map(fn(Item $item): string => GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName(), $items),
            'repair_amount' => $repair_amount
        ];
        return $this;
    }
}
