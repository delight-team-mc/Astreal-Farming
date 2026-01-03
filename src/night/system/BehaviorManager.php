<?php

namespace night\system;

use night\libraries\vanilla\block\BlockFactory;
use night\libraries\vanilla\block\component\BlockComponent;
use night\libraries\vanilla\block\component\BreathabilityComponent;
use night\libraries\vanilla\block\component\CollisionBoxComponent;
use night\libraries\vanilla\block\component\DestructibleByExplosionComponent;
use night\libraries\vanilla\block\component\DestructibleByMiningComponent;
use night\libraries\vanilla\block\component\DisplayNameComponent as BlockDisplayNameComponent;
use night\libraries\vanilla\block\component\FlammableComponent;
use night\libraries\vanilla\block\component\FrictionComponent;
use night\libraries\vanilla\block\component\GeometryComponent;
use night\libraries\vanilla\block\component\LightDampeningComponent;
use night\libraries\vanilla\block\component\LightEmissionComponent;
use night\libraries\vanilla\block\component\LiquidDetectionComponent;
use night\libraries\vanilla\block\component\MaterialInstancesComponent;
use night\libraries\vanilla\block\component\SelectionBoxComponent;
use night\libraries\vanilla\block\component\TagsComponent;
use night\libraries\vanilla\block\Material;
use night\libraries\vanilla\block\properties\Box;
use night\libraries\vanilla\item\component\AllowOffHandComponent;
use night\libraries\vanilla\item\component\BlockPlacerComponent;
use night\libraries\vanilla\item\component\BundleInteractionComponent;
use night\libraries\vanilla\item\component\CanDestroyInCreativeComponent;
use night\libraries\vanilla\item\component\CooldownComponent;
use night\libraries\vanilla\item\component\DamageAbsorptionComponent;
use night\libraries\vanilla\item\component\DamageComponent;
use night\libraries\vanilla\item\component\DiggerComponent;
use night\libraries\vanilla\item\component\DisplayNameComponent as ItemDisplayNameComponent;
use night\libraries\vanilla\item\component\DurabilityComponent;
use night\libraries\vanilla\item\component\DyeableComponent;
use night\libraries\vanilla\item\component\EnchantableSlotComponent;
use night\libraries\vanilla\item\component\EnchantableValueComponent;
use night\libraries\vanilla\item\component\EntityPlacerComponent;
use night\libraries\vanilla\item\component\FoilComponent;
use night\libraries\vanilla\item\component\FoodComponent;
use night\libraries\vanilla\item\component\FuelComponent;
use night\libraries\vanilla\item\component\GlintComponent;
use night\libraries\vanilla\item\component\HandEquippedComponent;
use night\libraries\vanilla\item\component\HoverTextColorComponent;
use night\libraries\vanilla\item\component\IconComponent;
use night\libraries\vanilla\item\component\InteractButtonComponent;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\component\LiquidClippedComponent;
use night\libraries\vanilla\item\component\MaxStackSizeComponent;
use night\libraries\vanilla\item\component\ProjectileComponent;
use night\libraries\vanilla\item\component\RarityComponent;
use night\libraries\vanilla\item\component\RecordComponent;
use night\libraries\vanilla\item\component\RepairableComponent;
use night\libraries\vanilla\item\component\ShooterComponent;
use night\libraries\vanilla\item\component\ShouldDespawnComponent;
use night\libraries\vanilla\item\component\StackedByDataComponent;
use night\libraries\vanilla\item\component\StorageItemComponent;
use night\libraries\vanilla\item\component\StorageWeightLimitComponent;
use night\libraries\vanilla\item\component\StorageWeightModifierComponent;
use night\libraries\vanilla\item\component\ThrowableComponent;
use night\libraries\vanilla\item\component\UseAnimationComponent;
use night\libraries\vanilla\item\component\UseDurationComponent;
use night\libraries\vanilla\item\component\UseModifiersComponent;
use night\libraries\vanilla\item\component\WearableComponent;
use night\libraries\vanilla\item\CreativeInventoryInfo;
use night\libraries\vanilla\item\ItemFactory;
use night\system\behavior\Block;
use night\system\behavior\BlockBrushCropSimple;
use night\system\behavior\BlockCropSimple;
use night\system\behavior\BlockPermutable;
use night\system\behavior\BlockSimple;
use night\system\behavior\Item;
use night\system\behavior\ItemArmorSimple;
use night\system\behavior\ItemDurableSimple;
use night\system\behavior\ItemFoodSimple;
use night\system\behavior\ItemProjectileSimple;
use night\system\behavior\ItemSimple;
use night\system\behavior\ItemToolSimple;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorMaterial;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\item\ToolTier;
use pocketmine\math\Vector3;
use pocketmine\world\sound\ArmorEquipGenericSound;
use PrefixedLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class BehaviorManager
{
    /**
     * @var BlockComponent[] $b_c
     * @var ItemComponent[] $i_c
     */
    public static array $b_c = [], $i_c = [];
    private static bool $initiate = false;

    /** @return BlockComponent[] */
    public static function getBlockComponents(): array
    {
        if (!self::$initiate) self::initiate();
        return self::$b_c;
    }

    public static function initiate(): void
    {
        self::$initiate = true;
        self::$b_c['minecraft:breathability'] = fn(mixed $component): BlockComponent => new BreathabilityComponent($component);
        self::$b_c['minecraft:collision_box'] = function (mixed $component): BlockComponent {
            if (is_bool($component)) return (new CollisionBoxComponent($component))->addBox(new Box(new Vector3(-8, 0, -8), new Vector3(16, 16, 16)));
            $component_ = new CollisionBoxComponent(true);
            $boxes = [];
            if (is_array($component) && isset($component[0])) {
                foreach ($component as $box) {
                    $origin = $box['origin'] ?? [-8, 0, -8];
                    $size = $box['size'] ?? [16, 24, 16];
                    $boxes[] = new Box(new Vector3($origin[0], $origin[1], $origin[2]), new Vector3($size[0], $size[1], $size[2]));
                }
                return $component_->addBoxes($boxes);
            }
            if (is_array($component) && isset($component['origin'])) {
                $origin = $component['origin'];
                $size = $component['size'] ?? [16, 24, 16];
                return $component_->addBox(new Box(new Vector3($origin[0], $origin[1], $origin[2]), new Vector3($size[0], $size[1], $size[2])));
            }
            if (empty($boxes)) $component_->addBox(new Box(new Vector3(-8, 0, -8), new Vector3(16, 16, 16)));
            return $component_;
        };
        self::$b_c['minecraft:destructible_by_explosion'] = fn(mixed $component): BlockComponent => new DestructibleByExplosionComponent($component['explosion_resistance']);
        self::$b_c['minecraft:destructible_by_mining'] = fn(mixed $component): BlockComponent => new DestructibleByMiningComponent($component['seconds_to_destroy']);
        self::$b_c['minecraft:display_name'] = fn(mixed $component): BlockComponent => new BlockDisplayNameComponent($component);
        self::$b_c['minecraft:flammable'] = function (mixed $component): BlockComponent {
            if (is_bool($component)) return new FlammableComponent($component ? 5 : 0, $component ? 20 : 0);
            return new FlammableComponent($component['catch_chance_modifier'] ?? 5, $component['destroy_chance_modifier'] ?? 20);
        };
        self::$b_c['minecraft:friction'] = fn(mixed $component): BlockComponent => new FrictionComponent($component);
        self::$b_c['minecraft:geometry'] = function (mixed $component): BlockComponent {
            if (is_string($component)) return new GeometryComponent($component);
            return new GeometryComponent($component['identifier']);
        };
        self::$b_c['minecraft:light_dampening'] = fn(mixed $component): BlockComponent => new LightEmissionComponent($component);
        self::$b_c['minecraft:light_emission'] = fn(mixed $component): BlockComponent => new LightDampeningComponent($component);
        self::$b_c['minecraft:liquid_detection'] = fn(mixed $component): BlockComponent => new LiquidDetectionComponent($component['detection_rules']);
        self::$b_c['minecraft:material_instances'] = fn(mixed $component): BlockComponent => new MaterialInstancesComponent(\array_map(fn(string $target, array $values): Material => Material::create($target, $values['texture'], $values['render_method'], $values['face_dimming'] ?? true, $values['ambient_occlusion'] ?? true), \array_keys($component), \array_values($component)));
        self::$b_c['minecraft:selection_box'] = function (mixed $component): BlockComponent {
            if (is_bool($component)) return new SelectionBoxComponent($component);
            $size = $component['size'] ?? [16, 24, 16];
            return new SelectionBoxComponent(true, new Vector3($component['origin'][0], $component['origin'][1], $component['origin'][2]), new Vector3($size[0], $size[1], $size[2]));
        };

        self::$i_c['minecraft:allow_off_hand'] = function (mixed $component): ItemComponent {
            if (is_bool($component)) return new AllowOffHandComponent($component);
            return new AllowOffHandComponent($component['value']);
        };
        self::$i_c['minecraft:block_placer'] = fn(mixed $component): ItemComponent => new BlockPlacerComponent(($item = StringToItemParser::getInstance()->parse($component['block'])) instanceof ItemBlock ? $item->getBlock() : VanillaBlocks::AIR(), $component['use_on']);
        self::$i_c['minecraft:bundle_interaction'] = fn(mixed $component): ItemComponent => new BundleInteractionComponent($component['num_viewable_slots']);
        self::$i_c['minecraft:can_destroy_in_creative'] = fn(mixed $component): ItemComponent => new CanDestroyInCreativeComponent($component);
        self::$i_c['minecraft:cooldown'] = fn(mixed $component): ItemComponent => new CooldownComponent($component['category'], $component['duration']);
        //self::$i_c['minecraft:compostable'] = fn(mixed $component): ItemComponent => new ();
        self::$i_c['minecraft:damage_absorption'] = fn(mixed $component): ItemComponent => new DamageAbsorptionComponent($component['absorbable_causes']);
        self::$i_c['minecraft:damage'] = function (mixed $component): ItemComponent {
            if (is_numeric($component)) return new DamageComponent($component);
            return new DamageComponent($component['value']);
        };
        self::$i_c['minecraft:digger'] = fn(mixed $component): ItemComponent => new DiggerComponent($component['use_efficiency'], $component['destroy_speeds']);
        self::$i_c['minecraft:display_name'] = fn(mixed $component): ItemComponent => new ItemDisplayNameComponent($component['value']);
        self::$i_c['minecraft:durability'] = fn(mixed $component): ItemComponent => new DurabilityComponent($component['max_durability'], $component['damage_chance']['min'], $component['damage_chance']['max']);
        self::$i_c['minecraft:dyeable'] = fn(mixed $component): ItemComponent => new DyeableComponent($component['default_color']);
        self::$i_c['minecraft:enchantable'] = fn(mixed $component): array => [new EnchantableSlotComponent($component['slot']), new EnchantableValueComponent($component['value'])];
        self::$i_c['minecraft:entity_placer'] = fn(mixed $component): ItemComponent => new EntityPlacerComponent($component['entity'], ($component['use_on'] ?? []), ($component['dispense_on'] ?? []));
        self::$i_c['minecraft:food'] = fn(mixed $component): ItemComponent => new FoodComponent($component['can_always_eat'], $component['nutrition'], $component['saturation_modifier'], $component['using_converts_to'] ?? '');
        self::$i_c['minecraft:fuel'] = fn(mixed $component): ItemComponent => new FuelComponent($component['duration']);
        self::$i_c['minecraft:foil'] = fn(mixed $component): ItemComponent => new FoilComponent($component);
        self::$i_c['minecraft:glint'] = function (mixed $component): ItemComponent {
            if (is_bool($component)) return new GlintComponent($component);
            return new GlintComponent($component['value']);
        };
        self::$i_c['minecraft:hand_equipped'] = function (mixed $component): ItemComponent {
            if (is_bool($component)) return new HandEquippedComponent($component);
            return new HandEquippedComponent($component['value']);
        };
        self::$i_c['minecraft:hover_text_color'] = fn(mixed $component): ItemComponent => new HoverTextColorComponent(match ($component) {
            "black" => "§0",
            "dark_blue" => "§1",
            "dark_green" => "§2",
            "dark_aqua" => "§3",
            "dark_red" => "§4",
            "dark_purple" => "§5",
            "gold" => "§6",
            "gray" => "§7",
            "dark_gray" => "§8",
            "blue" => "§9",
            "green" => "§a",
            "aqua" => "§b",
            "red" => "§c",
            "light_purple" => "§d",
            "yellow" => "§e",
            "white" => "§f",
            "minecoin_gold" => "§g",
            "material_quartz" => "§h",
            "material_iron" => "§i",
            "material_netherite" => "§j",
            "material_redstone" => "§m",
            "material_copper" => "§n",
            "material_gold" => "§p",
            "material_emerald" => "§q",
            "material_diamond" => "§s",
            "material_lapis" => "§t",
            "material_amethyst" => "§u",
            default => $component
        });
        self::$i_c['minecraft:icon'] = function (mixed $component): ItemComponent {
            if (is_string($component)) return new IconComponent($component);
            return new IconComponent($component['textures']['default'], $component['textures']['dyed'] ?? '', $component['textures']['icon_trim'] ?? '');
        };
        self::$i_c['minecraft:interact_button'] = fn(mixed $component): ItemComponent => new InteractButtonComponent($component);
        self::$i_c['minecraft:liquid_clipped'] = function (mixed $component): ItemComponent {
            if (is_bool($component)) return new LiquidClippedComponent($component);
            return new LiquidClippedComponent($component['value']);
        };
        self::$i_c['minecraft:max_stack_size'] = function (mixed $component): ItemComponent {
            if (is_numeric($component)) return new MaxStackSizeComponent($component);
            return new MaxStackSizeComponent($component['value']);
        };
        self::$i_c['minecraft:projectile'] = fn(mixed $component): ItemComponent => new ProjectileComponent($component['minimum_critical_power'], $component['projectile_entity']);
        self::$i_c['minecraft:rarity'] = function (mixed $component): ItemComponent {
            if (is_string($component)) return new DamageComponent($component);
            return new DamageComponent($component['value']);
        };;
        self::$i_c['minecraft:record'] = fn(mixed $component): ItemComponent => new RecordComponent($component['comparator_signal'], $component['duration'], $component['sound_event']);
        self::$i_c['minecraft:repairable'] = fn(mixed $component): ItemComponent => new RepairableComponent($component['repair_items']);
        self::$i_c['minecraft:shooter'] = fn(mixed $component): ItemComponent => new ShooterComponent($component['ammunition'], $component['charge_on_draw'], $component['max_draw_duration'], $component['scale_power_by_draw_duration']);
        self::$i_c['minecraft:should_despawn'] = fn(mixed $component): ItemComponent => new ShouldDespawnComponent($component);
        self::$i_c['minecraft:stacked_by_data'] = fn(mixed $component): ItemComponent => new StackedByDataComponent($component);
        self::$i_c['minecraft:storage_item'] = fn(mixed $component): ItemComponent => new StorageItemComponent($component['allow_nested_storage_items'], $component['max_slots'], $component['max_weight_limit'] ?? 64, $component['allowed_items'] ?? [], $component['banned_items'] ?? []);
        self::$i_c['minecraft:storage_weight_limit'] = fn(mixed $component): ItemComponent => new StorageWeightLimitComponent($component['max_weight_limit']);
        self::$i_c['minecraft:storage_weight_modifier'] = fn(mixed $component): ItemComponent => new StorageWeightModifierComponent($component['weight_in_storage_item']);
        self::$i_c['minecraft:throwable'] = fn(mixed $component): ItemComponent => new ThrowableComponent($component['do_swing_animation'], $component['launch_power_scale'], $component['max_draw_duration'], $component['max_launch_power'], $component['min_draw_duration'], $component['scale_power_by_draw_duration']);
        self::$i_c['minecraft:use_duration'] = fn(mixed $component): ItemComponent => new UseDurationComponent($component);
        self::$i_c['minecraft:use_animation'] = fn(mixed $component): ItemComponent => new UseAnimationComponent($component);
        self::$i_c['minecraft:use_modifiers'] = fn(mixed $component): ItemComponent => new UseModifiersComponent($component['movement_modifier'], $component['use_duration']);
        self::$i_c['minecraft:wearable'] = fn(mixed $component): ItemComponent => new WearableComponent($component['slot'], $component['protection'], $component['dispensable']);
    }

    public static function load(string $path): void
    {
        $logger = new PrefixedLogger(\GlobalLogger::get(), 'Behavior');
        if (!self::$initiate) self::initiate();
        /** @var SplFileInfo */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $raw_file) {
            if ($raw_file->isDir()) continue;
            if (strtolower($raw_file->getExtension()) !== "json") continue;
            try {
                $json = json_decode(file_get_contents($raw_file->getPathname()), true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $logger->error("invalid json data, path={$raw_file->getPathname()},error={$e->getMessage()}");
                continue;
            }
            if (isset($json['minecraft:item'])) {
                $file = new Item($raw_file->getPathname());
            } elseif (isset($json['minecraft:block'])) {
                $file = new Block($raw_file->getPathname());
            } else continue;
            if ($file instanceof Item) {
                foreach (\array_map(fn(string $component): ItemComponent|array => self::$i_c[$component]($file->getRawComponent($component)), \array_filter(\array_keys($file->getRawComponents()), fn(string $component): bool => isset(self::$i_c[$component]))) as $component) {
                    if (is_array($component)) foreach ($component as $c) $file->addComponent($c);
                    else $file->addComponent($component);
                }
                $creativeInfo = count($file->getMenuCategory()) > 0 ? CreativeInventoryInfo::create($file->getMenuCategory()['category'], $file->getMenuCategory()['group']) : null;
                $class = match (true) {
                    $file->hasRawComponent('minecraft:wearable') && \in_array($file->getRawComponentNested('minecraft:wearable.slot', ''), [WearableComponent::SLOT_ARMOR_HEAD, WearableComponent::SLOT_ARMOR_CHEST, WearableComponent::SLOT_ARMOR_LEGS, WearableComponent::SLOT_ARMOR_FEET]) => ItemArmorSimple::class,
                    $file->hasRawComponent('minecraft:digger') => ItemToolSimple::class,
                    $file->hasRawComponent('minecraft:durability') => ItemDurableSimple::class,
                    $file->hasRawComponent('minecraft:food') => ItemFoodSimple::class,
                    $file->hasRawComponent('minecraft:projectile') => ItemProjectileSimple::class,
                    default => ItemSimple::class
                };
                $identifier = new ItemIdentifier(ItemTypeIds::newId());
                $name = $file->getRawComponentNested('minecraft:display_name.value', str_replace('_', ' ', explode(':', $file->getIdentifier())[1]));
                if ($class === ItemArmorSimple::class) {
                    $item = new $class($identifier, $name, new ArmorTypeInfo($file->getRawComponentNested('minecraft:wearable.protection', 1), $file->getRawComponentNested('minecraft:durability.max_durability', 64), match ($file->getRawComponentNested('minecraft:wearable.slot', '')) {
                        WearableComponent::SLOT_ARMOR_HEAD => ArmorInventory::SLOT_HEAD,
                        WearableComponent::SLOT_ARMOR_CHEST => ArmorInventory::SLOT_CHEST,
                        WearableComponent::SLOT_ARMOR_LEGS => ArmorInventory::SLOT_LEGS,
                        WearableComponent::SLOT_ARMOR_FEET => ArmorInventory::SLOT_FEET,
                        default => ($file->getPocketmineProperties('item:armor')['slot'] ?? 5)
                    }, ($file->getPocketmineProperties('item:armor')['toughness'] ?? 0), ($file->getPocketmineProperties('item:armor')['fireProof'] ?? false), new ArmorMaterial($file->getRawComponent('minecraft:enchantable.value', 9), new ArmorEquipGenericSound())));
                } else if ($class === ItemToolSimple::class) {
                    $tier = match (\strtolower($file->getPocketmineProperties('item:tool_tier')['tier'] ?? 'wood')) {
                        'wood' => ToolTier::WOOD(),
                        'gold' => ToolTier::GOLD(),
                        'stone' => ToolTier::STONE(),
                        'iron' => ToolTier::IRON(),
                        'diamond' => ToolTier::DIAMOND(),
                        'netherite' => ToolTier::NETHERITE(),
                        default => ToolTier::WOOD()
                    };
                    $item = new $class($identifier, $name, $tier);
                } else $item = new $class($identifier, $name);
                $item->setBehavior($file);
                ItemFactory::getInstance()->registerItem($file->getIdentifier(), $item, creativeInfo: $creativeInfo);
            } elseif ($file instanceof Block) {
                $creativeInfo = count($file->getMenuCategory()) > 0 ? CreativeInventoryInfo::create($file->getMenuCategory()['category'], $file->getMenuCategory()['group']) : null;
                $file_path = $raw_file->getPathname();
                BlockFactory::getInstance()->registerBlock(static function () use ($file_path) {
                    $file = new Block($file_path);
                    foreach (\array_map(fn(string $component): BlockComponent => self::getBlockComponents()[$component]($file->getRawComponent($component)), \array_filter(\array_keys($file->getRawComponents()), fn(string $component): bool => isset(self::getBlockComponents()[$component]))) as $component) $file->addComponent($component);
                    $tags = array_filter(array_keys($file->getRawComponents()), fn(string $c) => str_starts_with($c, "tag:"));
                    if (count($tags) > 0) $file->addComponent(new TagsComponent($tags));
                    $d_b_m_component = $file->getRawComponent('minecraft:destructible_by_mining');
                    $identifier = new BlockIdentifier(BlockTypeIds::newId());
                    $d_b_m_component = $file->getRawComponent('minecraft:destructible_by_mining');
                    $seconds = $d_b_m_component['seconds_to_destroy'] ?? 0.0;
                    $explosion = $file->getRawComponent('minecraft:destructible_by_explosion');
                    $blastResistance = $explosion['explosion_resistance'] ?? 0.0;
                    $tool_tier = match (\strtolower($file->getPocketmineComponentNested('block:tool.tier', ''))) {
                        'wood' => ToolTier::WOOD()->getHarvestLevel(),
                        'stone' => ToolTier::STONE()->getHarvestLevel(),
                        'gold' => ToolTier::GOLD()->getHarvestLevel(),
                        'iron' => ToolTier::IRON()->getHarvestLevel(),
                        'diamond' => ToolTier::DIAMOND()->getHarvestLevel(),
                        'netherite' => ToolTier::NETHERITE()->getHarvestLevel(),
                        default => 0
                    };
                    $tool = match (\strtolower($file->getPocketmineComponentNested('block:tool.tool', ''))) {
                        'axe' => BlockToolType::AXE,
                        'hoe' => BlockToolType::HOE,
                        'pickaxe' => BlockToolType::PICKAXE,
                        'shears' => BlockToolType::SHEARS,
                        'shovel' => BlockToolType::SHOVEL,
                        'sword' => BlockToolType::SWORD,
                        default => BlockToolType::NONE
                    };
                    $breakInfo = match (true) {
                        isset($d_b_m_component['seconds_to_destroy']) => new BlockBreakInfo($seconds, $tool, $tool_tier, $blastResistance),
                        is_bool($d_b_m_component) && $d_b_m_component === true => new BlockBreakInfo($seconds, $tool, $tool_tier, $blastResistance),
                        default => BlockBreakInfo::indestructible()
                    };
                    $typeInfo = new BlockTypeInfo($breakInfo);
                    $traits = $file->getPocketmineProperties('traits');
                    $uses = $file->getPocketmineProperties('uses');
                    $extra_code = $file->getPocketmineProperties('extra_code');
                    $vars = $file->getPocketmineProperties('vars');
                    $implements = $file->getPocketmineProperties('implements');
                    $base = count($file->getStates()) > 0 ? match (true) {
                        $file->hasPocketmineComponent('pocketmine:crop') => BlockCropSimple::class,
                        $file->hasPocketmineComponent('pocketmine:brush_crop') => BlockBrushCropSimple::class,
                        default => BlockPermutable::class
                    } : BlockSimple::class;
                    if (count($traits) > 0 or (count($uses) > 0 or count($extra_code) > 0 or count($vars) > 0 or count($implements) > 0)) {
                        $dyn = "night\\dynamic\\block\\" . str_replace(":", "_", $file->getIdentifier());
                        if (!class_exists($dyn, false)) eval("namespace night\\dynamic\\block; " . implode(' ', array_map(fn($use) => str_starts_with($use, "use") ? $use : "use " . $use . (str_ends_with($use, ';') ? '' : ';'), $uses)) . " class " . str_replace(":", "_", $file->getIdentifier()) . " extends \\$base " . (empty($implements) ? '' : 'implements ' . implode(', ', $implements)) . " { " . (empty($traits) ? '' : "use " . implode(",", $traits) . "; ") . implode('; ', array_map(fn($var) => "public $" . $var . ';', $vars)) . " " . implode(' ', $extra_code) . "}");
                        $blockClass = $dyn;
                    }
                    $blockClass = $dyn ?? $base;
                    $block = new $blockClass($identifier, ($file->getRawComponent('minecraft:display_name', str_replace('_', ' ', explode(':', $file->getIdentifier())[1]))), $typeInfo, $file);
                    return $block;
                }, $file->getIdentifier(), $creativeInfo);
            }
        }
    }
}
