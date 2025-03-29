<?php

namespace astreal\system;

use pocketmine\command\Command;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\utils\SingletonTrait;
use astreal\libraries\commands\args\BaseArg;
use astreal\libraries\commands\args\EnumArg;
use astreal\libraries\commands\args\IntArg;
use astreal\libraries\commands\args\StringArg;
use astreal\libraries\commands\args\TargetArg;
use astreal\libraries\commands\BaseCommand;
use astreal\libraries\managers\BaseManager;
use astreal\libraries\Permission;

class CommandManager extends BaseManager
{
    use SingletonTrait;

    /** @var BaseCommand[] $commands */
    private $commands = [];

    public static function getInstance(): CommandManager
    {
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct('CommandManager');
        self::setInstance($this);
        $this->registerEvent(DataPacketSendEvent::class, $this->AvailableCommandsHandler(...));
        $map = $this->getServer()->getCommandMap();
        foreach (array_filter(array_map(fn(string $command) => $map->getCommand($command), []), fn(?Command $command): bool => !is_null($command)) as $command) $map->unregister($command);
        $this->register_permissions();
        $this->register_commands();
    }

    private function register_permissions(): void
    {
        foreach (
            [
                'globar.command' => Permission::DEFAULT_NOT_OP,
            ] as $permission => $default
        ) (new Permission($permission, $default))->build();
    }

    private function register_commands(): void
    {
        $this->registerCommand(...[
            new \astreal\command\TestCmd()
        ]);
    }

    private function getDefauls_commands(): array
    {
        $parser_args = fn(array $args): array => array_map(fn(BaseArg $arg): CommandParameter => $arg->getParameter(), $args);
        return [
            'say' => ['overloads' => [new CommandOverload(false, $parser_args([new StringArg('message')]))]],
            'clear' => ['overloads' => [new CommandOverload(false, $parser_args([new TargetArg('player', true), new EnumArg('itemName', true, 'Item', fn() => array_map(strtolower(...), StringToItemParser::getInstance()->getKnownAliases())), new IntArg('maxCount', true)]))]],
            'kick' => ['overloads' => [new CommandOverload(false, $parser_args([new TargetArg('player'), new StringArg('reason', true)]))]],
            'enchant' => ['overloads' => [new CommandOverload(false, $parser_args([new TargetArg('player'), new EnumArg('enchantment', false, 'Enchant', fn() => array_map(strtolower(...), StringToEnchantmentParser::getInstance()->getKnownAliases())), new IntArg('level', true)]))]],
            'whitelist' => ['overloads' => [
                new CommandOverload(false, [CommandParameter::enum('reload', new CommandEnum('reload', ['reload']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('on', new CommandEnum('on', ['on']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('off', new CommandEnum('off', ['off']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('list', new CommandEnum('list', ['list']), 0)]),
                new CommandOverload(false, array_merge([CommandParameter::enum('add', new CommandEnum('add', ['add']), 0)], $parser_args([new StringArg('name')]))),
                new CommandOverload(false, array_merge([CommandParameter::enum('remove', new CommandEnum('remove', ['remove']), 0)], $parser_args([new StringArg('name')]))),
            ]]
        ];
    }

    private function AvailableCommandsHandler(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) if ($packet instanceof AvailableCommandsPacket) {
            $soft_enums = [];
            foreach ($this->getCommands() as $key => $command) if (isset($packet->commandData[$key])) {
                if (!empty($overloads = $command->getOverloads())) $packet->commandData[$key]->overloads = $overloads;
                $soft_enums = array_merge($soft_enums, $command->getSoftEnums());
            }
            if (!empty($soft_enums)) $packet->softEnums = $soft_enums;
            foreach ($this->getDefauls_commands() as $command => $data) if (isset($packet->commandData[$command]) && !empty($data['overloads'])) $packet->commandData[$command]->overloads = $data['overloads'];
        }
    }

    public function registerCommand(Command ...$commands): void
    {
        $this->getServer()->getCommandMap()->registerAll('astreal', $commands);
        foreach ($commands as $command) if ($command instanceof BaseCommand) $this->commands[$command->getName()] = $command;
    }

    public function getCommand(string $name): ?BaseCommand
    {
        return $this->commands[strtolower($name)] ?? null;
    }

    /** @return BaseCommand[] */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
