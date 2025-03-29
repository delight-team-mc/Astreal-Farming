<?php

namespace astreal\libraries\inventory\interfaces;

use pocketmine\block\Block;
use pocketmine\player\Player;

interface FakeBlockInventory extends TranslatableInventory{

    public function getWindowType():int;

    public function getBlock():Block;

    public function beforeOpeningInventory(Player $player):void;
}