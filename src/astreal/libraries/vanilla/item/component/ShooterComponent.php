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

final class ShooterComponent implements ItemComponent
{
    private bool $chargeOnDraw;
    private float $maxDrawDuration;
    private bool $scalePowerByDrawDuration;
    private string $item;
    private bool $useOffhand;
    private bool $searchInventory;
    private bool $useInCreative;

    public function __construct(string $item, bool $useOffhand = false, bool $searchInventory = false, bool $useInCreative = false, bool $chargeOnDraw = false, float $maxDrawDuration = 0.0, bool $scalePowerByDrawDuration = false)
    {
        $this->item = $item;
        $this->useOffhand = $useOffhand;
        $this->searchInventory = $searchInventory;
        $this->useInCreative = $useInCreative;
        $this->chargeOnDraw = $chargeOnDraw;
        $this->maxDrawDuration = $maxDrawDuration;
        $this->scalePowerByDrawDuration = $scalePowerByDrawDuration;
    }

    public function getName(): string
    {
        return "minecraft:shooter";
    }

    public function getValue(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag(
                "ammunition",
                CompoundTag::create()
                    ->setString("item",  $this->item)
                    ->setByte("use_offhand",  $this->useOffhand ? 1 : 0)
                    ->setByte("search_inventory",  $this->searchInventory ? 1 : 0)
                    ->setByte("use_in_creative",  $this->useInCreative ? 1 : 0)
            )
            ->setByte("charge_on_draw",  $this->chargeOnDraw ? 1 : 0)
            ->setFloat("max_draw_duration",  $this->maxDrawDuration)
            ->setByte("scale_power_by_draw_duration",  $this->scalePowerByDrawDuration ? 1 : 0);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
