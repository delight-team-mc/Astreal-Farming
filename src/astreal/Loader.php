<?php

declare(strict_types=1);

namespace astreal;

use astreal\libraries\inventory\InventoryManager;
use astreal\libraries\LootTableManager;
use astreal\libraries\StructureBlockManager;
use astreal\libraries\vanilla\VanillaManager;
use astreal\system\CommandManager;
use astreal\system\DataProvider;
use astreal\system\RegisterManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;

class Loader extends PluginBase
{

    private DataProvider $data_provider;
    private CommandManager $command_manager;
    private StructureBlockManager $structure_block_manager;
    private LootTableManager $loot_table_manager;

    use SingletonTrait {
        getInstance as SI;
    }

    public static function getInstance(): self
    {
        return self::SI();
    }

    public function onLoad(): void
    {
        self::setInstance($this);
        $this->data_provider = new DataProvider();
    }

    public function onEnable(): void
    {
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        VanillaManager::getInstance();
        RegisterManager::make();
        new InventoryManager();
        $this->command_manager = new CommandManager();
        $this->structure_block_manager = new StructureBlockManager(Path::join($this->data_provider->getBehaviorFolder(), 'structures'));
        $this->loot_table_manager = new LootTableManager(Path::join($this->data_provider->getBehaviorFolder(), 'loot_tables'));
    }

    public function onDisable(): void {}
}
