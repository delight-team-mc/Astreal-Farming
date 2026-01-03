<?php

declare(strict_types=1);

namespace night\libraries\vanilla\item;

use night\libraries\vanilla\item\ItemComponents;
use night\libraries\vanilla\utils\NBT;
use pocketmine\block\Block;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeCategory;
use pocketmine\inventory\CreativeGroup;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use ReflectionClass;
use RuntimeException;

use function array_values;

final class ItemFactory
{
	use SingletonTrait {
		getInstance as SgetInstance;
	}

	/** @var ItemTypeEntry[] */
	private array $itemTableEntries = [];
	private array $groups = [];
	/** @var ItemComponentPacketEntry[] */
	private array $itemComponentEntries = [];

	public static function getInstance(): ItemFactory
	{
		return self::SgetInstance();
	}

	private function loadGroups(): void
	{
		if ($this->groups !== []) {
			return;
		}
		foreach (CreativeInventory::getInstance()->getAllEntries() as $entry) {
			$group = $entry->getGroup();
			if ($group !== null) {
				$this->groups[$group->getName()->getText()] = $group;
			}
		}
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

	public function registerItem(string $identifier, Item $item, bool $override = false, int $version = 1, ?CreativeInventoryInfo $creativeInfo = null): void
	{
		GlobalItemDataHandlers::getDeserializer()->map($identifier, fn() => clone $item);
		GlobalItemDataHandlers::getSerializer()->map($item, fn(): SavedItemData => new SavedItemData($identifier));
		if ($override) {
			StringToItemParser::getInstance()->override($identifier, fn(): Item => clone $item);
		} else {
			StringToItemParser::getInstance()->register($identifier, fn(): Item => clone $item);
		}
		$componentBased = false;
		$componentBased = $item instanceof ItemComponents;
		$nbt = $this->createItemNbt($item, $identifier, $item->getTypeId(), $creativeInfo);

		if ($creativeInfo !== null) {
			$this->loadGroups();
			if ($creativeInfo->getCategory() === CreativeInventoryInfo::CATEGORY_ALL || $creativeInfo->getCategory() === CreativeInventoryInfo::CATEGORY_COMMANDS) {
				return;
			}

			$group = $this->groups[$creativeInfo->getGroup()] ?? ($creativeInfo->getGroup() !== "" && $creativeInfo->getGroup() !== CreativeInventoryInfo::NONE ? new CreativeGroup(
				new Translatable($creativeInfo->getGroup()),
				$item
			) : null);

			if ($group !== null) {
				$this->groups[$group->getName()->getText()] = $group;
			}

			$category = match ($creativeInfo->getCategory()) {
				CreativeInventoryInfo::CATEGORY_CONSTRUCTION => CreativeCategory::CONSTRUCTION,
				CreativeInventoryInfo::CATEGORY_ITEMS => CreativeCategory::ITEMS,
				CreativeInventoryInfo::CATEGORY_NATURE => CreativeCategory::NATURE,
				CreativeInventoryInfo::CATEGORY_EQUIPMENT => CreativeCategory::EQUIPMENT,
				default => throw new AssumptionFailedError("Unknown category")
			};
			CreativeInventory::getInstance()->add($item, $category, $group);
		}
		$this->itemTableEntries[$identifier] = $entry = new ItemTypeEntry($identifier, $item->getTypeId(), $componentBased, $componentBased ? 1 : 0, new CacheableNbt($nbt));
		$this->registerCustomItemMapping($identifier, $item->getTypeId(), $entry);
	}

	private function createItemNbt(Item $item, string $identifier, int $itemId, ?CreativeInventoryInfo $creativeInfo): CompoundTag
	{
		$components = CompoundTag::create();
		$properties = CompoundTag::create();

		if ($item instanceof ItemComponents) {
			foreach ($item->getComponents() as $component) {
				if ($component->isProperty()) {
					$properties->setTag($component->getName(), $component->getValue());
					continue;
				}
				$components->setTag($component->getName(), $component->getValue());
			}
			if ($creativeInfo !== null) {
				$properties->setTag("creative_category", NBT::getTagType($creativeInfo->getNumericCategory()));
				$properties->setTag("creative_group", NBT::getTagType($creativeInfo->getGroup()));
			}
			$components->setTag("item_properties", $properties);
			return CompoundTag::create()
				->setTag("components", $components)
				->setInt("id", $itemId)
				->setString("name", $identifier);
		}
		return CompoundTag::create();
	}

	public function registerCustomItemMapping(string $identifier, int $itemId, ItemTypeEntry $entry): void
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
		$itemTypes = $reflection->getProperty("itemTypes");
		$value = $itemTypes->getValue($dictionary);
		$value[] = $entry;
		$itemTypes->setValue($dictionary, $value);
	}

	public function registerBlockItem(string $identifier, Block $block, int $version = 2): void
	{
		$itemId = $block->getIdInfo()->getBlockTypeId();
		StringToItemParser::getInstance()->registerBlock($identifier, fn(): Block => clone $block);
		$this->itemTableEntries[] = $entry = new ItemTypeEntry($identifier, $itemId, false, $version, new CacheableNbt(CompoundTag::create()));
		$this->registerCustomItemMapping($identifier, $itemId, $entry);
		$blockItemIdMap = BlockItemIdMap::getInstance();
		$reflection = new ReflectionClass($blockItemIdMap);
		$itemToBlockId = $reflection->getProperty("itemToBlockId");
		/** @var string[] $value */
		$value = $itemToBlockId->getValue($blockItemIdMap);
		$itemToBlockId->setValue($blockItemIdMap, $value + [$identifier => $identifier]);
	}
}
