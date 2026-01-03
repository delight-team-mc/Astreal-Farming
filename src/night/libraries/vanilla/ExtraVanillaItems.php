<?php

declare(strict_types=1);

namespace night\libraries\vanilla;

use night\libraries\vanilla\block\BlockFactory;
use night\libraries\vanilla\item\ItemComponents;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\component\SeedComponent;
use night\libraries\vanilla\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Food;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\utils\CloningRegistryTrait;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ByteTag;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Item BASIC_NET()
 * @method static Item IRON_NET()
 * @method static Item EMPTY_EXPERIENCE_BOTTLE()
 * @method static Item EXPERIENCE_BOTTLE()
 * @method static Item WORM()
 * @method static Item HOURGLASS()
 */
final class ExtraVanillaItems
{ //night:blueberry_bush_stage_ night:blueberrys night:blueberry_bush
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
			$item->initComponent($icon);
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
		self::register('basic_net', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Basic Net') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'night:basic_net'));
		self::register('iron_net', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Iron Net') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'night:iron_net'));
		self::register('empty_experience_bottle', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Empty Experience Bottle') extends Item implements ItemComponents {
			use ItemComponentsTrait;

			public function getMaxStackSize(): int
			{
				return 1;
			}
		}, 'night:empty_experience_bottle'));
		self::register('experience_bottle', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Experience Bottle') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'night:experience_bottle'));
		self::register('worm', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Worm') extends Item implements ItemComponents {
			use ItemComponentsTrait;
		}, 'night:worm'));
		self::register('hourglass', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Hourglass') extends Item implements ItemComponents {
			use ItemComponentsTrait;

			public function getMaxStackSize(): int
			{
				return 1;
			}
		}, 'night:hourglass'));
		self::register('blueberrys', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Blueberrys') extends Food implements ItemComponents {
			use ItemComponentsTrait;

			public function getAttackPoints(): int
			{
				return 0;
			}

			public function getFoodRestore(): int
			{
				return 2;
			}

			public function getSaturationRestore(): float
			{
				return 1.2;
			}

			public function getBlock(?int $clickedFace = null): Block
			{
				return BlockFactory::getInstance()->get('night:blueberry_bush');
			}
		}, 'night:blueberrys'));
		self::register('onion', self::getComponents(new class(new ItemIdentifier(ItemTypeIds::newId()), 'Onion') extends Food implements ItemComponents {
			use ItemComponentsTrait;

			public function getBlock(?int $clickedFace = null): Block
			{
				return BlockFactory::getInstance()->get('night:onions');
			}

			public function getFoodRestore(): int
			{
				return 3;
			}

			public function getSaturationRestore(): float
			{
				return 4.8;
			}

			public function getAttackPoints(): int
			{
				return 0;
			}

			public function getMaxStackSize(): int
			{
				return 64;
			}
		}, 'night:onion', [])); //(new SeedComponent('night:onions'))->withBlocks(VanillaBlocks::FARMLAND())
	}
}
