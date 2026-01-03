<?php

namespace night\command;

use night\libraries\commands\args\EnumArg;
use night\libraries\commands\args\IntArg;
use night\libraries\commands\args\JsonArg;
use night\libraries\commands\args\TargetArg;
use night\libraries\commands\BaseCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtException;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class GiveCommand extends BaseCommand
{

	public function __construct()
	{
		parent::__construct("give", KnownTranslationFactory::pocketmine_command_give_description(), KnownTranslationFactory::pocketmine_command_give_usage());
		$this->setPermissions([DefaultPermissionNames::COMMAND_GIVE_SELF, DefaultPermissionNames::COMMAND_GIVE_OTHER]);
		$this->registerArg(...[
			new TargetArg('player'),
			new EnumArg('itemName', false, 'Item', fn() => array_map(strtolower(...), StringToItemParser::getInstance()->getKnownAliases())),
			new IntArg('amount', true),
			new JsonArg('components', true)
		]);
		#/give <player: target> <itemName: Item> [amount: int] [components: json]
	}

	public function execute_command(CommandSender $sender, string $label, array $args)
	{
		if (count($args) < 2) throw new InvalidCommandSyntaxException();
		/** @var Player[] $selects */
		$selects = array_filter($this->getArgsParser()::parserTarget($sender, $args[0], false, true), fn($select): bool => $select instanceof Player);
		if (count($selects) === 0) return true;
		try {
			$item = StringToItemParser::getInstance()->parse($args[1]) ?? LegacyStringToItemParser::getInstance()->parse($args[1]);
		} catch (LegacyStringToItemParserException $e) {
			$sender->sendMessage(KnownTranslationFactory::commands_give_item_notFound($args[1])->prefix(TextFormat::RED));
			return true;
		}
		if (!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$count = $this->getBoundedInt($sender, $args[2], 1, 32767);
			if ($count === null) return true;
			$item->setCount($count);
		}
		if (isset($args[3])) {
			$data = implode(" ", array_slice($args, 3));
			try {
				$tags = JsonNbtParser::parseJson($data);
			} catch (NbtDataException $e) {
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return true;
			}
			try {
				$item->setNamedTag($tags);
			} catch (NbtException $e) {
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return true;
			}
		}
		if (count($selects) === 1) {
			$player = array_shift($selects);
			foreach ($player->getInventory()->addItem($item) as $drop) $player->dropItem($drop);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_give_success($item->getName() . " (" . $args[1] . ")", (string)$item->getCount(), $player->getName()));
		} else {
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_give_success($item->getName() . " (" . $args[1] . ")", (string)$item->getCount(), count($selects) . " players"));
			foreach ($selects as $player) foreach ($player->getInventory()->addItem($item) as $drop) $player->dropItem($drop);
		}
		return true;
	}
}
