<?php

namespace night\block\tile;

use night\block\inventory\GraveInventory;
use muqsit\invmenu\InvMenu;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Grave extends Spawnable implements Container, Nameable
{
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait {
        onBlockDestroyedHook as containerTraitBlockDestroyedHook;
    }

    const GRAVE_TAG = 'Grave'; //CompoundTag
    const OWNER_TAG = 'Owner'; //StringTag
    const DATE_TAG = 'Date'; //StringTag

    private GraveInventory $inventory;
    private InvMenu $menu;
    private string $date = '???', $owner = '';

    public function __construct(World $world, Vector3 $pos)
    {
        parent::__construct($world, $pos);
        $this->inventory = new GraveInventory(27 * 2);
        $this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $this->menu->setInventory($this->inventory);
    }

    public function getMenu(): InvMenu
    {
        return $this->menu;
    }

    public function getRealInventory(): GraveInventory
    {
        return $this->inventory;
    }

    public function getInventory(): GraveInventory
    {
        return $this->inventory;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setDate(): void
    {
        $this->date = date('Y-m-d H:i:s');
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getDefaultName(): string
    {
        return "Grave";
    }

    public function canOpenWith(string $key): bool
    {
        return $this->owner === '' || $key === $this->owner;
    }

    public function readSaveData(CompoundTag $nbt): void
    {
        $this->loadName($nbt);
        $this->loadItems($nbt);
        $grave_tag = $nbt->getCompoundTag(self::GRAVE_TAG) ?? CompoundTag::create();
        $this->date = $grave_tag->getString(self::DATE_TAG, $this->date);
        $this->owner = $grave_tag->getString(self::OWNER_TAG, $this->owner);
    }

    protected function writeSaveData(CompoundTag $nbt): void
    {
        $this->saveName($nbt);
        $this->saveItems($nbt);
        $grave_tag = CompoundTag::create();
        $grave_tag->setString(self::OWNER_TAG, $this->owner);
        $grave_tag->setString(self::DATE_TAG, $this->date);
        $nbt->setTag(self::GRAVE_TAG, $grave_tag);
    }
}
