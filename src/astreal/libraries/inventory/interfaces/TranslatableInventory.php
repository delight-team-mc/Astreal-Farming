<?php

namespace astreal\libraries\inventory\interfaces;

use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

interface TranslatableInventory{
    public function translate(ContainerOpenPacket $packet):void;
}