<?php

namespace astreal\command;

use astreal\libraries\commands\args\BoleanArg;
use astreal\libraries\commands\args\EnumArg;
use astreal\libraries\commands\ArgumentsParser;
use astreal\libraries\commands\BaseCommand;
use astreal\libraries\TranslateMessage;
use astreal\system\Protect;
use astreal\system\SessionManager;
use astreal\system\WorldProtect;
use pocketmine\command\CommandSender;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class WorldProtectCmd extends BaseCommand
{

    public function __construct()
    {
        parent::__construct('worldprotect', 'worldprotect command', aliases: ['wp']);
        $this->setPermission('world.protect.command');
        $this->registerArg(...[
            new EnumArg('world', false, 'world_name', fn() => array_map(fn(World $world) => strtolower($world->getFolderName()), Server::getInstance()->getWorldManager()->getWorlds()), true),
            new EnumArg('flag', false, 'flag_type', fn() => Protect::AVAIABLES_FLAGS, false),
            new BoleanArg('flag_value', false)
        ]);
    }

    public function execute_command(CommandSender $sender, string $label, array $args)
    {
        $lang = ($sender instanceof Player ? SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang() : TranslateMessage::EN_US);
        if (!isset($args[2])) return $sender->sendMessage($this->getUsageMessage($label, 'Usage: ', '§c', '§6'));
        $world = $this->getWorldByName($args[0]);
        $flag = $args[1];
        $flag_value = in_array($flag, [Protect::BLOCK_BREAK_LIST, Protect::BLOCK_PLACE_LIST]) ? ($args[2]) : ArgumentsParser::parserBoolean($args[2], true);
        if (!$world instanceof World) return $sender->sendMessage(TranslateMessage::create('command.worldprotect.world-not-found', $lang));
        if (!in_array($flag, Protect::AVAIABLES_FLAGS)) return $sender->sendMessage(TranslateMessage::create('command.worldprotect.flag-not-found', $lang));
        $protect = WorldProtect::getInstance()->getProtectByWorld($world) ?? WorldProtect::getInstance()->createProtect($world);
        if (in_array($flag, [Protect::BLOCK_BREAK_LIST, Protect::BLOCK_PLACE_LIST])) {
            $root = $protect->getRoot();
            $list = array_map(fn(StringTag $tag): string => $tag->getValue(), ($root->getListTag($flag) ?? new ListTag([], NBT::TAG_String))->getValue());
            if (in_array($flag_value, $list)) {
                $list = array_filter($list, fn($item) => $item !== $flag_value);
                $sender->sendMessage(TranslateMessage::create('command.worldprotect.list-flag-remove', $lang)->translate(['{world}', '{flag}', '{flag_value}'], [$world->getFolderName(), $flag, $flag_value]));
            } else {
                $list[] = $flag_value;
                $sender->sendMessage(TranslateMessage::create('command.worldprotect.list-flag-set', $lang)->translate(['{world}', '{flag}', '{flag_value}'], [$world->getFolderName(), $flag, $flag_value]));
            }
            $root->setTag($flag, new ListTag(array_map(fn($val) => new StringTag($val), $list), NBT::TAG_String));
            $protect->setRoot($root);
        } else {
            $protect->setFlag($flag, $flag_value);
            $sender->sendMessage(TranslateMessage::create('command.worldprotect.flag-set', $lang)->translate(['{world}', '{flag}', '{flag_value}'], [$world->getFolderName(), $flag, ($flag_value ? 'true' : 'false')]));
        }
        $protect->save();
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
