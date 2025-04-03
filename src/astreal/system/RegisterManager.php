<?php

namespace astreal\system;

use astreal\AstrealListener;
use astreal\block\tile\BackPack;
use astreal\block\tile\Grave;
use astreal\libraries\managers\BaseManager;
use astreal\libraries\vanilla\block\BlockFactory;
use astreal\libraries\vanilla\CraftingRegister;
use astreal\libraries\vanilla\CustomBlockTypeNames;
use astreal\libraries\vanilla\ExtraVanillaBlocks;
use astreal\libraries\vanilla\item\CreativeInventoryInfo;
use astreal\listeners\StructureListener;
use astreal\world\overworld\AstrealOverworldGenerator;
use muqsit\vanillagenerator\generator\nether\NetherGenerator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use pocketmine\block\tile\TileFactory;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\WorldCreationOptions;

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
        //CraftingRegister::load();
        $this->setup_recipes();
        $this->setup_entitys();
        $this->setup_blocks();
        $this->setup_tiles();
        $this->setup_listeners();
        $this->setup_generators();
        if (!$this->getServer()->getWorldManager()->loadWorld('overworld')) $this->getServer()->getWorldManager()->generateWorld('overworld', WorldCreationOptions::create()->setGeneratorClass(AstrealOverworldGenerator::class));
    }

    public function setup_tiles(): void
    {
        $register = TileFactory::getInstance()->register(...);
        $register(BackPack::class, ['astreal:backpack', 'backpack']);
        $register(Grave::class, ['astreal:grave', 'grave']);
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
    }

    private function setup_entitys(): void {}

    private function setup_items(): void {}

    private function setup_recipes(): void
    {

        /*foreach (
            [
                new ShapedRecipe(["AAA", "ABA", "AAA"], ["A" => new ExactRecipeIngredient(VanillaItems::GOLD_INGOT()), "B" => new ExactRecipeIngredient(VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::SKELETON())->asItem())], [CustomItems::GOLDEN_HEAD()])
            ] as $recipe
        ) $this->getServer()->getCraftingManager()->registerShapedRecipe($recipe);*/
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
