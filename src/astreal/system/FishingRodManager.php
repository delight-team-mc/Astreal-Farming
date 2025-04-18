<?php

namespace astreal\system;

use astreal\entity\projectile\FishingHook;
use astreal\libraries\managers\BaseManager;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Location;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\FishingRod;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use pocketmine\world\sound\ThrowSound;

class FishingRodManager extends BaseManager
{
    use SingletonTrait {
        getInstance as IS;
    }

    public static function getInstance(): FishingRodManager
    {
        return self::IS();
    }

    public array $fishingtime = [], $bubbletime = [], $hooks = [];

    public function __construct()
    {
        parent::__construct('FishingRodManager');
        //$this->getServer()->getPluginManager()->registerEvent(PlayerItemUseEvent::class, $this->__PlayerItemUse(...), EventPriority::NORMAL, $this->getLoader());
    }

    private function __PlayerItemUse(PlayerItemUseEvent $ev): void
    {
        $item = $ev->getItem();
        $player = $ev->getPlayer();
        if (!$item instanceof FishingRod) return;
        if ($player->hasItemCooldown($item)) {
            $ev->cancel();
            return;
        }
        $player->resetItemCooldown($item, 8);
        if (!$this->hasHook($player)) {
            $player->getWorld()->addSound($player->getPosition(), new ThrowSound());
            $location = $player->getLocation();
            $hook = new FishingHook(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
            $hook->spawnToAll();
            $hook->setMotion($player->getDirectionVector()->multiply(0.4));
            $this->setHook($player, $hook);
        } else {
            $item->applyDamage(1);
            $player->getInventory()->setItemInHand($item);
            $hook = $this->getFishingHook($player);
            $this->unsetHook($player);
            if (!$hook->isFlaggedForDespawn()) {
                $hook->flagForDespawn();
            }
        }
        $player->broadcastAnimation(new ArmSwingAnimation($player));
    }

    public function hasHook(Player $player): bool
    {
        return isset($this->hooks[$player->getPlayerInfo()->getUsername()]);
    }

    public function unsetHook(Player $player): void
    {
        unset($this->hooks[$player->getPlayerInfo()->getUsername()]);
    }

    public function setHook(Player $player, FishingHook $hook): void
    {
        $this->hooks[$player->getPlayerInfo()->getUsername()] = $hook;
    }

    public function getFishingHook(Player $player): ?FishingHook
    {
        return $this->hooks[$player->getPlayerInfo()->getUsername()] ?? null;
    }

    public function setBubbleTime(Player $player, int $time): void
    {
        $this->bubbletime[$player->getPlayerInfo()->getUsername()] = $time;
    }

    public function getBubbleTime(Player $player): int
    {
        return $this->bubbletime[$player->getPlayerInfo()->getUsername()] ?? 0;
    }

    public function unsetBubbleTime(Player $player): void
    {
        unset($this->bubbletime[$player->getPlayerInfo()->getUsername()]);
    }

    public function hasBubbleTime(Player $player): bool
    {
        return isset($this->bubbletime[$player->getPlayerInfo()->getUsername()]);
    }

    public function setFishingTime(Player $player, int $time): void
    {
        $this->fishingtime[$player->getPlayerInfo()->getUsername()] = $time;
    }

    public function getFishingTime(Player $player): int
    {
        return $this->fishingtime[$player->getPlayerInfo()->getUsername()] ?? 0;
    }

    public function unsetFishingTime(Player $player): void
    {
        unset($this->fishingtime[$player->getPlayerInfo()->getUsername()]);
    }

    public function hasFishingTime(Player $player): bool
    {
        return isset($this->fishingtime[$player->getPlayerInfo()->getUsername()]);
    }
}
