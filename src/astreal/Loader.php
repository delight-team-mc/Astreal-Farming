<?php

declare(strict_types=1);

namespace astreal;

use astreal\libraries\inventory\InventoryManager;
use astreal\libraries\LootTableManager;
use astreal\libraries\ScoreAPI;
use astreal\libraries\StructureBlockManager;
use astreal\libraries\vanilla\VanillaManager;
use astreal\system\CommandManager;
use astreal\system\DataProvider;
use astreal\system\FishingRodManager;
use astreal\system\RegisterManager;
use astreal\system\SessionManager;
use astreal\system\WorldProtect;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Path;

class Loader extends PluginBase
{

    private DataProvider $data_provider;
    private SessionManager $session_manager;
    private CommandManager $command_manager;
    private StructureBlockManager $structure_block_manager;
    private FishingRodManager $fishing_rod_manager;
    private LootTableManager $loot_table_manager;
    private WorldProtect $world_protect;

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
        TypeConverter::getInstance()->setSkinAdapter(new class extends LegacySkinAdapter {
            public function fromSkinData(\pocketmine\network\mcpe\protocol\types\skin\SkinData $data): \pocketmine\entity\Skin
            {
                if ($data->isPersona()) return new \pocketmine\entity\Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 4096));
                $capeData = $data->getCapeImage()->getData();
                $resourcePatch = json_decode($data->getResourcePatch(), true);
                if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
                    $geometryName = $resourcePatch["geometry"]["default"];
                } else throw new \pocketmine\entity\InvalidSkinException("Missing geometry name field");
                return new \pocketmine\entity\Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
            }
        });
        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        VanillaManager::getInstance();
        ScoreAPI::make();
        RegisterManager::make();
        new InventoryManager();
        $this->session_manager = new SessionManager(Path::join($this->getDataFolder(), 'sessions'));
        $this->command_manager = new CommandManager();
        $this->structure_block_manager = new StructureBlockManager(Path::join($this->data_provider->getBehaviorFolder(), 'structures'));
        $this->loot_table_manager = new LootTableManager($this->data_provider->getBehaviorFolder());
        $this->fishing_rod_manager = new FishingRodManager();
        $this->world_protect = new WorldProtect();
    }

    public function onDisable(): void
    {
        $this->session_manager->saveAll();
    }
}
