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

use night\libraries\vanilla\utils\NBT;
use pocketmine\nbt\tag\CompoundTag;

final class ShooterComponent implements ItemComponent
{
    public function __construct(
        private array $ammunition,
        private bool $chargeOnDraw = false,
        private float $maxDrawDuration = 0.0,
        private bool $scalePowerByDrawDuration = false
    ) {}

    public function getName(): string
    {
        return "minecraft:shooter";
    }

    public function getValue(): CompoundTag
    {
        return NBT::getTagType([
            "ammunition" => $this->ammunition,
            "charge_on_draw" => $this->chargeOnDraw,
            "max_draw_duration" => $this->maxDrawDuration,
            "scale_power_by_draw_duration" => $this->scalePowerByDrawDuration
        ]);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
