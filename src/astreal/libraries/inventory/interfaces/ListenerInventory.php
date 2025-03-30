<?php

namespace astreal\libraries\inventory\interfaces;

use astreal\libraries\inventory\utils\ListenerTransaction;
use pocketmine\player\Player;

interface ListenerInventory
{

    public function close(Player $player): void;

    public function transaction(ListenerTransaction $transaction): bool;
}
