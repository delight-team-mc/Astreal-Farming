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
use pocketmine\nbt\tag\Tag;

final class RecordComponent implements ItemComponent
{
    private int $comparatorSignal;
    private float $duration;
    private string $soundEvent;

    public function __construct(int $comparatorSignal = 1, float $duration, string $soundEvent = "undefined")
    {
        $this->comparatorSignal = $comparatorSignal;
        $this->duration = $duration;
        $this->soundEvent = $soundEvent;
    }
    public function getName(): string
    {
        return "minecraft:record";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()
            ->setInt("comparator_signal", $this->comparatorSignal)
            ->setFloat("duration", $this->duration)
            ->setString("sound_event", $this->soundEvent);
    }

    public function isProperty(): bool
    {
        return false;
    }
}
