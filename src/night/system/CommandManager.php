<?php

namespace night\system;

use night\command\EffectCommand;
use night\command\GiveCommand;
use night\command\WorldProtectCmd;
use night\command\WorldCommand;
use pocketmine\command\Command;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\utils\SingletonTrait;
use night\libraries\commands\args\BaseArg;
use night\libraries\commands\args\EnumArg;
use night\libraries\commands\args\IntArg;
use night\libraries\commands\args\StringArg;
use night\libraries\commands\args\TargetArg;
use night\libraries\commands\BaseCommand;
use night\libraries\managers\BaseManager;
use night\libraries\Permission;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketAssembler;
use pocketmine\network\mcpe\protocol\serializer\AvailableCommandsPacketDisassembler;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;

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
        foreach (array_filter(array_map(fn(string $command) => $map->getCommand($command), ['give', 'effect']), fn(?Command $command): bool => !is_null($command)) as $command) $map->unregister($command);
        $this->register_permissions();
        $this->register_commands();
    }

    private function register_permissions(): void
    {
        foreach (
            [
                'globar.command' => Permission::DEFAULT_NOT_OP,
                'world.protect.command' => Permission::DEFAULT_OP,
                'world.command' => Permission::DEFAULT_OP,
                'world.create.command' => Permission::DEFAULT_OP,
                'world.delete.command' => Permission::DEFAULT_OP,
                'world.info.command' => Permission::DEFAULT_OP,
                'world.teleport.command' => Permission::DEFAULT_OP,
            ] as $permission => $default
        ) (new Permission($permission, $default))->build();
    }

    private function register_commands(): void
    {
        $this->registerCommand(...[
            new \night\command\TestCmd(),
            new GiveCommand(),
            new WorldCommand(),
            new EffectCommand(),
            new WorldProtectCmd(),
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
                new CommandOverload(false, [CommandParameter::enum('reload', new CommandHardEnum('reload', ['reload']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('on', new CommandHardEnum('on', ['on']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('off', new CommandHardEnum('off', ['off']), 0)]),
                new CommandOverload(false, [CommandParameter::enum('list', new CommandHardEnum('list', ['list']), 0)]),
                new CommandOverload(false, array_merge([CommandParameter::enum('add', new CommandHardEnum('add', ['add']), 0)], $parser_args([new StringArg('name')]))),
                new CommandOverload(false, array_merge([CommandParameter::enum('remove', new CommandHardEnum('remove', ['remove']), 0)], $parser_args([new StringArg('name')]))),
            ]]
        ];
    }

    private function AvailableCommandsHandler(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) if ($packet instanceof AvailableCommandsPacket) {
            $disassembled = AvailableCommandsPacketDisassembler::disassemble($packet);
            $commandDataList = $disassembled->commandData;
            $soft_enums = [];
            $default = $this->getDefauls_commands();
            foreach ($commandDataList as $commandData) {
                $command = $this->getServer()->getCommandMap()->getCommand($commandData->getName());
                if (isset($default[$commandData->getName()]) && $command instanceof VanillaCommand) {
                    $commandData->overloads = $default[$commandData->getName()]['overloads'] ?? [];
                } elseif ($command instanceof BaseCommand) {
                    $commandData->overloads = $command->getOverloads();
                    $soft_enums = array_merge($soft_enums, $command->getSoftEnums());
                }
            }
            if (!empty($soft_enums)) $packet->softEnums = $soft_enums;
            $pk = AvailableCommandsPacketAssembler::assemble($commandDataList, [], $soft_enums);
            $packet->enumValues = $pk->enumValues;
            $packet->chainedSubCommandValues = $pk->chainedSubCommandValues;
            $packet->postfixes = $pk->postfixes;
            $packet->enums = $pk->enums;
            $packet->chainedSubCommandData = $pk->chainedSubCommandData;
            $packet->commandData = $pk->commandData;
            $packet->softEnums = $pk->softEnums;
            $packet->enumConstraints = $pk->enumConstraints;
        }
    }

    public function registerCommand(Command ...$commands): void
    {
        $this->getServer()->getCommandMap()->registerAll('night', $commands);
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
