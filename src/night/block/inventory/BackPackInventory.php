<?php

namespace night\block\inventory;

use night\block\BackPack;
use night\libraries\inventory\interfaces\ListenerInventory;
use night\libraries\inventory\interfaces\NameableFakeBlockInventory;
use night\libraries\inventory\traits\ListenerInventoryTrait;
use night\libraries\inventory\traits\NameableFakeBlockInventoryTrait;
use night\libraries\inventory\TranslatableFakeBlockInvenotry;
use night\libraries\inventory\utils\ListenerTransaction;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemBlock;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;

class BackPackInventory extends TranslatableFakeBlockInvenotry implements NameableFakeBlockInventory, ListenerInventory
{
    use NameableFakeBlockInventoryTrait;
    use ListenerInventoryTrait;

    public function __construct(private Position $position)
    {
        parent::__construct(27);
        $this->setTransactionListener(function (ListenerTransaction $tr) {
            if (($item = $tr->getIn()) instanceof ItemBlock && $item->getBlock() instanceof BackPack) return true;
            return false;
        });
    }

    public function getWindowType(): int
    {
        return WindowTypes::CONTAINER;
    }

    public function getBlock(): Block
    {
        return VanillaBlocks::CHEST();
    }

    public function getDefaultName(): string
    {
        return "BackPack";
    }

    public function getHolder(): Position
    {
        return $this->position;
    }

    public function loadFromNbt(): void {}
}
