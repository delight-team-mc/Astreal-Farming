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

final class StorageItemComponent implements ItemComponent
{

    private bool $allow_nested_storage_items;
    private int $max_slots;
    private int $max_weight_limit;

    public function __construct(bool $allow_nested_storage_items, int $max_slots, int $max_weight_limit, private array $allowed_items = [], private array $banned_items = [])
    {
        if ($max_slots <= 0 || $max_slots > 64) throw new \LogicException("max_slots must be between 1 and 64 inclusive");
        $this->allow_nested_storage_items = $allow_nested_storage_items;
        $this->max_slots = $max_slots;
        $this->max_weight_limit = $max_weight_limit;
    }

    public function getName(): string
    {
        return "minecraft:storage_item";
    }

    public function getValue(): Tag
    {
        return NBT::getTagType([
            'allow_nested_storage_items' => $this->allow_nested_storage_items,
            'allowed_items' => $this->allowed_items,
            'banned_items' => $this->banned_items,
            'max_slots' => $this->max_slots,
            'max_weight_limit' => $this->max_weight_limit
        ]);
    }

    public function isProperty(): bool
    {
        return false;
    }

    public function withAllowedItems(Item ...$items): StorageItemComponent
    {
        foreach ($items as $item) $this->allowed_items[] = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
        return $this;
    }

    public function withBannedItems(Item ...$items): StorageItemComponent
    {
        foreach ($items as $item) $this->banned_items[] = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
        return $this;
    }
}
