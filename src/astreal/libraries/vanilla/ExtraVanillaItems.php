<?php

declare(strict_types=1);

namespace astreal\libraries\vanilla;

use astreal\libraries\vanilla\item\component\CreativeCategoryComponent;
use astreal\libraries\vanilla\item\component\CreativeGroupComponent;
use astreal\libraries\vanilla\item\component\DisplayNameComponent;
use astreal\libraries\vanilla\item\component\HandEquippedComponent;
use astreal\libraries\vanilla\item\CreativeInventoryInfo;
use astreal\libraries\vanilla\item\ItemComponents;
use astreal\libraries\vanilla\item\component\ItemComponent;
use astreal\libraries\vanilla\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\enchantment\ItemEnchantmentTags;
use pocketmine\item\SpawnEgg;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\math\Vector3;
use pocketmine\world\World;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 */
final class ExtraVanillaItems
{
	use CloningRegistryTrait;

	private function __construct()
	{
		//NOOP
	}

	protected static function register(string $name, Item $item): void
	{
		self::_registryRegister($name, $item);
	}

	/** @return Item[] */
	public static function getAll(): array
	{
		$result = self::_registryGetAll();
		return $result;
	}

	/**
	 * @param Item $item
	 * @param string $icon
	 * @param ItemComponent[] $components
	 */
	private static function getComponents(Item $item, string $icon, array $components = []): Item
	{
		if ($item instanceof ItemComponents) {
			/** @var Item|ItemComponents $item */
			$item->initComponents($icon, CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_ITEMS));
			foreach ($components as $component) $item->addComponent($component);
		}
		return $item;
	}

	private static function getItemNBT(Item $item, string $displayName, string $iconPath, bool $category = true, bool $handEquipped = false, bool $throwable = false, int $width = 16, int $height = 16): Item
	{
		$renderOffsets = CompoundTag::create();
		$display_name = CompoundTag::create();
		$components = CompoundTag::create();
		$properties = CompoundTag::create();
		$cooldown = CompoundTag::create();
		$icon = CompoundTag::create();

		$icon->setString("texture", $iconPath);
		$display_name->setString("value", $displayName);
		if (!$handEquipped) {
			$render = [0.075, 0.125, 0.075];
		} else {
			$render = [0.1, 0.1, 0.1];
		}
		$X = $render[0] / ($width / 16);
		$Y = $render[1] / ($height / 16);
		$Z = $render[2] / ($width / 16);

		$horizontal = ($handEquipped ? 0.075 : 0.1) / ($width / 16);
		$vertical = ($handEquipped ? 0.125 : 0.1) / ($height / 16);
		$renderOffsetsScale = CompoundTag::create()->setTag("scale", new ListTag([
			new FloatTag($X),
			new FloatTag($Y),
			new FloatTag($Z)
		]));
		$renderOffsets->setTag("main_hard", CompoundTag::create()->setTag("first_person", $renderOffsetsScale));
		$renderOffsets->setTag("off_hand", CompoundTag::create()->setTag("third_person", $renderOffsetsScale));
		if ($category) {
			$properties->setString("creative_group", "itemGroup.name.custom");
			$properties->setInt("creative_category", 4);
		}
		$properties->setTag("hand_equipped", new ByteTag($handEquipped ? 1 : 0));
		$properties->setInt("max_stack_size", $item->getMaxStackSize());
		$properties->setTag("minecraft:icon", $icon);
		$components->setTag("minecraft:throwable", new ListTag(["do_swing_animation" => new ByteTag($throwable ? 1 : 0)]));
		$components->setTag("minecraft:render_offsets", $renderOffsets);
		$components->setTag("minecraft:display_name", $display_name);
		$components->setTag("item_properties", $properties);
		$item->getNamedTag()->setTag("components", $components);
		return $item;
	}

	private static function registerSpawnEggs(): void
	{
		self::register("horse_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Horse Spawn Egg") extends BaseSpawnEgg {

			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Horse(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Horse Spawn Egg'), 'spawn_egg');
		self::register("blaze_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Blaze Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Blaze(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Blaze Spawn Egg'), 'spawn_egg');
		self::register("cave_spider_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Cave Spider Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new CaveSpider(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Cave Spider Spawn Egg'), 'spawn_egg');
		self::register("cow_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Cow Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Cow(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Cow Spawn Egg'), 'spawn_egg');
		self::register("creeper_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Creeper Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Creeper(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Creeper Spawn Egg'), 'spawn_egg');
		self::register("enderman_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), " Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Enderman(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Enderman Spawn Egg'), 'spawn_egg');
		self::register("skeleton_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), " Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Skeleton(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Skeleton Spawn Egg'), 'spawn_egg');
		self::register("slime_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), " Spawn Egg") extends BaseSpawnEgg {
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				return new Slime(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		}, 'Skeleton Spawn Egg'), 'spawn_egg');
		$ci = CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_MOB_EGGS);
		self::register("witch_trader_spawn_egg", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), "Witch Trader Spawn Egg") extends BaseSpawnEgg {

			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch): Entity
			{
				$nbt = WitchTraderCommand::load() ?? CompoundTag::create();
				$nbt->setInt(Professions::TAG_PROFESSION, Professions::PROFESSION_LIBRARIAN)->setInt(Professions::TAG_BIOME, Professions::BIOME_PLAINS)->setInt(Professions::TAG_TIER, Professions::TIER_MASTER);
				return new WitchTrader(Location::fromObject($pos, $world, $yaw, $pitch), $nbt);
			}
		}, 'delight:witch_trader_spawn_egg', [new CreativeGroupComponent($ci), new CreativeCategoryComponent($ci)]));
	}

	protected static function setup(): void
	{
		$EQUIPMENT_HELMET_CI = CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_HELMET);
		$EQUIPMENT_SWORD_CI = CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_SWORD);
		self::registerSpawnEggs();
		self::register("ender_eye", self::getComponents(new EnderEye(new ItemIdentifier(ItemTypeIds::newId()), ItemTypeNames::ENDER_EYE), "Ender Eye"), "ender_eye");
		self::register("name_tag", self::getComponents(new NameTag(new ItemIdentifier(ItemTypeIds::newId()), "name_tag"), "nightfall:name_tag", [new HandEquippedComponent(false)]));
		self::register("enchanted_book", self::getComponents(new EnchantedBook(new ItemIdentifier(ItemTypeIds::newId()), "Enchanted Book"), "nightfall:enchanted_book", [new HandEquippedComponent(false)]));
		self::register("bottle", self::getComponents(new Bottle(new ItemIdentifier(ItemTypeIds::newId()), "Bottle"), "nightfall:bottle", [new HandEquippedComponent(false)]));
		self::register("splash_potion", self::getComponents(new SplashPotion(new ItemIdentifier(ItemTypeIds::newId()), "Splash Potion"), "nightfall:splash_potion", [new HandEquippedComponent(false)]));
		self::register("ender_pearl", self::getComponents((new EnderPearl(new ItemIdentifier(ItemTypeIds::newId()), "custom ender pearl")), "nightfall:ender_pearl", [new HandEquippedComponent(false), new DisplayNameComponent("Ender Pearl")]));
		self::register("crowbar", self::getComponents(new Crowbar(new ItemIdentifier(ItemTypeIds::newId()), "Crowbar"), "nightfall:crowbar", [new HandEquippedComponent(true)]));
		self::register("assault_fireball", self::getComponents(new AssaultFireBall(new ItemIdentifier(ItemTypeIds::newId()), "Assault Fireball"), "nightfall:assault_fireball", [new HandEquippedComponent(false)]));
		self::register("kit", self::getComponents(new Kit(new ItemIdentifier(ItemTypeIds::newId()), "kit"), "nightfall:kit", [new HandEquippedComponent(false)]));
		self::register("grapplinghook", self::getComponents(new FishingRod(new ItemIdentifier(ItemTypeIds::newId()), "GrapplingHook"), "nightfall:grapplinghook", [new HandEquippedComponent(true)]));
		self::register("firework_rocket", self::getComponents(new Fireworks(new ItemIdentifier(ItemTypeIds::newId()), "Firework Rocket"), "nightfall:fireworks"));

		self::register("itz_katana", self::getComponents(new KatanaSword(new ItemIdentifier(ItemTypeIds::newId()), 'Katana', ToolTier::NETHERITE(), [ItemEnchantmentTags::SWORD]), 'itz:katana', [new CreativeGroupComponent($EQUIPMENT_SWORD_CI), new CreativeCategoryComponent($EQUIPMENT_SWORD_CI)]));
		self::register("itz_katana_1", self::getComponents(new KatanaSword(new ItemIdentifier(ItemTypeIds::newId()), 'Katana 1', ToolTier::NETHERITE(), [ItemEnchantmentTags::SWORD]), 'itz:katana_1', [new CreativeGroupComponent($EQUIPMENT_SWORD_CI), new CreativeCategoryComponent($EQUIPMENT_SWORD_CI)]));
		self::register("itz_katana_2", self::getComponents(new KatanaSword(new ItemIdentifier(ItemTypeIds::newId()), 'Katana 2', ToolTier::NETHERITE(), [ItemEnchantmentTags::SWORD]), 'itz:katana_2', [new CreativeGroupComponent($EQUIPMENT_SWORD_CI), new CreativeCategoryComponent($EQUIPMENT_SWORD_CI)]));
		self::register("itz_scythe_of_death", self::getComponents(new KatanaSword(new ItemIdentifier(ItemTypeIds::newId()), 'Scrythe Of Death', ToolTier::NETHERITE(), [ItemEnchantmentTags::SWORD]), 'itz:scythe', [new CreativeGroupComponent($EQUIPMENT_SWORD_CI), new CreativeCategoryComponent($EQUIPMENT_SWORD_CI)]));

		self::register("witch_hat", self::getComponents(new WitchHat(new ItemIdentifier(ItemTypeIds::newId()), 'Witch Hat', new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD, 2, false, VanillaArmorMaterials::DIAMOND()), [ItemEnchantmentTags::HELMET]), 'delight:witch_hant', [new CreativeGroupComponent($EQUIPMENT_HELMET_CI), new CreativeCategoryComponent($EQUIPMENT_HELMET_CI), new \astreal\libraries\vanilla\item\component\GlintComponent(true)]));
		self::register("spookey_coin", self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Spookey Coin') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'delight:spookey_coin', [new DisplayNameComponent('§r§6Spookey Coin§r')]));

		self::register("bone", self::getComponents(new Bone(new ItemIdentifier(ItemTypeIds::newId()), "Antitrap Bone"), "nightfall:antitrap_bone", [new HandEquippedComponent(false)]));
		self::register("combo_ability", self::getComponents(new ComboAbility(new ItemIdentifier(ItemTypeIds::newId()), "Combo Ability"), "nightfall:combo_ability", [new HandEquippedComponent(false)]));
		self::register("medkit", self::getComponents(new Medkit(new ItemIdentifier(ItemTypeIds::newId()), "Medkit"), "nightfall:medkit", [new HandEquippedComponent(false)]));
		self::register("strength", self::getComponents(new Strength(new ItemIdentifier(ItemTypeIds::newId()), "Strength"), "nightfall:strength", [new HandEquippedComponent(false)]));
		self::register("pearl_reset", self::getComponents(new PearlReset(new ItemIdentifier(ItemTypeIds::newId()), "Pearl Reset"), "nightfall:pearl_reset", [new HandEquippedComponent(false)]));
		self::register("portable_bard", self::getComponents(new PortableBard(new ItemIdentifier(ItemTypeIds::newId()), "Portable Bard"), "nightfall:portable_bard", [new HandEquippedComponent(false)]));
		self::register("decoy", self::getComponents(new Decoy(new ItemIdentifier(ItemTypeIds::newId()), "Decoy"), "nightfall:decoy", [new HandEquippedComponent(false)]));
		self::register("stick", self::getComponents(new Stick(new ItemIdentifier(ItemTypeIds::newId()), "Stick of Confusion"), "nightfall:stick", [new HandEquippedComponent(true)]));
		self::register("ninjastar", self::getComponents(new NinjaStar(new ItemIdentifier(ItemTypeIds::newId()), "NinjaStar"), "nightfall:ninjastar", [new HandEquippedComponent(false)]));
		self::register("focusmode", self::getComponents(new FocusMode(new ItemIdentifier(ItemTypeIds::newId()), "Focusmode"), "nightfall:focusmode", [new HandEquippedComponent(false)]));
		self::register("effect_disabler", self::getComponents(new EffectDisabler(new ItemIdentifier(ItemTypeIds::newId()), "Effect Disabler"), "nightfall:effect_disabler", [new HandEquippedComponent(false)]));
		self::register("stone_breaker", self::getComponents(new StoneBreaker(new ItemIdentifier(ItemTypeIds::newId()), "Stone Breaker"), "nightfall:stone_breaker", [new HandEquippedComponent(true)]));
		self::register("tank", self::getComponents(new Tank(new ItemIdentifier(ItemTypeIds::newId()), "Tank"), "nightfall:tank", [new HandEquippedComponent(false)]));
		self::register("rose", self::getComponents(new Rose(new ItemIdentifier(ItemTypeIds::newId()), "Power Rose"), "nightfall:rose", [new HandEquippedComponent(false)]));
		self::register("switcher", self::getComponents(new Switcher(new ItemIdentifier(ItemTypeIds::newId()), "Switcher"), "nightfall:switcher", [new HandEquippedComponent(false)]));
		self::register("guardian_angel", self::getComponents(new GuardianAngel(new ItemIdentifier(ItemTypeIds::newId()), "Guardian Angel"), "nightfall:guardian_angel", [new HandEquippedComponent(false)]));
		self::register("last_breath", self::getComponents(new LastBreath(new ItemIdentifier(ItemTypeIds::newId()), "Last Breath"), "nightfall:last_breath", [new HandEquippedComponent(false)]));
		self::register("time_warp", self::getComponents(new TimeWarp(new ItemIdentifier(ItemTypeIds::newId()), "Time Warp"), "nightfall:time_warp", [new HandEquippedComponent(false)]));
		self::register("fakelogger", self::getComponents(new FakeLogger(new ItemIdentifier(ItemTypeIds::newId()), "FakeLogger"), "nightfall:fakelogger", [new HandEquippedComponent(false)]));
		self::register("portable_archer", self::getComponents(new PortableArcher(new ItemIdentifier(ItemTypeIds::newId()), "Portable Archer"), "nightfall:portable_archer", [new HandEquippedComponent(false)]));
		self::register("notch_soup", self::getComponents(new NotchSoup(new ItemIdentifier(ItemTypeIds::newId()), "Notch Soup"), "delight:notch_soup", [new HandEquippedComponent(false)]));
		self::register("p1", self::getComponents(new P1(new ItemIdentifier(ItemTypeIds::newId()), "P1"), "delight:p1", [new HandEquippedComponent(false)]));
		self::register("pumpkin", self::getComponents(new Pumpkin(new ItemIdentifier(ItemTypeIds::newId()), "Pumpkin Ability"), "delight:pumpkin", [new HandEquippedComponent(false)]));
		self::register("zap", self::getComponents(new Zap(new ItemIdentifier(ItemTypeIds::newId()), "Zap"), "delight:zap", [new HandEquippedComponent(false)]));

		self::register("ban", self::getComponents(new Ban(new ItemIdentifier(ItemTypeIds::newId()), "Ban"), "nightfall:ban", [new HandEquippedComponent(false)]));
		self::register("compass", self::getComponents(new Compass(new ItemIdentifier(ItemTypeIds::newId()), "Tp Compass"), "nightfall:compass", [new HandEquippedComponent(false)]));
		self::register("frezen", self::getComponents(new Frezen(new ItemIdentifier(ItemTypeIds::newId()), "Frezen"), "nightfall:frezen", [new HandEquippedComponent(false)]));
		self::register("gamemode", self::getComponents(new Gamemode(new ItemIdentifier(ItemTypeIds::newId()), "Gamemode"), "nightfall:gamemode", [new HandEquippedComponent(false)]));
		self::register("invsee", self::getComponents(new InvSee(new ItemIdentifier(ItemTypeIds::newId()), "InvSee"), "nightfall:invsee", [new HandEquippedComponent(false)]));
		self::register("mute", self::getComponents(new Mute(new ItemIdentifier(ItemTypeIds::newId()), "Mute"), "nightfall:mute", [new HandEquippedComponent(false)]));
		self::register("playerinfo", self::getComponents(new PlayerInfo(new ItemIdentifier(ItemTypeIds::newId()), "PlayerInfo"), "nightfall:playerinfo", [new HandEquippedComponent(false)]));
		self::register("vanish_off", self::getComponents(new VanishOFF(new ItemIdentifier(ItemTypeIds::newId()), "Vanish Off"), "nightfall:vanish_off", [new HandEquippedComponent(false)]));
		self::register("vanish_on", self::getComponents(new VanishON(new ItemIdentifier(ItemTypeIds::newId()), "Vanish On"), "nightfall:vanish_on", [new HandEquippedComponent(false)]));
	}
}
