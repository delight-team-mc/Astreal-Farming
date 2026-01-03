<?php

namespace night\command;

use pocketmine\command\CommandSender;
use pocketmine\world\World;
use night\command\sub_commands\world\CreateSubCommand;
use night\command\sub_commands\world\DeleteSubCommand;
use night\command\sub_commands\world\InfoSubCommand;
use night\command\sub_commands\world\TeleportSubCommand;
use night\libraries\commands\BaseCommand;
use night\libraries\form\simple\FormAPI;
use night\libraries\TranslateMessage;
use night\system\SessionManager;
use pocketmine\player\Player;

class WorldCommand extends BaseCommand
{
    use FormAPI;

    public function __construct()
    {
        parent::__construct('world', 'world manager command');
        $this->setPermission('world.command');
        $this->registerSubCommand(...[
            new CreateSubCommand(),
            new DeleteSubCommand(),
            new InfoSubCommand(),
            new TeleportSubCommand()
        ]);
    }

    public function execute_command(CommandSender $sender, string $label, array $args)
    {
        /*if (!isset($args[0]) && $sender instanceof Player) {
            $this->MainForm($sender);
            return;
        }*/
        $this->sendHelpPage($sender, $label);
    }

    private function MainForm(Player $sender)
    {
        $form = self::createSimpleForm(function (Player $sender, $data) {
            if (is_null($data)) return;
            switch ($data) {
                case 0:
                    $this->TeleportWorlds($sender);
                    break;
                case 1:
                    CreateSubCommand::CreateWorldForm($sender);
                    break;
            }
        });
        $form->setTitle("§eMain Menu");
        $form->addButton("§6World Teleport", 0, "textures/ui/world_glyph_color_2x_black_outline");
        $form->addButton("§6World Creator", 0, "textures/ui/world_glyph_color_2x_black_outline");
        $form->sendToPlayer($sender);
    }

    private function TeleportWorlds(Player $sender)
    {
        $lang = ($sender instanceof Player ? SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang() : TranslateMessage::EN_US);
        /** @var World[] */
        $worlds = [];
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) $worlds[] = $world;
        $form = self::createSimpleForm(function (Player $sender, $data) use ($worlds, $lang) {
            if (is_null($data)) return;
            $world = $worlds[$data];
            if (!$world->isLoaded()) {
                $sender->sendMessage(TranslateMessage::create('commands.world.world-not-loader', $lang)->translate(['{world.folder_name}'], [$world->getFolderName()]));
                return;
            }
            $sender->sendMessage(TranslateMessage::create('commands.world.teleport', $lang)->translate(['{world.folder_name}'], [$world->getFolderName()]));
            if (is_null($world->getOrLoadChunkAtPosition($pos = $world->getSpawnLocation()))) {
                $sender->teleport($pos);
                return;
            }
            $sender->teleport($world->getSafeSpawn());
        });
        $form->setTitle("§eWorldsUI");
        foreach ($worlds as $world) $form->addButton("§b{$world->getFolderName()}", 0, "textures/ui/world_glyph_color_2x_black_outline");
        $form->sendToPlayer($sender);
    }
}
