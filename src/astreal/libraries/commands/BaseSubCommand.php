<?php

namespace astreal\libraries\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use astreal\libraries\commands\args\BaseArg;
use astreal\Loader;

abstract class BaseSubCommand
{

	private array $permission = [];
	private array $args = [];
	private BaseCommand $parent;

	public function __construct(private string $name, private array $alias = []) {}

	public function getName(): string
	{
		return strtolower($this->name);
	}

	public function setAlias(array $alias): void
	{
		$this->alias = $alias;
	}

	public function getAlias(): array
	{
		return $this->alias;
	}

	public function getArgsParser(): ArgumentsParser
	{
		return ArgumentsParser::get();
	}

	/** @return CommandOverload[] */
	public function getOverloads(): array
	{
		$overload = [];
		foreach ($this->getAlias() as $alias) {
			$args = [CommandParameter::enum($alias, new CommandEnum($alias, [$alias]), 0)];
			foreach ($this->getArgs() as $parameter) $args[] = $parameter->getParameter();
			$overload[] = new CommandOverload(false, $args);
		}
		$args = [CommandParameter::enum($this->name, new CommandEnum($this->name, [$this->name]), 0)];
		foreach ($this->getArgs() as $parameter) $args[] = $parameter->getParameter();
		$overload[] = new CommandOverload(false, $args);
		return $overload;
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

	public function getUsage(string $command_label, string $label, string $prefix = '', string $prefix_color = '§a', string $sub_prefix_color = '§e'): string
	{
		return "{$prefix_color}{$prefix}/{$command_label} {$label} {$sub_prefix_color}" . implode(' ', array_map(fn(BaseArg $arg): string => $arg->isOptional() ? "[{$arg->getName()}]" : "<{$arg->getName()}>", $this->getArgs()));
	}

	public function getCommandParent(): BaseCommand
	{
		return $this->parent;
	}

	public function setCommandParent(BaseCommand $command): void
	{
		$this->parent = $command;
	}

	public function update_enums(): void
	{
		$this->getCommandParent()->update_enums();
	}

	/**
	 * @param CommandSender $target
	 * @param string|null $permission
	 * @return bool
	 */
	public function testPermission(CommandSender $target, ?string $permission = null): bool
	{
		return $this->testPermissionSilent($target, $permission);
	}

	public function testPermissionSilent(CommandSender $target, ?string $permission = null): bool
	{
		if (count($this->permission) === 0) return true;
		foreach (($permission !== null ? [$permission] : $this->permission) as $p) if ($target->hasPermission($p)) return true;
		return false;
	}

	public function getPermissions(): array
	{
		return $this->permission;
	}

	/** @param string[] $permissions */
	public function setPermissions(array $permissions): void
	{
		$this->permission = $permissions;
	}

	public function setPermission(?string $permission): void
	{
		$this->setPermissions($permission === null ? [] : explode(";", $permission));
	}

	public function getLoader(): Loader
	{
		return Loader::getInstance();
	}

	public function getServer(): Server
	{
		return Server::getInstance();
	}

	abstract public function execute_command(CommandSender $sender, string $parent_label, string $label, array $args);

	protected function fetchPermittedPlayerTarget(CommandSender $sender, ?string $target, string $selfPermission, string $otherPermission): ?Player
	{
		if ($target !== null) {
			$player = $sender->getServer()->getPlayerByPrefix($target);
		} elseif ($sender instanceof Player) {
			$player = $sender;
		} else throw new InvalidCommandSyntaxException();
		if ($player === null) {
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return null;
		}
		if (($player === $sender && $this->testPermission($sender, $selfPermission)) || ($player !== $sender && $this->testPermission($sender, $otherPermission))) return $player;
		return null;
	}

	protected function getInteger(CommandSender $sender, string $value, int $min = BaseCommand::MIN_COORD, int $max = BaseCommand::MAX_COORD): int
	{
		$i = (int)$value;
		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}
		return $i;
	}

	protected function getRelativeDouble(float $original, CommandSender $sender, string $input, float $min = BaseCommand::MIN_COORD, float $max = BaseCommand::MAX_COORD): float
	{
		if ($input[0] === "~") {
			$value = $this->getDouble($sender, substr($input, 1));
			return $original + $value;
		}
		return $this->getDouble($sender, $input, $min, $max);
	}

	protected function getDouble(CommandSender $sender, string $value, float $min = BaseCommand::MIN_COORD, float $max = BaseCommand::MAX_COORD): float
	{
		$i = (float)$value;
		if ($i < $min) {
			$i = $min;
		} elseif ($i > $max) {
			$i = $max;
		}
		return $i;
	}

	protected function getBoundedInt(CommandSender $sender, string $input, int $min, int $max): ?int
	{
		if (!is_numeric($input)) throw new InvalidCommandSyntaxException();
		$v = (int)$input;
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
