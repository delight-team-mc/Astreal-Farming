<?php

namespace night\libraries\vanilla\block\component;

use night\libraries\vanilla\util\NBT as UtilNBT;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class LiquidDetectionComponent implements BlockComponent
{

    const WATER_LIQUID_TYPE = 'water';
    const BLOCKING_ON_LIQUID_TOUCHES = 'blocking';
    const BROKEN_ON_LIQUID_TOUCHES = 'broken';
    const NO_REACTION_ON_LIQUID_TOUCHES = 'no_reaction';
    const POPPED_ON_LIQUID_TOUCHES = 'popped';
    const DOWN_LIQUID_DIRECTION = 'down';
    const EAST_LIQUID_DIRECTION = 'east';
    const NORTH_LIQUID_DIRECTION = 'north';
    const SOUTH_LIQUID_DIRECTION = 'south';
    const UP_LIQUID_DIRECTION = 'up';
    const WEST_LIQUID_DIRECTION = 'west';

    public function __construct(private array $detection_rules) {}

    public function getName(): string
    {
        return 'minecraft:liquid_detection';
    }

    public function getValue(): CompoundTag
    {
        return CompoundTag::create()->setTag("detectionRules", new ListTag(\array_map(fn(array $data) => CompoundTag::create()->setByte('canContainLiquid', ($data['can_contain_liquid'] ?? false) ? 1 : 0)->setString('liquidType', ($data['liquid_type'] ?? self::WATER_LIQUID_TYPE))->setString('onLiquidTouches', ($data['on_liquid_touches'] ?? self::BLOCKING_ON_LIQUID_TOUCHES))->setByte('stopsLiquidFromDirection', array_sum(array_map(fn(string $direction) => match ($direction) {
            'down' => 1,
            'east' => 32,
            'north' => 4,
            'south' => 8,
            'up' => 2,
            'west' => 16,
            default => 0
        }, ($data['stops_liquid_flowing_from_direction'] ?? [])))), $this->detection_rules)));
    }
}
