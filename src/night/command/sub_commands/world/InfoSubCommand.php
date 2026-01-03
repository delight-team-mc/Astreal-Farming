<?php

namespace night\command\sub_commands\world;

use pocketmine\command\CommandSender;
use pocketmine\world\World;
use night\libraries\commands\args\EnumArg;
use night\libraries\commands\BaseSubCommand;
use night\libraries\TranslateMessage;
use night\system\SessionManager;
use pocketmine\player\Player;

class InfoSubCommand extends BaseSubCommand
{

    public function __construct()
    {
        parent::__construct('info');
        $this->setPermission('world.info.command');
        $this->registerArg(new EnumArg('world', false, 'world name', fn() => array_map(fn(World $world): string => strtolower($world->getFolderName()), $this->getServer()->getWorldManager()->getWorlds()), true));
    }

    public function execute_command(CommandSender $sender, string $parent_label, string $label, array $args)
    {
        if (!$sender instanceof Player) return $sender->sendMessage(TranslateMessage::create('commands.not.in.game'));
        if (!isset($args[0])) {
            $sender->sendMessage($this->getUsage($parent_label, $label, '§cUsage: ', '§c', '§6'));
            return;
        }
        $lang = SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang();
        $world = $this->getWorldByName($args[0]);
        if (!$world instanceof World) {
            $sender->sendMessage(TranslateMessage::create('commands.world.not.exists', $lang));
            return;
        }
        $sender->sendMessage(str_replace([
            '{display_name}',
            '{folder_name}',
            '{players}',
            '{generator}',
            '{seed}',
            '{time}',
        ], [
            $world->getDisplayName(),
            $world->getFolderName(),
            count($world->getPlayers()),
            $world->getProvider()->getWorldData()->getGenerator(),
            $world->getSeed(),
            $world->getTime()
        ], "§b-----------------------------\n§aName§7:§e {display_name}\n§aFolder Name§7: §e{folder_name}\n§aPlayers§7:§e {players}\n§aGenerator§7:§e {generator}\n§aSeed§7:§e {seed}\n§aTime§7:§e {time}"));
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
