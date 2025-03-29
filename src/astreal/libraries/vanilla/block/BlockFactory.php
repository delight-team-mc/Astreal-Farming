<?php
declare(strict_types=1);

namespace astreal\libraries\vanilla\block;

use Closure;
use astreal\libraries\vanilla\block\BlockPalette;
use astreal\libraries\vanilla\block\Model;
use astreal\libraries\vanilla\block\permutations\Permutable;
use astreal\libraries\vanilla\block\permutations\Permutation;
use astreal\libraries\vanilla\block\permutations\Permutations;
use astreal\libraries\vanilla\item\CreativeInventoryInfo;
use astreal\libraries\vanilla\item\ItemFactory;
use astreal\libraries\vanilla\task\AsyncRegisterBlocksTask;
use astreal\libraries\vanilla\util\NBT;
use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\inventory\CreativeInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use function array_map;
use function array_reverse;
use function hash;
use function strcmp;
use function usort;

final class BlockFactory{
	use SingletonTrait{
		getInstance as SgetInstance;
	}

	/**
	 * @var Closure[]
	 * @phpstan-var array<string, array{(Closure(int): Block), (Closure(BlockStateWriter): Block), (Closure(Block): BlockStateReader)}>
	 */
	private array $blockFuncs = [];
	/** @var BlockPaletteEntry[] */
	private array $blockPaletteEntries = [];
	/** @var array<string, Block> */
	private array $customBlocks = [];

	public static function getInstance(): BlockFactory{
		return self::SgetInstance();
	}

	public function addWorkerInitHook(string $cachePath): void {
		$server = Server::getInstance();
		$blocks = $this->blockFuncs;
		$server->getAsyncPool()->addWorkerStartHook(static function (int $worker) use ($cachePath, $server, $blocks): void {
			$server->getAsyncPool()->submitTaskToWorker(new AsyncRegisterBlocksTask($cachePath, $blocks), $worker);
		});
	}

	public function get(string $identifier): ?Block {
		return clone (
			$this->customBlocks[$identifier] ??
			null
		);
	}

	/**
	 * @return BlockPaletteEntry[]
	 */
	public function getBlockPaletteEntries(): array {
		return $this->blockPaletteEntries;
	}

	public function registerBlock(Closure $blockFunc, string $identifier, ?Model $model = null, ?CreativeInventoryInfo $creativeInfo = null, ?Closure $serializer = null, ?Closure $deserializer = null): void {
		$block = $blockFunc();
		if(!$block instanceof Block) {
			throw new InvalidArgumentException("Class returned from closure is not a Block");
		}
		RuntimeBlockStateRegistry::getInstance()->register($block);
		ItemFactory::getInstance()->registerBlockItem($identifier, $block);
		$this->customBlocks[$identifier] = $block;
		$propertiesTag = CompoundTag::create();
		$components = CompoundTag::create()->setTag("minecraft:light_emission", CompoundTag::create()->setByte("emission", $block->getLightLevel()))->setTag("minecraft:light_dampening", CompoundTag::create()->setByte("lightLevel", $block->getLightFilter()))->setTag("minecraft:destructible_by_mining", CompoundTag::create()->setFloat("value", $block->getBreakInfo()->getHardness()))->setTag("minecraft:friction", CompoundTag::create()->setFloat("value", 1 - $block->getFrictionFactor()));
		if($model !== null){
			/** @var string $tagName */
			foreach($model->toNBT() as $tagName => $tag){
				$components->setTag($tagName, $tag);
			}
		}
		if($block instanceof Permutable){
			$blockPropertyNames = $blockPropertyValues = $blockProperties = [];
			foreach($block->getBlockProperties() as $blockProperty){
				$blockPropertyNames[] = $blockProperty->getName();
				$blockPropertyValues[] = $blockProperty->getValues();
				$blockProperties[] = $blockProperty->toNBT();
			}
			$permutations = array_map(static fn(Permutation $permutation) => $permutation->toNBT(), $block->getPermutations());
			$components->setTag("minecraft:on_player_placing", CompoundTag::create());
			$propertiesTag->setTag("permutations", new ListTag($permutations))->setTag("properties", new ListTag(array_reverse($blockProperties)));
			foreach(Permutations::getCartesianProduct($blockPropertyValues) as $meta => $permutations){
				$states = CompoundTag::create();
				foreach($permutations as $i => $value)$states->setTag($blockPropertyNames[$i], NBT::getTagType($value));
				$blockState = CompoundTag::create()->setString(BlockStateData::TAG_NAME, $identifier)->setTag(BlockStateData::TAG_STATES, $states);
				BlockPalette::getInstance()->insertState($blockState, $meta);
			}
			$serializer ??= static function (Permutable $block) use ($identifier, $blockPropertyNames) : BlockStateWriter {
				$b = BlockStateWriter::create($identifier);
				$block->serializeState($b);
				return $b;
			};
			$deserializer ??= static function (BlockStateReader $in) use ($block, $identifier, $blockPropertyNames) : Permutable {
				$b = BlockFactory::getInstance()->get($identifier);
				assert($b instanceof Permutable);
				$b->deserializeState($in);
				return $b;
			};
		} else {
			$blockState = CompoundTag::create()->setString(BlockStateData::TAG_NAME, $identifier)->setTag(BlockStateData::TAG_STATES, CompoundTag::create());
			BlockPalette::getInstance()->insertState($blockState);
			$serializer ??= static fn() => new BlockStateWriter($identifier);
			$deserializer ??= static fn(BlockStateReader $in) => $block;
		}
		GlobalBlockStateHandlers::getSerializer()->map($block, $serializer);
		GlobalBlockStateHandlers::getDeserializer()->map($identifier, $deserializer);
		$creativeInfo ??= CreativeInventoryInfo::DEFAULT();
		$components->setTag("minecraft:creative_category", CompoundTag::create()->setString("category", $creativeInfo->getCategory())->setString("group", $creativeInfo->getGroup()));
		$propertiesTag->setTag("components", $components->setTag("minecraft:creative_category", CompoundTag::create()->setString("category", $creativeInfo->getCategory())->setString("group", $creativeInfo->getGroup())))->setTag("menu_category", CompoundTag::create()->setString("category", $creativeInfo->getCategory() ?? "")->setString("group", $creativeInfo->getGroup() ?? ""))->setInt("molangVersion", 1);
		CreativeInventory::getInstance()->add($block->asItem());
		$this->blockPaletteEntries[] = new BlockPaletteEntry($identifier, new CacheableNbt($propertiesTag));
		$this->blockFuncs[$identifier] = [$blockFunc, $serializer, $deserializer];
		usort($this->blockPaletteEntries, static function(BlockPaletteEntry $a, BlockPaletteEntry $b): int {
			return strcmp(hash("fnv164", $a->getName()), hash("fnv164", $b->getName()));
		});
		foreach($this->blockPaletteEntries as $i => $entry){
			$root = $entry->getStates()->getRoot()->setTag("vanilla_block_data", CompoundTag::create()->setInt("block_id", 10000 + $i));
			$this->blockPaletteEntries[$i] = new BlockPaletteEntry($entry->getName(), new CacheableNbt($root));
		}
	}
}