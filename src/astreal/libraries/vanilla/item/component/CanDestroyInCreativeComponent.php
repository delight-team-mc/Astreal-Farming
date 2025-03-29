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

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Tag;

final class CanDestroyInCreativeComponent implements ItemComponent
{

    private bool $canDestroyInCreative;

    public function __construct(bool $canDestroyInCreative = true)
    {
        $this->canDestroyInCreative = $canDestroyInCreative;
    }

    public function getName(): string
    {
        return "can_destroy_in_creative";
    }

    public function getValue(): Tag
    {
        return new ByteTag($this->canDestroyInCreative ? 1 : 0);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
