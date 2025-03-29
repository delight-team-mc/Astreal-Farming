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

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;

final class DamageComponent implements ItemComponent
{
    private int $damage;

    public function __construct(int $damage)
    {
        $this->damage = $damage;
    }

    public function getName(): string
    {
        return "damage";
    }

    public function getValue(): Tag
    {
        return new IntTag($this->damage);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
