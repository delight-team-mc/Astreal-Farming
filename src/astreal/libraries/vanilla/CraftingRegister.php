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

use pocketmine\crafting\ExactRecipeIngredient;
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
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class CraftingRegister
{
    const SHAPED_TYPE = 'shaped';
    const SHAPELESS_TYPE = 'shapeless';

    public static function load(string $crafting_recipes_path, string $crafting_tags_path): void
    {
        $tags = json_decode(Filesystem::fileGetContents($crafting_tags_path), true);
        if (is_array($tags)) foreach ($tags as $tag_name => $tag_list) {
            if (!is_array($tag_list) or is_string($tag_name)) continue;
            foreach ($tag_list as $tag_id) ItemTagToIdMap::getInstance()->addIdToTag($tag_name, $tag_id);
        }
        $recipes = json_decode(Filesystem::fileGetContents($crafting_recipes_path), true);
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
}
