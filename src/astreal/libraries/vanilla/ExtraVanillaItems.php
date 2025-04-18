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
 * @method static Item BASIC_NET()
 * @method static Item IRON_NET()
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

	private static function registerSpawnEggs(): void {}

	protected static function setup(): void
	{
		$EQUIPMENT_HELMET_CI = CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_HELMET);
		$EQUIPMENT_SWORD_CI = CreativeInventoryInfo::create(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_SWORD);
		self::register('basic_net', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Basic Net') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'astreal:basic_net'));
		self::register('iron_net', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Iron Net') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'astreal:iron_net'));
	}
}
