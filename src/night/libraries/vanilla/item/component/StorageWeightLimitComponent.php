<?php

namespace night\libraries\vanilla\item\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

class StorageWeightLimitComponent implements ItemComponent
{

    public function __construct(private int $max_weight_limit = 64) {}

    public function getName(): string
    {
        return "minecraft:storage_weight_limit";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()->setInt("max_weight_limit", $this->max_weight_limit);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
