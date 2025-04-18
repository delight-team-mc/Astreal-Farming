<?php

namespace astreal\libraries\vanilla\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class LiquidDetectionComponent implements BlockComponent
{

    public function __construct(private string $liquid_type = 'water', private bool $can_contain_liquid = true, private string $on_liquid_touches = 'no_reaction') {}

    public function getName(): string
    {
        return 'minecraft:liquid_detection';
    }

    public function getValue(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("detection_rules", new ListTag([
                CompoundTag::create()
                    ->setString("liquid_type", $this->liquid_type)
                    ->setByte("can_contain_liquid", $this->can_contain_liquid ? 1 : 0)
                    ->setString("on_liquid_touches", $this->on_liquid_touches)
            ]));
    }
}
