<?php

namespace night\command\sub_commands\world;

use pocketmine\command\CommandSender;
use pocketmine\world\World;
use night\libraries\commands\args\EnumArg;
use night\libraries\commands\BaseSubCommand;
use night\libraries\TranslateMessage;
use night\system\DataProvider;
use night\system\SessionManager;
use pocketmine\player\Player;

class DeleteSubCommand extends BaseSubCommand
{

    public function __construct()
    {
        parent::__construct('delete');
        $this->setPermission('world.delete.command');
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
            $sender->sendMessage(TranslateMessage::create('commands.world.not.exists', $lang)->translate(['{world}'], [$args[0]]));
            return;
        }
        $files = $this->remove_world($world);
        $sender->sendMessage(TranslateMessage::create('commands.world.delete.success', $lang)->translate(['{world}', '{total_files}'], [($world->getFolderName() === $world->getDisplayName() ? $world->getDisplayName() : "{$world->getDisplayName()} ({$world->getFolderName()})"), $files]));
        $this->update_enums();
    }

    private function remove_world(World $world): int
    {
        $default = $this->getServer()->getWorldManager()->getDefaultWorld();
        if ($default !== null) foreach ($world->getPlayers() as $player) {
            try {
                $player->teleport($default->getSafeSpawn());
            } catch (\Throwable $th) {
                $player->teleport($default->getSpawnLocation());
            }
        }
        if ($world->isLoaded()) $this->getServer()->getWorldManager()->unloadWorld($world, true);
        return DataProvider::getInstance()->removeContainerFiles($this->getServer()->getDataPath() . DIRECTORY_SEPARATOR . 'worlds' . DIRECTORY_SEPARATOR . $world->getFolderName());
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
