<?php

declare(strict_types=1);

namespace astreal\libraries\vanilla;

use astreal\block\BackPack;
use astreal\block\DyedBackpack;
use astreal\block\FishingFrame;
use astreal\block\FishingNet;
use astreal\block\Grave;
use astreal\block\tile\BackPack as TitleBackPack;
use astreal\block\tile\FishingNet as TileFishingNet;
use astreal\block\tile\Grave as TileGrave;
use astreal\block\utils\GraveType;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockToolType;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\utils\CloningRegistryTrait;

/**
 * @method static BackPack BACKPACK()
 * @method static DyedBackPack BLACK_BACKPACK()
 * @method static DyedBackPack BLUE_BACKPACK()
 * @method static DyedBackPack BROWN_BACKPACK()
 * @method static DyedBackPack CYAN_BACKPACK()
 * @method static DyedBackPack GRAY_BACKPACK()
 * @method static DyedBackPack GREEN_BACKPACK()
 * @method static DyedBackPack LIGHT_BLUE_BACKPACK()
 * @method static DyedBackPack LIGHT_GRAY_BACKPACK()
 * @method static DyedBackPack LIME_BACKPACK()
 * @method static DyedBackPack MAGENTA_BACKPACK()
 * @method static DyedBackPack ORANGE_BACKPACK()
 * @method static DyedBackPack PINK_BACKPACK()
 * @method static DyedBackPack PURPLE_BACKPACK()
 * @method static DyedBackPack RED_BACKPACK()
 * @method static DyedBackPack WHITE_BACKPACK()
 * @method static DyedBackPack YELLOW_BACKPACK()
 * @method static Grave STONE_GRAVE()
 * @method static Grave GRAVEL_GRAVE()
 * @method static FishingFrame FISHING_FRAME()
 * @method static FishingNet BASIC_FISHING_NET()
 * @method static FishingNet IRON_FISHING_NET()
 * 
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 */
final class ExtraVanillaBlocks
{
	use CloningRegistryTrait;

	private function __construct()
	{
		//NOOP
	}

	protected static function register(string $name, Block $block): void
	{
		self::_registryRegister($name, $block);
	}

	/** @return Block[] */
	public static function getAll(): array
	{
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup(): void
	{
		\pocketmine\block\VanillaBlocks::WALL_BANNER();
		self::register('fishing_frame', new FishingFrame(new BlockIdentifier(BlockTypeIds::newId()), 'Fishing Frame', new BlockTypeInfo(BlockBreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));
		self::register('basic_fishing_net', (new FishingNet(new BlockIdentifier(BlockTypeIds::newId(), TileFishingNet::class), 'Basic Fishing Net', new BlockTypeInfo(BlockBreakInfo::axe(2.0, null, 15.0))))->setMaterial('astreal:basic_fishing_net'));
		self::register('iron_fishing_net', (new FishingNet(new BlockIdentifier(BlockTypeIds::newId(), TileFishingNet::class), 'Iron Fishing Net', new BlockTypeInfo(BlockBreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))))->setMaterial('astreal:iron_fishing_net'));
		self::register('stone_grave', (new Grave(new BlockIdentifier(BlockTypeIds::newId(), TileGrave::class), 'Stone Grave', new BlockTypeInfo(BlockBreakInfo::pickaxe(2.0, ToolTier::STONE, 30.0))))->setType(GraveType::STONE())->setTexture('astreal:stone_grave', 'geometry.grave.var.1'));
		self::register('gravel_grave', (new Grave(new BlockIdentifier(BlockTypeIds::newId(), TileGrave::class), 'Gravel Grave', new BlockTypeInfo(BlockBreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0))))->setType(GraveType::GRAVEL())->setTexture('astreal:gravel_grave', 'geometry.grave.var.2'));
		self::register('backpack', new BackPack(new BlockIdentifier(BlockTypeIds::newId(), TitleBackPack::class), 'BackPack', new BlockTypeInfo(new BlockBreakInfo(0.8, BlockToolType::SHEARS))));
		foreach (DyeColor::getAll() as $color) {
			$id = match ($color) {
				DyeColor::BLACK() => CustomBlockTypeNames::BLACK_BACKPACK,
				DyeColor::BLUE() => CustomBlockTypeNames::BLUE_BACKPACK,
				DyeColor::BROWN() => CustomBlockTypeNames::BROWN_BACKPACK,
				DyeColor::CYAN() => CustomBlockTypeNames::CYAN_BACKPACK,
				DyeColor::GRAY() => CustomBlockTypeNames::GRAY_BACKPACK,
				DyeColor::GREEN() => CustomBlockTypeNames::GREEN_BACKPACK,
				DyeColor::LIGHT_BLUE() => CustomBlockTypeNames::LIGHT_BLUE_BACKPACK,
				DyeColor::LIGHT_GRAY() => CustomBlockTypeNames::LIGHT_GRAY_BACKPACK,
				DyeColor::LIME() => CustomBlockTypeNames::LIME_BACKPACK,
				DyeColor::MAGENTA() => CustomBlockTypeNames::MAGENTA_BACKPACK,
				DyeColor::ORANGE() => CustomBlockTypeNames::ORANGE_BACKPACK,
				DyeColor::PINK() => CustomBlockTypeNames::PINK_BACKPACK,
				DyeColor::PURPLE() => CustomBlockTypeNames::PURPLE_BACKPACK,
				DyeColor::RED() => CustomBlockTypeNames::RED_BACKPACK,
				DyeColor::WHITE() => CustomBlockTypeNames::WHITE_BACKPACK,
				DyeColor::YELLOW() => CustomBlockTypeNames::YELLOW_BACKPACK,
			};
			self::register(str_replace('astreal:', '', $id), (new DyedBackpack(new BlockIdentifier(BlockTypeIds::newId(), TitleBackPack::class), $color->getDisplayName() . ' BackPack', new BlockTypeInfo(new BlockBreakInfo(0.8, BlockToolType::SHEARS))))->setColor($color)->setColorTexture($id));
		}
	}
}
