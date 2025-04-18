<?php

namespace astreal\system;

use astreal\AstrealListener;
use astreal\block\tile\BackPack;
use astreal\block\tile\FishingNet;
use astreal\block\tile\Grave;
use astreal\entity\projectile\FishingHook;
use astreal\libraries\managers\BaseManager;
use astreal\libraries\vanilla\block\BlockFactory;
use astreal\libraries\vanilla\CraftingRegister;
use astreal\libraries\vanilla\CustomBlockTypeNames;
use astreal\libraries\vanilla\CustomItemTypeNames;
use astreal\libraries\vanilla\ExtraVanillaBlocks;
use astreal\libraries\vanilla\ExtraVanillaItems;
use astreal\libraries\vanilla\item\CreativeInventoryInfo;
use astreal\libraries\vanilla\item\ItemFactory;
use astreal\listeners\StructureListener;
use astreal\task\ScoreTask;
use astreal\world\overworld\AstrealOverworldGenerator;
use muqsit\vanillagenerator\generator\nether\NetherGenerator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use pocketmine\block\tile\TileFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Symfony\Component\Filesystem\Path;

class RegisterManager extends BaseManager
{
    use SingletonTrait;

    public static function make(): void
    {
        new self();
    }

    public static function getInstance(): RegisterManager
    {
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct('RegisterManager');
        self::setInstance($this);
        $this->setup_items();
        $this->setup_blocks();
        CraftingRegister::load(Path::join($this->getLoader()->getDataFolder(), 'CraftingRecipes.json'), Path::join($this->getLoader()->getDataFolder(), 'CraftingTags.json'), Path::join(DataProvider::getInstance()->getBehaviorFolder(), 'recipes'));
        $this->setup_entitys();
        $this->setup_tiles();
        $this->setup_listeners();
        $this->setup_generators();
        $this->setup_tasks();
        if (!$this->getServer()->getWorldManager()->loadWorld('overworld')) $this->getServer()->getWorldManager()->generateWorld('overworld', WorldCreationOptions::create()->setGeneratorClass(AstrealOverworldGenerator::class));
    }

    public function setup_tiles(): void
    {
        $register = TileFactory::getInstance()->register(...);
        $register(BackPack::class, ['astreal:backpack', 'backpack']);
        $register(Grave::class, ['astreal:grave', 'grave']);
        $register(FishingNet::class, ['astreal:fishing_net', 'fishing_net']);
    }

    public function setup_blocks(): void
    {
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::BACKPACK(), CustomBlockTypeNames::BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::BLACK_BACKPACK(), CustomBlockTypeNames::BLACK_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::BLUE_BACKPACK(), CustomBlockTypeNames::BLUE_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::BROWN_BACKPACK(), CustomBlockTypeNames::BROWN_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::CYAN_BACKPACK(), CustomBlockTypeNames::CYAN_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::GRAY_BACKPACK(), CustomBlockTypeNames::GRAY_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::GREEN_BACKPACK(), CustomBlockTypeNames::GREEN_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::LIGHT_BLUE_BACKPACK(), CustomBlockTypeNames::LIGHT_BLUE_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::LIGHT_GRAY_BACKPACK(), CustomBlockTypeNames::LIGHT_GRAY_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::LIME_BACKPACK(), CustomBlockTypeNames::LIME_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::MAGENTA_BACKPACK(), CustomBlockTypeNames::MAGENTA_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::ORANGE_BACKPACK(), CustomBlockTypeNames::ORANGE_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::PINK_BACKPACK(), CustomBlockTypeNames::PINK_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::PURPLE_BACKPACK(), CustomBlockTypeNames::PURPLE_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::RED_BACKPACK(), CustomBlockTypeNames::RED_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::WHITE_BACKPACK(), CustomBlockTypeNames::WHITE_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::YELLOW_BACKPACK(), CustomBlockTypeNames::YELLOW_BACKPACK, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION, 'itemGroup.name.backpacks'));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::STONE_GRAVE(), CustomBlockTypeNames::STONE_GRAVE, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::GRAVEL_GRAVE(), CustomBlockTypeNames::GRAVEL_GRAVE, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_CONSTRUCTION));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::FISHING_FRAME(), CustomBlockTypeNames::FISHING_FRAME, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::BASIC_FISHING_NET(), CustomBlockTypeNames::BASIC_FISHING_NET, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::IRON_FISHING_NET(), CustomBlockTypeNames::IRON_FISHING_NET, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
    }

    private function setup_entitys(): void
    {
        $pm_register = \pocketmine\entity\EntityFactory::getInstance()->register(...);
        $pm_register(FishingHook::class, fn(World $world, CompoundTag $nbt): Entity => new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt), ['fishing_hook', 'minecraft:fishing_hook']);
    }

    private function setup_tasks(): void
    {
        ($schedule = $this->getLoader()->getScheduler())->scheduleRepeatingTask(new ScoreTask(), 20);
    }

    private function setup_items(): void
    {
        ItemFactory::getInstance()->registerItem(CustomItemTypeNames::BASIC_FISHING_NET, ExtraVanillaItems::BASIC_NET());
        ItemFactory::getInstance()->registerItem(CustomItemTypeNames::IRON_FISHING_NET, ExtraVanillaItems::IRON_NET());
    }

    public function setup_listeners(): void
    {
        $register = $this->getServer()->getPluginManager()->registerEvents(...);
        $register(new AstrealListener(), $this->getLoader());
        $register(new StructureListener(), $this->getLoader());
    }

    public function setup_generators(): void
    {
        $generator_manager = GeneratorManager::getInstance();
        $generator_manager->addGenerator(NetherGenerator::class, "vanilla_nether", fn() => null);
        $generator_manager->addGenerator(OverworldGenerator::class, "vanilla_overworld", fn() => null);
        $generator_manager->addGenerator(AstrealOverworldGenerator::class, "astreal_overworld", fn() => null);
    }
}
