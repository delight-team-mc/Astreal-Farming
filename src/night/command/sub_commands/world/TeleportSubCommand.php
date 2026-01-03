<?php

namespace night\command\sub_commands\world;

use pocketmine\command\CommandSender;
use pocketmine\world\World;
use night\libraries\commands\args\EnumArg;
use night\libraries\commands\BaseSubCommand;
use night\libraries\TranslateMessage;
use night\system\SessionManager;
use pocketmine\player\Player;

class TeleportSubCommand extends BaseSubCommand
{

    public function __construct()
    {
        parent::__construct('teleport', ['tp']);
        $this->setPermission('world.teleport.command');
        $this->registerArg(new EnumArg('world', false, 'world name', fn() => array_map(fn(World $world): string => strtolower($world->getFolderName()), $this->getServer()->getWorldManager()->getWorlds()), true));
    }

    public function execute_command(CommandSender $sender, string $parent_label, string $label, array $args)
    {
        if (!$sender instanceof Player) return $sender->sendMessage(TranslateMessage::create('commands.not.in.game'));
        $lang = SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang();
        if (!isset($args[0])) {
            $sender->sendMessage($this->getUsage($parent_label, $label, '§cUsage: ', '§c', '§6'));
            return;
        }
        $world = $this->getWorldByName($args[0]);
        if (!$world instanceof World) {
            $sender->sendMessage(TranslateMessage::create('commands.world.not.exists', $lang)->translate(['{world}'], [$args[0]]));
            return;
        }
        if (!$world->isLoaded()) {
            $sender->sendMessage(TranslateMessage::create('commands.world.not.loader', $lang)->translate(['{world.folder_name}', $world->getFolderName()]));
            return;
        }
        $sender->sendMessage(TranslateMessage::create('commands.world.teleport', $lang)->translate(['{world.folder_name}'], [$world->getFolderName()]));
        if (is_null($world->getOrLoadChunkAtPosition($pos = $world->getSpawnLocation()))) {
            $sender->teleport($pos);
            return;
        }
        $sender->teleport($world->getSafeSpawn());
    }

    public function getWorldByName(string $name): ?World
    {
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            if (strtolower($world->getFolderName()) === strtolower($name)) {
                return $world;
            }
        }

        return null;
    }
}
