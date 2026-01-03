<?php

namespace night\libraries\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use night\libraries\commands\args\BaseArg;
use night\libraries\commands\args\EnumArg;
use night\Loader;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

abstract class BaseCommand extends Command implements PluginOwned
{
	/**
	 * @var BaseSubCommand[] $sub_commands
	 * @var BaseArg[] $args
	 */
	private array $sub_commands = [], $args = [];

	/** @return CommandOverload[] */
	public function getOverloads(): array
	{
		$overloads = [];
		foreach ($this->getSubCommands() as $subCommand) $overloads = array_merge($overloads, $subCommand->getOverloads());
		$args = array_map(fn(BaseArg $arg) => $arg->getParameter(), $this->getArgs());
		if (!empty($args)) $overloads[] = new CommandOverload(false, $args);
		return $overloads;
	}

	public function registerSubCommand(BaseSubCommand ...$sub_commands): void
	{
		foreach ($sub_commands as $sub_command) {
			$sub_command->setCommandParent($this);
			$this->sub_commands[$sub_command->getName()] = $sub_command;
			foreach ($sub_command->getAlias() as $alias) $this->sub_commands[$alias] = $sub_command;
		}
	}

	/** @return BaseSubCommand[] */
	public function getSubCommands(): array
	{
		return $this->sub_commands;
	}

	public function getSubCommand(string $name): ?BaseSubCommand
	{
		return $this->sub_commands[strtolower($name)] ?? null;
	}

	public function registerArg(BaseArg ...$args): void
	{
		foreach ($args as $arg) $this->args[$arg->getName()] = $arg;
	}

	/** @return BaseArg[] */
	public function getArgs(): array
	{
		return $this->args;
	}

	public function getArgsParser(): ArgumentsParser
	{
		return ArgumentsParser::get();
	}

	public function execute(CommandSender $sender, string $label, array $args)
	{
		if (!$this->testPermission($sender)) return;
		if (!isset($args[0])) return $this->execute_command($sender, $label, $args);
		if (!is_null(($sub_command = $this->getSubCommand($args[0])))) {
			if (!$sub_command->testPermission($sender)) return $this->execute_command($sender, $label, $args);
			$sub_label = array_shift($args);
			return $sub_command->execute_command($sender, $label, $sub_label, $args);
		}
		$this->execute_command($sender, $label, $args);
	}

	public function sendHelpPage(CommandSender $sender, string $label): void
	{
		if (count($this->getArgs()) > 0) $sender->sendMessage("§b- §a/{$label}§e " . implode(' ', array_map(fn(BaseArg $arg): string => $arg->isOptional() ? "[{$arg->getName()}]" : "<{$arg->getName()}>", $this->getArgs())));
		if (count($this->getSubCommands()) > 0) $sender->sendMessage(implode(PHP_EOL, array_map(fn(BaseSubCommand $sub_command): string => "§b- §a/{$label} §e{$sub_command->getName()} " . implode(' ', array_map(fn(BaseArg $arg): string => $arg->isOptional() ? "[{$arg->getName()}]" : "<{$arg->getName()}>", $sub_command->getArgs())), $this->getSubCommands())));
	}

	public function getUsageMessage(string $label, string $prefix = '', string $prefix_color = '§a', string $sub_prefix_color = '§e'): string
	{
		return "{$prefix_color}{$prefix}/{$label} {$sub_prefix_color}" . implode(' ', array_map(fn(BaseArg $arg): string => $arg->isOptional() ? "[{$arg->getName()}]" : "<{$arg->getName()}>", $this->getArgs()));
	}

	public function update_enums(): void
	{
		$args = $this->getArgs();
		foreach ($this->getSubCommands() as $subCommand) $args = array_merge($args, $subCommand->getArgs());
		$packets = array_map(fn(EnumArg $arg): UpdateSoftEnumPacket => UpdateSoftEnumPacket::create($arg->getEnumName(), $arg->getEnumValues(), UpdateSoftEnumPacket::TYPE_SET), array_filter($args, fn($arg): bool => $arg instanceof EnumArg && $arg->isSoft()));
		if (!empty($packets)) NetworkBroadcastUtils::broadcastPackets($this->getServer()->getOnlinePlayers(), $packets);
	}

	/** @return CommandSoftEnum[] */
	public function getSoftEnums(): array
	{
		$args = $this->getArgs();
		foreach ($this->getSubCommands() as $subCommand) $args = array_merge($args, $subCommand->getArgs());
		return array_map(fn(EnumArg $arg): CommandSoftEnum => new CommandSoftEnum($arg->getCommandEnum()->getName(), $arg->getCommandEnum()->getValues()), array_filter($args, fn($arg): bool => $arg instanceof EnumArg && $arg->isSoft()));
	}

	public function getOwningPlugin(): Plugin
	{
		return Loader::getInstance();
	}

	public function getLoader(): Loader
	{
		return Loader::getInstance();
	}

	public function getServer(): Server
	{
		return Server::getInstance();
	}

	abstract public function execute_command(CommandSender $sender, string $label, array $args);

	public const MAX_COORD = 30000000;
	public const MIN_COORD = -30000000;

	protected function fetchPermittedPlayerTarget(CommandSender $sender, ?string $target, string $selfPermission, string $otherPermission): ?Player
	{
		if ($target !== null) {
			$player = $sender->getServer()->getPlayerByPrefix($target);
		} elseif ($sender instanceof Player) {
			$player = $sender;
		} else {
			throw new InvalidCommandSyntaxException();
		}

		if ($player === null) {
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return null;
		}
		if (
			($player === $sender && $this->testPermission($sender, $selfPermission)) ||
			($player !== $sender && $this->testPermission($sender, $otherPermission))
		) {
			return $player;
		}
		return null;
	}

	protected function getInteger(CommandSender $sender, string $value, int $min = self::MIN_COORD, int $max = self::MAX_COORD): int
	{
		$i = (int) $value;

		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}

		return $i;
	}

	protected function getRelativeDouble(float $original, CommandSender $sender, string $input, float $min = self::MIN_COORD, float $max = self::MAX_COORD): float
	{
		if ($input[0] === "~") {
			$value = $this->getDouble($sender, substr($input, 1));

			return $original + $value;
		}

		return $this->getDouble($sender, $input, $min, $max);
	}

	protected function getDouble(CommandSender $sender, string $value, float $min = self::MIN_COORD, float $max = self::MAX_COORD): float
	{
		$i = (float) $value;

		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}

		return $i;
	}

	protected function getBoundedInt(CommandSender $sender, string $input, int $min, int $max): ?int
	{
		if (!is_numeric($input)) {
			throw new InvalidCommandSyntaxException();
		}

		$v = (int) $input;
		if ($v > $max) {
			$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig($input, (string) $max)->prefix(TextFormat::RED));
			return null;
		}
		if ($v < $min) {
			$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooSmall($input, (string) $min)->prefix(TextFormat::RED));
			return null;
		}

		return $v;
	}
}
