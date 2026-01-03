<?php

namespace night\system\behavior;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\utils\Ageable;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\sound\SweetBerriesPickSound;

class BlockBrushCropSimple extends BlockPermutable implements Ageable
{
    use StaticSupportTrait;

    public const STAGE_SAPLING = 0;
    public const STAGE_BUSH_NO_BERRIES = 1;
    public const STAGE_BUSH_SOME_BERRIES = 2;
    public const STAGE_MATURE = 3;
    public const MAX_AGE = self::STAGE_MATURE;

    protected int $age = 0;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
    {
        $w->boundedIntAuto(0, $this->getMaxAge(), $this->age);
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getMaxAge(): int
    {
        return self::MAX_AGE;
    }

    /**
     * @return $this
     */
    public function setAge(int $age): self
    {
        if ($age < 0 || $age > $this->getMaxAge()) {
            throw new \InvalidArgumentException("Age must be in range 0 ... " . $this->getMaxAge());
        }
        $this->age = $age;
        return $this;
    }

    public function getBerryDropAmount(): int
    {
        if ($this->age === self::STAGE_MATURE) {
            return mt_rand($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.min', 2), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.max', 3));
        } elseif ($this->age >= self::STAGE_BUSH_SOME_BERRIES) {
            return mt_rand($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.min', 1), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.max', 2));
        }
        return 0;
    }

    protected function canBeSupportedBy(Block $block): bool
    {
        $blocks = (is_array($b = $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.can_be_supported_at', [BlockTypeTags::DIRT, BlockTypeTags::MUD])) ? $b : [$b]);
        if (\in_array(GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName(), $blocks)) return true;
        if (\count(\array_filter($blocks, fn(string $tag): bool => $block->hasTypeTag($tag))) > 0) return true;
        return false;
    }

    private function canBeSupportedAt(Block $block): bool
    {
        $supportBlock = $block->getSide(Facing::DOWN);
        return $this->canBeSupportedBy($supportBlock);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $world = $this->position->getWorld();
        if ($this->age < self::STAGE_MATURE && $item instanceof Fertilizer) {
            $block = clone $this;
            $block->age++;
            if (BlockEventHelper::grow($this, $block, $player)) {
                $item->pop();
            }
        } elseif (($dropAmount = $this->getBerryDropAmount()) > 0) {
            \GlobalLogger::get()->notice(\pocketmine\world\format\io\GlobalItemDataHandlers::getSerializer()->serializeType($this->asItem())->getName());
            $world->setBlock($this->position, $this->setAge(self::STAGE_BUSH_NO_BERRIES));
            $world->dropItem($this->position, $this->asItem()->setCount($dropAmount));
            $world->addSound($this->position, new SweetBerriesPickSound());
        }

        return true;
    }

    public function asItem(): Item
    {
        return (StringToItemParser::getInstance()->parse($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.crop_result', '')) ?? VanillaItems::AIR());
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        $count = match ($this->age) {
            self::STAGE_MATURE => ($this->behavior->getPocketmineComponent('pocketmine:brush_crop.mature.fortune', true) ? \mt_rand($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.min', 2), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.max', 3)) : FortuneDropHelper::discrete($item, $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.min', 2), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.mature.max', 3))),
            self::STAGE_BUSH_SOME_BERRIES => ($this->behavior->getPocketmineComponent('pocketmine:brush_crop.some_berries.fortune', true) ? \mt_rand($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.min', 1), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.max', 2)) : FortuneDropHelper::discrete($item, $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.min', 1), $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.some_berries.max', 2))),
            default => 0
        };
        return [
            $this->asItem()->setCount($count)
        ];
    }

    public function ticksRandomly(): bool
    {
        return $this->age < self::STAGE_MATURE;
    }

    public function onRandomTick(): void
    {
        if ($this->age < self::STAGE_MATURE && mt_rand(0, 2) === 1) {
            $block = clone $this;
            ++$block->age;
            BlockEventHelper::grow($this, $block, null);
        }
    }

    public function hasEntityCollision(): bool
    {
        return $this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.collision_damage', false);
    }

    public function onEntityInside(Entity $entity): bool
    {
        if ($this->behavior->getPocketmineComponentNested('pocketmine:brush_crop.collision_damage', false) && $this->age >= self::STAGE_BUSH_NO_BERRIES && $entity instanceof Living) {
            $entity->resetFallDistance();
            $entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageByBlockEvent::CAUSE_CONTACT, 1));
        }
        return true;
    }

    public function isSolid(): bool
    {
        return false;
    }

    public function isTransparent(): bool
    {
        return true;
    }
}
