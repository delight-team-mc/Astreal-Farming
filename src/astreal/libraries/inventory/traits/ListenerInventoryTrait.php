<?php

namespace astreal\libraries\inventory\traits;

use astreal\libraries\inventory\utils\ListenerTransaction;
use pocketmine\player\Player;

trait ListenerInventoryTrait{
    private ?\Closure $close_listener=null,$transaction_listener=null;

    public function close(Player $player):void{
        if($this->close_listener!==null)($this->close_listener)($player,$this);
    }

    public function transaction(ListenerTransaction $transaction):bool{
        return $this->transaction_listener!==null ? ($this->transaction_listener)($transaction) : false;
    }

    public function setTransactionListener(?\Closure $transaction_listener):void{
        $this->transaction_listener=$transaction_listener;
    }

    public function setCloseListener(?\Closure $close_listener):void{
        $this->close_listener=$close_listener;
    }
}