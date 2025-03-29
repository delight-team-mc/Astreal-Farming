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

use astreal\libraries\vanilla\util\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\block\Block;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

final class BlockPlacerComponent implements ItemComponent
{
    private Block $block;
    private array $useOn = [];

    public function __construct(Block $block)
    {
        $this->block = $block;
    }
    public function getName(): string
    {
        return "minecraft:block_placer";
    }

    public function getValue(): Tag
    {
        return CompoundTag::create()->setString("block", GlobalBlockStateHandlers::getSerializer()->serialize($this->block->getStateId())->getName())->setTag("use_on", NBT::getArrayTag($this->useOn));
    }

    public function isProperty(): bool
    {
        return false;
    }

    public function useOn(Block ...$blocks): self
    {
        foreach ($blocks as $block) {
            $this->useOn[] = [
                "name" => GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName()
            ];
        }
        return $this;
    }
}
