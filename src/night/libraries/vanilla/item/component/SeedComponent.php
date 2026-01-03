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

use pocketmine\block\Block;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

final class SeedComponent implements ItemComponent
{
    private string $crop_result;
    private array $plant_at = [];
    private bool $plant_at_any_solid_surface;
    private string $plant_at_face;

    public function __construct(string $crop_result, bool $plant_at_any_solid_surface = false, string $plant_at_face = 'up')
    {
        $this->crop_result = $crop_result;
        $this->plant_at_any_solid_surface = $plant_at_any_solid_surface;
        $this->plant_at_face = $plant_at_face;
    }

    public function getName(): string
    {
        return "minecraft:seed";
    }

    public function getValue(): CompoundTag
    {
        return CompoundTag::create()
            ->setString('crop_result', $this->crop_result)
            ->setTag('plant_at', new ListTag(array_map(fn(string $block): StringTag => new StringTag($block), $this->plant_at), NBT::TAG_String))
            ->setByte('plant_at_any_solid_surface', $this->plant_at_any_solid_surface ? 1 : 0)
            ->setString('plant_at_face', $this->plant_at_face);
    }

    public function isProperty(): bool
    {
        return true;
    }

    public function withBlocks(Block ...$blocks): SeedComponent
    {
        foreach ($blocks as $block) $this->plant_at[] = GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName();
        return $this;
    }
}
