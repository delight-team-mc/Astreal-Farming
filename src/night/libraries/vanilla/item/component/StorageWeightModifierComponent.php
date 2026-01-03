<?php

namespace night\libraries\vanilla\item\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

class StorageWeightModifierComponent implements ItemComponent
{

    public function __construct(private int $weight_in_storage_item = 64) {
        if ($this->weight_in_storage_item < 0 || $this->weight_in_storage_item > 64) throw new \LogicException("weight_in_storage_item must be between 0 and 64 inclusive");
    }

    public function getName(): string
    {
        return "minecraft:storage_weight_modifier";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()->setInt('weight_in_storage_item', $this->weight_in_storage_item);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
