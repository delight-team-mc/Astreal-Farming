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

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Tag;

final class ShouldDespawnComponent implements ItemComponent
{
    private bool $shouldDespawn;

    public function __construct(bool $shouldDespawn = true)
    {
        $this->shouldDespawn = $shouldDespawn;
    }

    public function getName(): string
    {
        return "should_despawn";
    }

    public function getValue(): Tag
    {
        return new ByteTag($this->shouldDespawn ? 1 : 0);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
