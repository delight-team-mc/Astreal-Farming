<?php

namespace night\libraries\inventory\interfaces;

use night\libraries\inventory\utils\ListenerTransaction;
use pocketmine\player\Player;

interface ListenerInventory
{

    public function close(Player $player): void;

    public function transaction(ListenerTransaction $transaction): bool;
}
