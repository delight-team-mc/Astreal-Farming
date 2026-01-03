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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class InteractButtonComponent implements ItemComponent
{
    private bool|string $interactButton;

    public function __construct(bool|string $interactButton)
    {
        if (is_bool($interactButton) === true) {
            $this->interactButton = "action.interact.use";
        } else {
            $this->interactButton = (string) $interactButton;
        }
    }
    public function getName(): string
    {
        return "minecraft:interact_button";
    }
    public function getValue(): Tag
    {
        return CompoundTag::create()
            ->setString("interact_text", (string) $this->interactButton)
            ->setInt("requires_interact", 1);
    }
    public function isProperty(): bool
    {
        return false;
    }
}
