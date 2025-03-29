<?php

namespace astreal;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class AstrealListener implements Listener
{

    public function onPlayerJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setJoinMessage("§7[§a+§7]§2 " . $player->getName() . " §7joined the server.");
    }


    public function onPlayerQuit(PlayerQuitEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setQuitMessage("§7[§c-§7]§6 " . $player->getName() . " §7left the server.");
    }
}
