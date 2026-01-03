<?php


namespace night\entity\projectile;

use night\entity\animation\FishHookPositionAnimation;
use night\system\FishingRodManager;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\block\Water;
use pocketmine\world\sound\CauldronEmptyWaterSound;

class FishingHook extends Projectile
{

    protected $touchWaterSound = false;
    public CompoundTag $compTag;
    public $elapsedtime = 1;
    private bool $has_fish = false;

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FISHING_HOOK;
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0;
    }

    protected function getInitialGravity(): float
    {
        return 0;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->compTag = $nbt;
    }

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $nbt);
        $this->setGravity(0.01);
        if ($shootingEntity instanceof Player) FishingRodManager::getInstance()->setHook($shootingEntity, $this);
        else $this->flagForDespawn();
    }

    /*
    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $player = $this->getOwningEntity();
        if ($player instanceof Player) {
            $playername = $player->getName();
            $this->unsetBubble($player);
            if (FishingRodManager::getInstance()->hasBubbleTime($player)) $this->broadcastFishHookPosition();
            $this->controlDistance();
            if (!FishingRodManager::getInstance()->hasFishingTime($player)) {
                $needwait = rand(4, 60);
                FishingRodManager::getInstance()->setFishingTime($player, time() + $needwait);
                if ($this->controlWater()) {
                    if (FishingRodManager::getInstance()->hasBubbleTime($player))
                        $this->getWorld()->addSound($this->location, new CauldronEmptyWaterSound());
                }
            }
            if (time() > FishingRodManager::getInstance()->getFishingTime($player)) {
                FishingRodManager::getInstance()->unsetFishingTime($player);
                $this->setBubbleTime($player);
            }
        }
        $despawn = false;
        if ($player instanceof Player) {
            if (
                $player->getInventory()->getItemInHand()->getTypeId() !== ItemTypeIds::FISHING_ROD ||
                !$player->isAlive() ||
                $player->isClosed() ||
                $player->getLocation()->getWorld()->getFolderName() !== $this->getLocation()->getWorld()->getFolderName()
            ) $despawn = true;
        } else $despawn = true;
        if ($despawn) {
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        if ($this->controlWater()) {
            if (!$this->touchWaterSound) {
                $this->getWorld()->addSound($this->location, new CauldronEmptyWaterSound());
                $this->touchWaterSound = true;
            }
        }
        return $hasUpdate;
    }*/

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $player = $this->getOwningEntity();
        $despawn = false;
        if ($player instanceof Player) {
            if (
                $player->getInventory()->getItemInHand()->getTypeId() !== ItemTypeIds::FISHING_ROD ||
                !$player->isAlive() ||
                $player->isClosed() ||
                $player->getLocation()->getWorld()->getFolderName() !== $this->getLocation()->getWorld()->getFolderName()
            ) $despawn = true;
        } else $despawn = true;
        if ($despawn) {
            FishingRodManager::getInstance()->unsetHook($player);
            if (!$this->isFlaggedForDespawn()) $this->flagForDespawn();
            return false;
        }
        return $hasUpdate;
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->closed) return false;
        if ($this->isUnderwater()) {
            $this->setHasGravity(false);
            $this->motion->y += $this->gravity / 2;
        } else $this->setHasGravity(true);
        return parent::onUpdate($currentTick);
    }

    public function setBubbleTime(Player $player)
    {
        $playername = $player->getName();
        if (!FishingRodManager::getInstance()->hasBubbleTime($player)) FishingRodManager::getInstance()->setBubbleTime($player, time() + 2);
        if (time() > FishingRodManager::getInstance()->getBubbleTime($player)) FishingRodManager::getInstance()->unsetBubbleTime($player);
    }

    public function flagForDespawn(): void
    {
        $owningEntity = $this->getOwningEntity();
        if ($owningEntity instanceof Player) {
            FishingRodManager::getInstance()->unsetHook($owningEntity);
            if (FishingRodManager::getInstance()->hasHook($owningEntity)) FishingRodManager::getInstance()->unsetHook($owningEntity);
        }
        parent::flagForDespawn();
    }

    public function broadcastFishHookPosition()
    {
        $this->broadcastAnimation(new FishHookPositionAnimation($this));
    }

    public function controlWater()
    {
        $x = $this->getPosition()->getFloorX();
        $y = $this->getPosition()->getFloorY();
        $z = $this->getPosition()->getFloorZ();
        $world = $this->getWorld();
        $block = $world->getBlockAt($x, $y - 1, $z);
        return $block instanceof Water;
    }

    public function controlDistance()
    {
        if ($this->getOwningEntity() instanceof Player && $this->getOwningEntity()->getPosition()->distance($this->getPosition()) >= 32) $this->flagForDespawn();
        return false;
    }
    public function unsetBubble(Player $player)
    {
        if (FishingRodManager::getInstance()->hasBubbleTime($player) && time() > FishingRodManager::getInstance()->getBubbleTime($player)) FishingRodManager::getInstance()->unsetBubbleTime($player);
    }
}
