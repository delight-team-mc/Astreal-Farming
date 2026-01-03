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

final class LiquidClippedComponent implements ItemComponent
{
    private bool $liquidClipped;

    public function __construct(bool $liquidClipped = true)
    {
        $this->liquidClipped = $liquidClipped;
    }

    public function getName(): string
    {
        return "liquid_clipped";
    }

    public function getValue(): Tag
    {
        return new ByteTag($this->liquidClipped ? 1 : 0);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
