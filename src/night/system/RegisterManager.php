<?php

namespace night\system;

use night\NightListener;
use night\block\tile\BackPack;
use night\block\tile\FishingNet;
use night\block\tile\Grave;
use night\entity\projectile\FishingHook;
use night\libraries\managers\BaseManager;
use night\libraries\vanilla\block\BlockFactory;
use night\libraries\vanilla\CraftingRegister;
use night\libraries\vanilla\CustomBlockTypeNames;
use night\libraries\vanilla\CustomItemTypeNames;
use night\libraries\vanilla\ExtraVanillaBlocks;
use night\libraries\vanilla\ExtraVanillaItems;
use night\libraries\vanilla\item\CreativeInventoryInfo;
use night\libraries\vanilla\item\ItemFactory;
use night\listeners\StructureListener;
use night\task\ScoreTask;
use night\world\overworld\AstrealOverworldGenerator;
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
        $this->getServer()->getWorldManager()->loadWorld('overworld');
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
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::POTATO_CRATE(), 'night:potato_crate', CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
        BlockFactory::getInstance()->registerBlock(static fn() => ExtraVanillaBlocks::ONION(), 'night:onions', CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
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
        $register = ItemFactory::getInstance()->registerItem(...);
        $register(CustomItemTypeNames::BASIC_FISHING_NET, ExtraVanillaItems::BASIC_NET());
        $register(CustomItemTypeNames::IRON_FISHING_NET, ExtraVanillaItems::IRON_NET());
        $register(CustomItemTypeNames::EMPTY_EXPERIENCE_BOTTLE, ExtraVanillaItems::EMPTY_EXPERIENCE_BOTTLE());
        $register(CustomItemTypeNames::EXPERIENCE_BOTTLE, ExtraVanillaItems::EXPERIENCE_BOTTLE());
        $register(CustomItemTypeNames::WORM, ExtraVanillaItems::WORM());
        $register(CustomItemTypeNames::HOURGLASS, ExtraVanillaItems::HOURGLASS());
        $register('night:onion', ExtraVanillaItems::ONION(), version: 0, creativeInfo: CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_CROP));
    }

    public function setup_listeners(): void
    {
        $register = $this->getServer()->getPluginManager()->registerEvents(...);
        $register(new NightListener(), $this->getLoader());
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
