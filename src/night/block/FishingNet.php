<?php

namespace night\block;

use night\block\tile\FishingNet as TileFishingNet;
use night\libraries\inventory\InventoryManager;
use night\libraries\LootTableManager;
use night\libraries\vanilla\block\BlockComponents;
use night\libraries\vanilla\block\BlockComponentsTrait;
use night\libraries\vanilla\block\component\CollisionBoxComponent;
use night\libraries\vanilla\block\component\GeometryComponent;
use night\libraries\vanilla\block\component\MaterialInstancesComponent;
use night\libraries\vanilla\block\component\SelectionBoxComponent;
use night\libraries\vanilla\block\Material;
use night\libraries\vanilla\CustomBlockTypeNames;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Transparent;
use pocketmine\block\Water;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\WaterParticle;
use pocketmine\world\sound\CauldronFillWaterSound;

class FishingNet extends Transparent implements BlockComponents
{
    use BlockComponentsTrait;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->initComponent(CustomBlockTypeNames::BACKPACK);
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, CustomBlockTypeNames::BASIC_FISHING_NET, Material::RENDER_METHOD_ALPHA_TEST)]));
        $this->addComponent(new GeometryComponent('geometry.fishing.net'));
        $this->addComponent(new SelectionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 16.0, 16.0)));
        $this->addComponent(new CollisionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 16.0, 16.0)));
    }

    public function setMaterial(string $texture): FishingNet
    {
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, $texture, Material::RENDER_METHOD_ALPHA_TEST)]));
        return $this;
    }

    public function getFrictionFactor(): float
    {
        return 0.4;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player !== null) {
            $tile = $this->position->getWorld()->getTile($this->position);
            if ($tile instanceof TileFishingNet) {
                $tile->getInventory()->setCustomName($tile->getName());
                $tile->getInventory()->sendActor($player);
                return InventoryManager::getInstance()->sendInventory($player, $tile->getInventory());
            }
        }
        return false;
    }

    public function onScheduledUpdate(): void
    {
        $pos = $this->getPosition();
        $world = $pos->getWorld();
        $tile = $world->getTile($pos);
        if (!$tile instanceof TileFishingNet) return;
        $tile->generateWaitingTime();
        $adjacentPositions = [$pos->east(), $pos->west(), $pos->south(), $pos->north()];
        $hasWater = false;
        foreach ($adjacentPositions as $adjacentPos) {
            if ($world->getBlock($adjacentPos) instanceof Water) {
                $hasWater = true;
                break;
            }
        }
        if ($hasWater) {
            $tile->generateWaitingTime();
            $world->addParticle($pos->up(), new WaterParticle());
            $world->addSound($pos, new CauldronFillWaterSound());
            $lootTable = LootTableManager::getInstance()->load($tile->getLootTable());
            if ($lootTable !== null) {
                $tile->getInventory()->addItem(...$lootTable->get());
            }
        }
        $world->scheduleDelayedBlockUpdate($pos, $tile->getWaitingTime() * 20);
    }
}
