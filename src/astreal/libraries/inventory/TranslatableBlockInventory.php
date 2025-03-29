<?php

namespace astreal\libraries\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use astreal\libraries\inventory\interfaces\TranslatableInventory;

abstract class TranslatableBlockInventory extends SimpleInventory implements BlockInventory,TranslatableInventory{

    abstract public function getWindowType():int;

    public function translate(ContainerOpenPacket $packet):void{
        $packet->blockPosition=BlockPosition::fromVector3($this->getHolder());
        $packet->windowType=$this->getWindowType();
    }
}