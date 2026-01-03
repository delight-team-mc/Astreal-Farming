<?php

namespace night\block\tile;

use night\block\inventory\BackPackInventory;
use night\libraries\inventory\MenuInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;
use pocketmine\world\World;
use Ramsey\Uuid\Uuid;

class BackPack extends Spawnable implements Container
{
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }

    const BACKPACK_TAG = 'BackPack'; //CompoundTag
    const UUID_TAG = 'Uuid'; //StringTag
    const UPDRADES_TAG = 'Upgrades'; //ListTag <StringTag>
    const FURNACE_UPDRADE_TAG = 'Furnaces'; //ListTag <CompoundTag>

    private BackPackInventory $inventory;
    private string $uuid;

    public function __construct(World $world, Vector3 $pos)
    {
        parent::__construct($world, $pos);
        $this->inventory = new BackPackInventory(Position::fromObject($pos->down()->north(2), $world));
    }

    public function getRealInventory(): BackPackInventory
    {
        return $this->inventory;
    }

    public function getInventory(): BackPackInventory
    {
        return $this->inventory;
    }

    protected function onBlockDestroyedHook(): void {}

    public function readSaveData(CompoundTag $nbt): void
    {
        $this->loadItems($nbt);
        $backpack_tag = $nbt->getCompoundTag(self::BACKPACK_TAG) ?? CompoundTag::create();
        $this->uuid = $backpack_tag->getString(self::UUID_TAG, Uuid::uuid4()->toString());
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $this->saveItems($nbt);
        $backpack_tag = CompoundTag::create();
        $backpack_tag->setString(self::UUID_TAG, $this->uuid??Uuid::uuid4()->toString());
        $backpack_tag->setTag(self::UPDRADES_TAG, new ListTag([], NBT::TAG_String));
        $backpack_tag->setTag(self::FURNACE_UPDRADE_TAG, new ListTag([], NBT::TAG_Compound));
        $nbt->setTag(self::BACKPACK_TAG, $backpack_tag);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {}
}
