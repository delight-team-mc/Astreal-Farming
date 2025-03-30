<?php

namespace astreal\block\tile;

use astreal\libraries\inventory\MenuInventory;
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
use pocketmine\world\World;

class BackPack extends Spawnable implements Container
{
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }

    const BACKPACK_TAG = 'BackPack'; //CompoundTag
    const UPDRADES_TAG = 'Upgrades'; //ListTag <StringTag>
    const FURNACE_UPDRADE_TAG = 'Furnaces'; //ListTag <CompoundTag>

    private Inventory $inventory;

    public function __construct(World $world, Vector3 $pos)
    {
        parent::__construct($world, $pos);
        $this->inventory = new MenuInventory(27, VanillaBlocks::CHEST(), WindowTypes::CONTAINER);
    }

    public function getRealInventory(): MenuInventory
    {
        return $this->inventory;
    }

    public function getInventory(): MenuInventory
    {
        return $this->inventory;
    }

    protected function onBlockDestroyedHook(): void {}

    public function readSaveData(CompoundTag $nbt): void
    {
        $this->loadItems($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $this->saveItems($nbt);
        $backpack_tag = CompoundTag::create();
        $backpack_tag->setTag(self::UPDRADES_TAG, new ListTag([], NBT::TAG_String));
        $backpack_tag->setTag(self::FURNACE_UPDRADE_TAG, new ListTag([], NBT::TAG_Compound));
        $nbt->setTag(self::BACKPACK_TAG, $backpack_tag);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {}
}
