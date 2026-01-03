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

final class StackedByDataComponent implements ItemComponent
{
    private bool $stackedByData;

    public function __construct(bool $stackedByData = true)
    {
        $this->stackedByData = $stackedByData;
    }

    public function getName(): string
    {
        return "stacked_by_data";
    }

    public function getValue(): Tag
    {
        return new ByteTag($this->stackedByData ? 1 : 0);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
