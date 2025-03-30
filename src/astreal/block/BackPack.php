<?php

namespace astreal\block;

use astreal\block\tile\BackPack as TitleBackPack;
use astreal\libraries\inventory\InventoryManager;
use astreal\libraries\vanilla\block\BlockComponents;
use astreal\libraries\vanilla\block\BlockComponentsTrait;
use astreal\libraries\vanilla\block\component\GeometryComponent;
use astreal\libraries\vanilla\block\component\MaterialInstancesComponent;
use astreal\libraries\vanilla\block\Material;
use astreal\libraries\vanilla\block\permutations\Permutable;
use astreal\libraries\vanilla\block\permutations\RotatableTrait;
use astreal\libraries\vanilla\CustomBlockTypeNames;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class BackPack extends Transparent implements Permutable, BlockComponents
{
    use BlockComponentsTrait;
    use RotatableTrait;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->initComponent(CustomBlockTypeNames::BACKPACK);
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, CustomBlockTypeNames::BACKPACK, Material::RENDER_METHOD_ALPHA_TEST)]));
        $this->addComponent(new GeometryComponent('geometry.backpack'));
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player !== null) {
            $tile = $this->position->getWorld()->getTile($this->position);
            if ($tile instanceof TitleBackPack) {
                return InventoryManager::getInstance()->sendInventory($player, $tile->getInventory());
            }
        }
        return false;
    }

    private function addDataFromTile(TitleBackPack $tile, Item $item): void
    {
        $shulkerNBT = $tile->getCleanedNBT();
        if ($shulkerNBT !== null) {
            $item->setNamedTag(CompoundTag::create()->setTag("BlockEntityTag", $shulkerNBT));
        }
    }

    public function getDrops(Item $item): array
    {
        $drop = $this->asItem();
        if (($tile = $this->position->getWorld()->getTile($this->position)) instanceof TitleBackPack) {
            $this->addDataFromTile($tile, $drop);
        }
        return [$drop];
    }

    public function getPickedItem(bool $addUserData = false): Item
    {
        $result = parent::getPickedItem($addUserData);
        if ($addUserData && ($tile = $this->position->getWorld()->getTile($this->position)) instanceof TitleBackPack) {
            $this->addDataFromTile($tile, $result);
        }
        return $result;
    }

    public function asItem(): Item
    {
        $item = parent::asItem();
        return $item;
    }

    public function onScheduledUpdate(): void
    {
        $tile = $this->position->getWorld()->getTile($this->position);
        if ($tile instanceof TitleBackPack) {
            $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
        }
    }
}
