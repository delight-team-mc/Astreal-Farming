<?php

namespace astreal\libraries\inventory\utils;

use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ListenerTransaction{

    public static function create(Player $player,Item $out,Item $in,SlotChangeAction $action,InventoryTransaction $transaction):self{
        return new self($player,$out,$in,$action,$transaction);
    } 

    public function __construct(private Player $player,private Item $out,private Item $in,private SlotChangeAction $action,private InventoryTransaction $transaction){}

	public function getPlayer():Player{
		return $this->player;
	}

	public function getOut():Item{
		return $this->out;
	}

	public function getIn():Item{
		return $this->in;
	}

	public function getAction():SlotChangeAction{
		return $this->action;
	}

	public function getTransaction():InventoryTransaction{
		return $this->transaction;
	}
}