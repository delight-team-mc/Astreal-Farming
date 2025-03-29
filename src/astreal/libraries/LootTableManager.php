<?php

namespace astreal\libraries;

use astreal\libraries\managers\BaseManager;
use JsonSerializable;
use pocketmine\block\DyedShulkerBox;
use pocketmine\block\tile\Container;
use pocketmine\color\Color;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\EventPriority;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\WritableBookBase;
use pocketmine\item\WritableBookPage;
use pocketmine\item\WrittenBook;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Random;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use Symfony\Component\Filesystem\Path;

class LootTableManager extends BaseManager
{
    use SingletonTrait {
        getInstance as IS;
    }

    public static function getInstance(): LootTableManager
    {
        return self::IS();
    }

    public function __construct(private string $path)
    {
        parent::__construct('LootTables');
        self::setInstance($this);
        @mkdir($path);
        $this->getServer()->getPluginManager()->registerEvent(BlockPlaceEvent::class, $this->onBlockPlace(...), EventPriority::NORMAL, $this->getLoader());
    }

    private function onBlockPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        $item = $ev->getItem();
        if (!$item instanceof ItemBlock or ($block_nbt = $item->getCustomBlockData()) === null or ($loot_table = LootTableManager::getInstance()->load($block_nbt->getString('LootTable', ''))) === null) return;
        /** @var Block $b */
        foreach ($ev->getTransaction()->getBlocks() as [$x, $y, $z, $b]) {
            if ($item->getBlock() instanceof $b) {
                $this->getLoader()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($x, $y, $z, $player, $loot_table) {
                    if (($tile = $player->getWorld()->getTileAt($x, $y, $z)) instanceof Container) {
                        $slots = range(0, $tile->getInventory()->getSize() - 1);
                        shuffle($slots);
                        $drops = $loot_table->get();
                        foreach ($drops as $drop) {
                            if (empty($slots)) break;
                            $slot = array_pop($slots);
                            $tile->getInventory()->setItem($slot, $drop);
                        }
                    }
                }), 1);
            }
        }
    }

    public function has(string $file_path): bool
    {
        return file_exists(Path::join($this->path, $file_path));
    }

    public function load(string $file_path): ?LootTable
    {
        if (!$this->has($file_path)) return null;
        try {
            return new LootTable(json_decode(file_get_contents(Path::join($this->path, $file_path)), true, flags: JSON_THROW_ON_ERROR));
        } catch (\Throwable $th) {
            return null;
        }
    }
}

class LootTable implements JsonSerializable
{

    public function __construct(private array $data) {}

    /** @return Item[] */
    public function get(): array
    {
        $result = [];
        foreach ($this->data['pools'] as $pool) {
            $rolls = (int)((is_array($pool['rolls']) && isset($pool['rolls']['min']) && isset($pool['rolls']['max'])) ? mt_rand($pool['rolls']['min'], $pool['rolls']['max']) : (is_int($pool['rolls']) ? $pool['rolls'] : 1));
            for ($i = 0; $i < $rolls; $i++) {
                $entry = $this->getEntry($pool['entries']);
                if ($entry !== null) {
                    switch ($entry['type']) {
                        case 'item':
                            $item = StringToItemParser::getInstance()->parse($entry['name']);
                            if ($item !== null) {
                                $item = $this->parseFunction($item, $entry);
                                if ($item->getCount() > $item->getMaxStackSize()) {
                                    for ($i = $item->getCount(); $i <= $item->getMaxStackSize(); $i--) $result[] = (clone $item)->setCount(1);
                                } else $result[] = $item;
                            }
                            break;
                        case 'loot_table':
                            $loot_table = LootTableManager::getInstance()->load($entry['name']);
                            if ($loot_table !== null) array_merge($result, $loot_table->get());
                        case 'empty':
                            break;
                    }
                }
            }
        }
        return $result;
    }

    private function getEntry(array $entries): ?array
    {
        $totalWeight = array_sum(array_column($entries, "weight"));
        $rand = mt_rand(1, $totalWeight);
        $currentWeight = 0;
        foreach ($entries as $entry) {
            $currentWeight += $entry["weight"];
            if ($rand <= $currentWeight) {
                return $entry;
            }
        }
        return null;
    }

    private function parseFunction(Item $item, array $entry): Item
    {
        foreach (($entry['functions'] ?? []) as $function) {
            switch ($function['function']) {
                case 'set_name':
                    $item->setCustomName(TextFormat::colorize($function['name']));
                    break;
                case 'set_count':
                    if (is_array($function['count']) && isset($function['count']['min']) && isset($function['count']['max'])) {
                        $item->setCount(mt_rand($function['count']['min'], $function['count']['max']));
                    } elseif (is_int($function['count'])) {
                        $item->setCount($function['count']);
                    } else $item->setCount(1);
                    break;
                case 'set_lore':
                    $item->setLore(array_map(TextFormat::colorize(...), (is_string($function['lore']) ? explode("\n", $function['lore']) : $function['lore'])));
                    break;
                case 'set_damage':
                    if ($item instanceof Durable) {
                        if (is_array($function['damage']) && isset($function['damage']["min"], $function['damage']["max"])) {
                            $percent = mt_rand($function['damage']["min"] * 100, $function['damage']["max"] * 100) / 100;
                        } elseif (is_numeric($function['damage'])) {
                            $percent = floatval($function['damage']);
                        } else $percent = 0;
                        if ($percent > 0) $item->setDamage((int)($item->getMaxDurability() * (1 - $percent)));
                    }
                    break;
                case 'set_data':
                    break;
                case 'random_block_state':
                    switch ($function['block_state']) {
                        case 'color':
                            break;
                    }
                    break;
                case 'set_book_contents':
                    if ($item instanceof WritableBookBase && isset($function['pages'])) {
                        $item->setPages(array_map(fn(string $page): WritableBookPage => new WritableBookPage(TextFormat::colorize($page)), $function['pages']));
                        if ($item instanceof WrittenBook) {
                            if (isset($function['title'])) $item->setTitle($function['title']);
                            if (isset($function['author'])) $item->setAuthor($function['author']);
                        }
                    }
                    break;
                case 'random_dye':
                    if ($item instanceof Armor) $item->setCustomColor(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
                    break;
                case 'fill_container':
                    $item->setNamedTag(CompoundTag::create()->setTag($item::TAG_BLOCK_ENTITY_TAG, CompoundTag::create()->setString('LootTable', $function['loot_table'])));
                    break;
                case 'enchant_with_levels':
                    $xpLevel = mt_rand($function['levels']['min'] ?? 1, $function['levels']['max'] ?? 30);
                    $item = $item->equals(VanillaItems::BOOK()) ? VanillaItems::ENCHANTED_BOOK() : $item;
                    $enchantments = array_filter(
                        AvailableEnchantmentRegistry::getInstance()->getAllEnchantmentsForItem($item),
                        fn(Enchantment $ench) => ($function['treasure'] ?? false) || !in_array(
                            EnchantmentIdMap::getInstance()->toId($ench),
                            [EnchantmentIds::FROST_WALKER, EnchantmentIds::MENDING, EnchantmentIds::SOUL_SPEED, EnchantmentIds::BINDING, EnchantmentIds::VANISHING]
                        )
                    );
                    $appliedEnchantments = [];
                    for ($i = 0; $i < (1 + (new Random())->nextBoundedInt(4)); $i++) {
                        if (empty($enchantments)) break;
                        $enchantment = $enchantments[array_rand($enchantments)];
                        $isCompatible = true;
                        foreach ($appliedEnchantments as $applied) {
                            if (!$enchantment->isCompatibleWith($applied->getType())) {
                                $isCompatible = false;
                                break;
                            }
                        }
                        if (!$isCompatible) {
                            unset($enchantments[array_search($enchantment, $enchantments)]);
                            continue;
                        }
                        $level = min(mt_rand(1, (int) ceil($xpLevel / 10)), $enchantment->getMaxLevel());
                        $enchantInstance = new EnchantmentInstance($enchantment, $level);
                        $item->addEnchantment($enchantInstance);
                        $appliedEnchantments[] = $enchantInstance;
                    }
                    break;
                case 'enchant_randomly':
                    $item = $item->equals(VanillaItems::BOOK()) ? VanillaItems::ENCHANTED_BOOK() : $item;
                    $enchantments = array_filter(
                        AvailableEnchantmentRegistry::getInstance()->getAllEnchantmentsForItem($item),
                        fn(Enchantment $enchantment) => ($function['treasure'] ?? false) || !in_array(
                            EnchantmentIdMap::getInstance()->toId($enchantment),
                            [EnchantmentIds::FROST_WALKER, EnchantmentIds::MENDING, EnchantmentIds::SOUL_SPEED, EnchantmentIds::BINDING, EnchantmentIds::VANISHING]
                        )
                    );
                    $appliedEnchantments = [];
                    for ($i = 0; $i < (1 + (new Random())->nextBoundedInt(4)); $i++) {
                        if (empty($enchantments)) break;
                        $enchantment = $enchantments[array_rand($enchantments)];
                        $isCompatible = true;
                        foreach ($appliedEnchantments as $applied) {
                            if (!$enchantment->isCompatibleWith($applied->getType())) {
                                $isCompatible = false;
                                break;
                            }
                        }
                        if (!$isCompatible) {
                            unset($enchantments[array_search($enchantment, $enchantments)]);
                            continue;
                        }
                        $level = mt_rand(1, $enchantment->getMaxLevel());
                        $enchantInstance = new EnchantmentInstance($enchantment, $level);
                        $item->addEnchantment($enchantInstance);
                        $appliedEnchantments[] = $enchantInstance;
                    }
                    break;
                case 'specific_enchants':
                    $item = $item->equals(VanillaItems::BOOK()) ? VanillaItems::ENCHANTED_BOOK() : $item;
                    $appliedEnchantments = [];
                    foreach ($function['enchants'] as $enchant) {
                        if (is_array($enchant)) {
                            $enchantment = StringToEnchantmentParser::getInstance()->parse($enchant['id']);
                            if ($enchantment instanceof Enchantment) {
                                $isCompatible = true;
                                foreach ($appliedEnchantments as $applied) {
                                    if (!$enchantment->isCompatibleWith($applied->getType())) {
                                        $isCompatible = false;
                                        break;
                                    }
                                }
                                if (!$isCompatible) continue;
                                $level = is_int($enchant['level']) ? min($enchant['level'], $enchantment->getMaxLevel()) : min(mt_rand($enchant['level']['min'] ?? $enchant['level'][0], $enchant['level']['max'] ?? $enchant['level'][1]), $enchantment->getMaxLevel());
                                $enchantInstance = new EnchantmentInstance($enchantment, $level);
                                $item->addEnchantment($enchantInstance);
                                $appliedEnchantments[] = $enchantInstance;
                            }
                        } elseif (is_string($enchant)) {
                            $enchantment = StringToEnchantmentParser::getInstance()->parse($enchant);
                            if ($enchantment instanceof Enchantment) {
                                $isCompatible = true;
                                foreach ($appliedEnchantments as $applied) {
                                    if (!$enchantment->isCompatibleWith($applied->getType())) {
                                        $isCompatible = false;
                                        break;
                                    }
                                }
                                if (!$isCompatible) continue;
                                $enchantInstance = new EnchantmentInstance($enchantment);
                                $item->addEnchantment($enchantInstance);
                                $appliedEnchantments[] = $enchantInstance;
                            }
                        }
                    }
                    break;
                case 'set_potion':
                    if ($item instanceof Potion || $item instanceof SplashPotion) {
                        $item->setType(match (strtoupper($function['id'])) {
                            'WATER' => PotionType::WATER(),
                            'MUNDANE' => PotionType::MUNDANE(),
                            'LONG_MUNDANE' => PotionType::LONG_MUNDANE(),
                            'THICK' => PotionType::THICK(),
                            'AWKWARD' => PotionType::AWKWARD(),
                            'NIGHT_VISION' => PotionType::NIGHT_VISION(),
                            'LONG_NIGHT_VISION' => PotionType::LONG_NIGHT_VISION(),
                            'INVISIBILITY' => PotionType::INVISIBILITY(),
                            'LONG_INVISIBILITY' => PotionType::LONG_INVISIBILITY(),
                            'LEAPING' => PotionType::LEAPING(),
                            'LONG_LEAPING' => PotionType::LONG_LEAPING(),
                            'STRONG_LEAPING' => PotionType::STRONG_LEAPING(),
                            'FIRE_RESISTANCE' => PotionType::FIRE_RESISTANCE(),
                            'LONG_FIRE_RESISTANCE' => PotionType::LONG_FIRE_RESISTANCE(),
                            'SWIFTNESS' => PotionType::SWIFTNESS(),
                            'LONG_SWIFTNESS' => PotionType::LONG_SWIFTNESS(),
                            'STRONG_SWIFTNESS' => PotionType::STRONG_SWIFTNESS(),
                            'SLOWNESS' => PotionType::SLOWNESS(),
                            'LONG_SLOWNESS' => PotionType::LONG_SLOWNESS(),
                            'WATER_BREATHING' => PotionType::WATER_BREATHING(),
                            'LONG_WATER_BREATHING' => PotionType::LONG_WATER_BREATHING(),
                            'HEALING' => PotionType::HEALING(),
                            'STRONG_HEALING' => PotionType::STRONG_HEALING(),
                            'HARMING' => PotionType::HARMING(),
                            'STRONG_HARMING' => PotionType::STRONG_HARMING(),
                            'POISON' => PotionType::POISON(),
                            'LONG_POISON' => PotionType::LONG_POISON(),
                            'STRONG_POISON' => PotionType::STRONG_POISON(),
                            'REGENERATION' => PotionType::REGENERATION(),
                            'LONG_REGENERATION' => PotionType::LONG_REGENERATION(),
                            'STRONG_REGENERATION' => PotionType::STRONG_REGENERATION(),
                            'STRENGTH' => PotionType::STRENGTH(),
                            'LONG_STRENGTH' => PotionType::LONG_STRENGTH(),
                            'STRONG_STRENGTH' => PotionType::STRONG_STRENGTH(),
                            'WEAKNESS' => PotionType::WEAKNESS(),
                            'LONG_WEAKNESS' => PotionType::LONG_WEAKNESS(),
                            'WITHER' => PotionType::WITHER(),
                            'TURTLE_MASTER' => PotionType::TURTLE_MASTER(),
                            'LONG_TURTLE_MASTER' => PotionType::LONG_TURTLE_MASTER(),
                            'STRONG_TURTLE_MASTER' => PotionType::STRONG_TURTLE_MASTER(),
                            'SLOW_FALLING' => PotionType::SLOW_FALLING(),
                            'LONG_SLOW_FALLING' => PotionType::LONG_SLOW_FALLING(),
                            'STRONG_SLOWNESS' => PotionType::STRONG_SLOWNESS(),
                            default => PotionType::WATER()
                        });
                    }
                    break;
            }
        }
        return $item;
    }


    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
