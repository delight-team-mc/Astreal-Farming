<?php

declare(strict_types=1);

namespace astreal\libraries\vanilla\item;

use astreal\libraries\vanilla\item\ItemComponents;
use pocketmine\block\Block;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use function array_values;

final class ItemFactory
{
	use SingletonTrait {
		getInstance as SgetInstance;
	}

	/** @var ItemTypeEntry[] */
	private array $itemTableEntries = [];
	/** @var ItemComponentPacketEntry[] */
	private array $itemComponentEntries = [];

	public static function getInstance(): ItemFactory
	{
		return self::SgetInstance();
	}

	/**
	 * @return ItemComponentPacketEntry[]
	 */
	public function getItemComponentEntries(): array
	{
		return $this->itemComponentEntries;
	}

	/**
	 * @return ItemTypeEntry[]
	 */
	public function getItemTableEntries(): array
	{
		return array_values($this->itemTableEntries);
	}

	public function registerItem(string $identifier, Item $item, bool $override = false, int $version = 1): void
	{
		$this->registerCustomItemMapping($identifier, $item->getTypeId());
		GlobalItemDataHandlers::getDeserializer()->map($identifier, fn() => clone $item);
		GlobalItemDataHandlers::getSerializer()->map($item, fn(): SavedItemData => new SavedItemData($identifier));
		if ($override) {
			StringToItemParser::getInstance()->override($identifier, fn(): Item => clone $item);
		} else {
			StringToItemParser::getInstance()->register($identifier, fn(): Item => clone $item);
		}
		$componentBased = false;
		if ($item instanceof ItemComponents) $componentBased = true;
		$this->itemTableEntries[$identifier] = new ItemTypeEntry($identifier, $item->getTypeId(), $componentBased, $version, ($componentBased ? new CacheableNbt($item->getComponents()->setInt("id", $item->getTypeId())->setString("name", $identifier)) : new CacheableNbt(CompoundTag::create())));
		CreativeInventory::getInstance()->add($item);
	}

	public function registerCustomItemMapping(string $identifier, int $itemId): void
	{
		$dictionary = TypeConverter::getInstance()->getItemTypeDictionary();
		$reflection = new ReflectionClass($dictionary);
		$intToString = $reflection->getProperty("intToStringIdMap");
		/** @var int[] $value */
		$value = $intToString->getValue($dictionary);
		$intToString->setValue($dictionary, $value + [$itemId => $identifier]);
		$stringToInt = $reflection->getProperty("stringToIntMap");
		/** @var int[] $value */
		$value = $stringToInt->getValue($dictionary);
		$stringToInt->setValue($dictionary, $value + [$identifier => $itemId]);
	}

	public function registerBlockItem(string $identifier, Block $block, int $version = 2): void
	{
		$itemId = $block->getIdInfo()->getBlockTypeId();
		$this->registerCustomItemMapping($identifier, $itemId);
		StringToItemParser::getInstance()->registerBlock($identifier, fn(): Block => clone $block);
		$this->itemTableEntries[] = new ItemTypeEntry($identifier, $itemId, false, $version, new CacheableNbt(CompoundTag::create()));
		$blockItemIdMap = BlockItemIdMap::getInstance();
		$reflection = new ReflectionClass($blockItemIdMap);
		$itemToBlockId = $reflection->getProperty("itemToBlockId");
		/** @var string[] $value */
		$value = $itemToBlockId->getValue($blockItemIdMap);
		$itemToBlockId->setValue($blockItemIdMap, $value + [$identifier => $identifier]);
	}
}
