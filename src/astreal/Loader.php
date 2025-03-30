<?php

declare(strict_types=1);

namespace astreal;

use astreal\libraries\inventory\InventoryManager;
use astreal\libraries\LootTableManager;
use astreal\libraries\StructureBlockManager;
use astreal\libraries\vanilla\VanillaManager;
use astreal\system\CommandManager;
use astreal\system\RegisterManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase
{

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
        $this->structure_block_manager = new StructureBlockManager($this->getDataFolder() . 'structures');
    }

    public function onEnable(): void
    {
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        VanillaManager::getInstance();
        RegisterManager::make();
        new InventoryManager();
        $this->command_manager = new CommandManager();
        $this->loot_table_manager = new LootTableManager($this->getDataFolder() . 'loot_tables');
    }

    public function onDisable(): void {}

    private function listeners(): void
    {
        $register = $this->getServer()->getPluginManager()->registerEvents(...);
        $register(new AstrealListener(), $this);
    }
}
