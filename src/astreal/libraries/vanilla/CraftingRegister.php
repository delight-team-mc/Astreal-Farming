<?php
/*
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗  ██████╗  █████╗ ██╗  ██╗██████╗ ██╗   ██╗██████╗ 
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝  ██╔══██╗██╔══██╗╚██╗██╔╝██╔══██╗██║   ██║██╔══██╗
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║     ██████╦╝██║  ██║ ╚███╔╝ ██████╔╝╚██╗ ██╔╝██████╔╝
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║     ██╔══██╗██║  ██║ ██╔██╗ ██╔═══╝  ╚████╔╝ ██╔═══╝ 
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║     ██████╦╝╚█████╔╝██╔╝╚██╗██║       ╚██╔╝  ██║     
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝     ╚═════╝  ╚════╝ ╚═╝  ╚═╝╚═╝        ╚═╝   ╚═╝     
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 */

namespace astreal\libraries\vanilla;

use Exception;
use GlobalLogger;
use JsonSerializable;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\crafting\json\RecipeIngredientData;
use pocketmine\crafting\MetaWildcardRecipeIngredient;
use pocketmine\crafting\RecipeIngredient;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\crafting\ShapelessRecipeType;
use pocketmine\crafting\TagWildcardRecipeIngredient;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\bedrock\ItemTagToIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\recipe\CraftingRecipeBlockName;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipeBlockName;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class CraftingRegister
{

    const SHAPED_TYPE = 'shaped';
    const SHAPELESS_TYPE = 'shapeless';

    public static function load(string $one_file_crafting_recipes_path, string $crafting_tags_path, string $recipes_folder): void
    {
        $tags = json_decode(Filesystem::fileGetContents($crafting_tags_path), true);
        if (is_array($tags)) foreach ($tags as $tag_name => $tag_list) {
            if (!is_array($tag_list) or is_string($tag_name)) continue;
            foreach ($tag_list as $tag_id) ItemTagToIdMap::getInstance()->addIdToTag($tag_name, $tag_id);
        }
        $recipes = json_decode(Filesystem::fileGetContents($one_file_crafting_recipes_path), true);
        if (!is_array($recipes)) return;
        $shaped_list = array_filter($recipes, fn(array $data): bool => $data['type'] === self::SHAPED_TYPE);
        $shapeless_list = array_filter($recipes, fn(array $data): bool => $data['type'] === self::SHAPELESS_TYPE);
        foreach ($shapeless_list as $recipe) {
            $type = match ($recipe['block']) {
                'crafting_table' => ShapelessRecipeType::CRAFTING,
                'stonecutter' => ShapelessRecipeType::STONECUTTER,
                'smithing_table' => ShapelessRecipeType::SMITHING,
                'cartography_table' => ShapelessRecipeType::CARTOGRAPHY,
                default => null
            };
            if ($type === null) continue;
            $inputs = [];
            foreach ($recipe['input'] as $input_data) {
                $input = self::deserializeIngredient($input_data);
                if ($input === null) continue 2;
                $inputs[] = $input;
            }
            $outputs = [];
            foreach ($recipe['output'] as $output_data) {
                $output = self::deserializeItemStackFromFields($output_data['name'], $output_data['meta'] ?? null, $output_data['count'] ?? null, $output_data['block_states'] ?? null, $output_data['nbt'] ?? null, $output_data['can_place_on'] ?? [], $output_data['can_destroy'] ?? []);
                if ($output === null) continue 2;
                $outputs[] = $output;
            }
            Server::getInstance()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe($inputs, $outputs, $type));
        }
        foreach ($shaped_list as $recipe) {
            if ($recipe['block'] !== 'crafting_table') continue;
            $inputs = [];
            foreach (Utils::stringifyKeys($recipe['input']) as $symbol => $input_data) {
                $input = self::deserializeIngredient($input_data);
                if ($input === null) continue 2;
                $inputs[$symbol] = $input;
            }
            $outputs = [];
            foreach ($recipe['output'] as  $output_data) {
                $output = self::deserializeItemStackFromFields($output_data['name'], $output_data['meta'] ?? null, $output_data['count'] ?? null, $output_data['block_states'] ?? null, $output_data['nbt'] ?? null, $output_data['can_place_on'] ?? [], $output_data['can_destroy'] ?? []);
                if ($output === null) continue 2;
                $outputs[] = $output;
            }
            Server::getInstance()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe($recipe['shape'], $inputs, $outputs));
        }
        self::load_recipes_by_folder($recipes_folder);
    }

    private static function load_recipes_by_folder(string $folder): void
    {
        try {
            if (!is_dir($folder)) @mkdir($folder);
            if (is_dir($folder)) foreach (array_filter(self::getFilesByPath($folder), fn(SplFileInfo $file): bool => $file->getExtension() === 'json') as $file) {
                $recipe_data = new RecipeData(json_decode(Filesystem::fileGetContents($file->getPathname()), true, flags: JSON_THROW_ON_ERROR));
                switch ($recipe_data->getRecipeType()) {
                    case RecipeData::SHAPED_TYPE:
                        try {
                            $ingredients = $results = [];
                            foreach ($recipe_data->getKeys() as $symbol => $item_data) {
                                if (isset($item_data['item'])) $ingredients[$symbol] = new ExactRecipeIngredient(self::deserializeItemStackFromVanillaRecipe($item_data['item']));
                                elseif (isset($item_data['tag'])) $ingredients[$symbol] = new TagWildcardRecipeIngredient($item_data['tag']);
                            }
                            if (!isset($recipe_data->getResults()['item'])) foreach ($recipe_data->getResults()['result'] as $item_data) $results[] = self::deserializeItemStackFromVanillaRecipe($item_data['item'], $item_data['count'] ?? 1);
                            else $results[] = self::deserializeItemStackFromVanillaRecipe($recipe_data->getResults()['item'], $recipe_data->getResults()['count'] ?? 1);
                            $shaped_recipe = new ShapedRecipe($recipe_data->getPattern(), $ingredients, $results);
                            Server::getInstance()->getCraftingManager()->registerShapedRecipe($shaped_recipe);
                        } catch (\Throwable $th) {
                            \GlobalLogger::get()->error("Error processing data from " . $recipe_data->getIdentifier() . ": " . $th->getMessage());
                        }
                        break;
                    case RecipeData::SHAPELESS_TYPE:
                        try {
                            $ingredients = $results = [];
                            foreach ($recipe_data->getIngredients() as $item_data) if (isset($item_data['item'])) $ingredients[] = new ExactRecipeIngredient(self::deserializeItemStackFromVanillaRecipe($item_data['item']));
                            if (!isset($recipe_data->getResults()['item'])) foreach ($recipe_data->getResults() as $item_data) $results[] = self::deserializeIngredient($item_data['item'], $item_data['count'] ?? 1);
                            else $results[] = self::deserializeItemStackFromVanillaRecipe($recipe_data->getResults()['item'], $recipe_data->getResults()['count'] ?? 1);
                            foreach ($recipe_data->getTags() as $tag) if ($tag !== null) Server::getInstance()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe($ingredients, $results, $tag));
                        } catch (\Throwable $th) {
                            \GlobalLogger::get()->error("Error processing data from " . $recipe_data->getIdentifier() . ": " . $th->getMessage());
                        }
                        break;
                    case RecipeData::FURNACE_TYPE:
                        try {
                            $input = new ExactRecipeIngredient(self::deserializeItemStackFromVanillaRecipe($recipe_data->getFurnaceInput()));
                            $output = self::deserializeItemStackFromVanillaRecipe($recipe_data->getFurnaceOutput());
                            foreach ($recipe_data->getTags() as $tag) if ($tag !== null) Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager($tag)->register(new FurnaceRecipe($output, $input));
                        } catch (\Throwable $th) {
                            \GlobalLogger::get()->error("Error processing data from " . $recipe_data->getIdentifier() . ": " . $th->getMessage());
                        }
                        break;
                }
            }
        } catch (\Throwable $th) {
            GlobalLogger::get()->error($th->getMessage());
        }
    }

    private static function deserializeItemStackFromVanillaRecipe(string $itemName, int $count = 1): Item
    {
        return GlobalItemDataHandlers::getDeserializer()->deserializeStack(
            new SavedItemStackData(
                new SavedItemData(
                    $itemName,
                    block: self::getBlockState($itemName)
                ),
                $count,
                null,
                null,
                [],
                []
            )
        );
    }

    private static function getBlockState(string $itemName): ?BlockStateData
    {
        $blockName = BlockItemIdMap::getInstance()->lookupBlockId($itemName);
        if ($blockName === null) return null;
        foreach (TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->getStates() as $entry) if ($entry->getStateName() == $blockName) return $entry->generateStateData();
        return null;
    }

    private static function deserializeIngredient(array $data): ?RecipeIngredient
    {
        if (isset($data['count']) && $data['count'] !== 1) throw new SavedDataLoadingException('Recipe inputs should have a count of exactly 1');
        if (isset($data['tag'])) return new TagWildcardRecipeIngredient($data['tag']);
        $meta = $data['meta'] ?? null;
        if ($meta === RecipeIngredientData::WILDCARD_META_VALUE) return new MetaWildcardRecipeIngredient($data['name']);
        $itemStack = self::deserializeItemStackFromFields(
            $data['name'],
            $meta,
            $data['count'] ?? null,
            $data['block_states'] ?? null,
            null,
            [],
            []
        );
        if ($itemStack === null) return null;
        return new ExactRecipeIngredient($itemStack);
    }

    /**
     * @param string[] $canPlaceOn
     * @param string[] $canDestroy
     */
    private static function deserializeItemStackFromFields(string $name, ?int $meta, ?int $count, ?string $blockStatesRaw, ?string $nbtRaw, array $canPlaceOn, array $canDestroy): ?item
    {
        $meta ??= 0;
        $count ??= 1;
        $blockName = BlockItemIdMap::getInstance()->lookupBlockId($name);
        if ($blockName !== null) {
            if ($meta !== 0) {
                throw new SavedDataLoadingException('Meta should not be specified for blockitems');
            }
            $blockStatesTag = $blockStatesRaw === null ?
                [] : (new LittleEndianNbtSerializer())
                ->read(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => base64_decode($blockStatesRaw, true)))
                ->mustGetCompoundTag()
                ->getValue();
            $blockStateData = BlockStateData::current($blockName, $blockStatesTag);
        } else {
            $blockStateData = null;
        }
        $nbt = $nbtRaw === null ? null : (new LittleEndianNbtSerializer())
            ->read(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => base64_decode($nbtRaw, true)))
            ->mustGetCompoundTag();
        $itemStackData = new SavedItemStackData(
            new SavedItemData(
                $name,
                $meta,
                $blockStateData,
                $nbt
            ),
            $count,
            null,
            null,
            $canPlaceOn,
            $canDestroy,
        );
        try {
            return GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStackData);
        } catch (ItemTypeDeserializeException) {
            return null;
        }
    }

    /**
     * @return SplFileInfo[]
     */
    private static function getFilesByPath(string $path): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) if ($file->isFile()) $files[] = $file;
        return $files;
    }
}
class RecipeData implements JsonSerializable
{
    const SHAPED_TYPE = 'minecraft:recipe_shaped';
    const SHAPELESS_TYPE = 'minecraft:recipe_shapeless';
    const FURNACE_TYPE = 'minecraft:recipe_furnace';
    const BREWING_MIX_TYPE = 'minecraft:recipe_brewing_mix';
    const BEWWING_CONTAINER_TYPE = 'minecraft:recipe_brewing_container';

    private string $format_version, $recipe_type, $furnace_input, $furnace_output;
    private array $tags, $pattern, $keys, $ingredients, $results;

    public function __construct(private array $data)
    {
        $this->format_version = $data['format_version'];
        if (isset($data[self::SHAPED_TYPE])) {
            $this->recipe_type = self::SHAPED_TYPE;
            $this->pattern = $data[self::SHAPED_TYPE]['pattern'];
            $this->keys = $data[self::SHAPED_TYPE]['key'];
            $this->results = $data[self::SHAPED_TYPE]['result'];
            $this->tags = $data[self::SHAPED_TYPE]['tags'];
        } elseif (isset($data[self::SHAPELESS_TYPE])) {
            $this->recipe_type = self::SHAPELESS_TYPE;
            $this->ingredients = $data[self::SHAPELESS_TYPE]['ingredients'];
            $this->results = $data[self::SHAPELESS_TYPE]['result'];
            $this->tags = array_map(fn(string $tag) => match ($tag) {
                CraftingRecipeBlockName::STONECUTTER => ShapelessRecipeType::STONECUTTER(),
                CraftingRecipeBlockName::CARTOGRAPHY_TABLE => ShapelessRecipeType::CARTOGRAPHY(),
                CraftingRecipeBlockName::CRAFTING_TABLE => ShapelessRecipeType::CRAFTING(),
                CraftingRecipeBlockName::SMITHING_TABLE => ShapelessRecipeType::SMITHING(),
                default => null
            }, $data[self::SHAPELESS_TYPE]['tags']);
        } elseif (isset($data[self::FURNACE_TYPE])) {
            $this->recipe_type = self::FURNACE_TYPE;
            $this->furnace_input = $data[self::FURNACE_TYPE]['input'];
            $this->furnace_output = $data[self::FURNACE_TYPE]['output'];
            $this->tags = array_map(fn(string $tag) => match ($tag) {
                FurnaceRecipeBlockName::SMOKER => FurnaceType::SMOKER(),
                FurnaceRecipeBlockName::FURNACE => FurnaceType::FURNACE(),
                FurnaceRecipeBlockName::CAMPFIRE => FurnaceType::CAMPFIRE(),
                FurnaceRecipeBlockName::BLAST_FURNACE => FurnaceType::BLAST_FURNACE(),
                FurnaceRecipeBlockName::SOUL_CAMPFIRE => FurnaceType::SOUL_CAMPFIRE(),
                default => null
            }, $data[self::FURNACE_TYPE]['tags']);
        } else throw new RecipeException("Unsupported recipe type");
    }

    public function getFormatVersion(): string
    {
        return $this->format_version;
    }

    public function getIdentifier(): string
    {
        return $this->data[$this->recipe_type]['description']['identifier'] ?? '???';;
    }

    public function getTags(): array
    {
        return $this->tags ?? [];
    }

    public function getPattern(): array
    {
        return $this->pattern ?? [];
    }

    public function getKeys(): array
    {
        return $this->keys ?? [];
    }

    public function getIngredients(): array
    {
        return $this->ingredients ?? [];
    }

    public function getFurnaceInput(): string
    {
        return $this->furnace_input;
    }

    public function getFurnaceOutput(): string
    {
        return $this->furnace_output;
    }

    public function getResults(): array
    {
        return $this->results ?? [];
    }

    public function getRecipeType(): string
    {
        return $this->recipe_type;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
class RecipeException extends Exception {}
