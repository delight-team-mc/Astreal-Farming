<?php

namespace astreal\block\tile;

use astreal\block\inventory\FishingNetInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class FishingNet extends Spawnable implements Container, Nameable
{
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }

    const FISHING_TAG = 'Fishing'; //CompoundTag
    const WAITING_TIME_TAG = 'WaitingTime'; //InttTag
    const LOOT_TABLE = 'LootTable'; //StringTag

    private FishingNetInventory $inventory;
    private string $loot_table = 'loot_tables/gameplay/fishing.json';
    private int $waiting_time = 60;

    public function __construct(World $world, Vector3 $pos)
    {
        parent::__construct($world, $pos);
        $this->inventory = new FishingNetInventory();
    }

    public function getRealInventory(): FishingNetInventory
    {
        return $this->inventory;
    }

    public function getInventory(): FishingNetInventory
    {
        return $this->inventory;
    }

    public function getDefaultName(): string
    {
        return "Fishing Net";
    }

    public function setLootTable(string $table): void
    {
        $this->loot_table = $table;
    }

    public function getLootTable(): string
    {
        return $this->loot_table;
    }

    public function setWaitingTime(int $time): void
    {
        $this->waiting_time = $time;
    }

    public function getWaitingTime(): int
    {
        return $this->waiting_time;
    }

    public function generateWaitingTime(int $upgrade_level = 0): void
    {
        $this->waiting_time = mt_rand(30 - ($upgrade_level > 0 ? $upgrade_level * 5 : 0), 60 - ($upgrade_level > 0 ? $upgrade_level * 5 : 0));
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        $this->loadName($nbt);
        $this->loadItems($nbt);
        $fishing_tag = $nbt->getCompoundTag(self::FISHING_TAG) ?? CompoundTag::create();
        $this->waiting_time = $fishing_tag->getInt(self::WAITING_TIME_TAG, $this->waiting_time);
        $this->loot_table = $fishing_tag->getString(self::LOOT_TABLE, $this->loot_table);
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $this->saveName($nbt);
        $this->saveItems($nbt);
        $fishing_tag = CompoundTag::create();
        $fishing_tag->setInt(self::WAITING_TIME_TAG, $this->waiting_time);
        $fishing_tag->setString(self::LOOT_TABLE, $this->loot_table);
        $nbt->setTag(self::FISHING_TAG, $fishing_tag);
    }

    protected function onBlockDestroyedHook(): void
    {
        $inv = $this->getRealInventory();
        $pos = $this->getPosition();

        $world = $pos->getWorld();
        $dropPos = $pos->add(0.5, 0.5, 0.5);
        foreach ($inv->getContents() as $k => $item) {
            if ($item->getNamedTag()->getByte('lock', 0) !== 1) $world->dropItem($dropPos, $item);
        }
        $inv->clearAll();
    }
}
