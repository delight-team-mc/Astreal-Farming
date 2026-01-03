<?php

namespace night\system\behavior;

use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\utils\Ageable;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\CropGrowthHelper;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

class BlockCropSimple extends BlockPermutable implements Ageable
{
    use StaticSupportTrait;

    protected int $age = 0;

    public function getDropsForCompatibleTool(Item $item): array
    {
        $result = [];
        foreach ($this->behavior->getPocketmineComponentNested('pocketmine:crop.crop_result', []) as $crop) {
            $crop_item = StringToItemParser::getInstance()->parse($crop['name']);
            if ($crop_item !== null && (($crop['age'] ?? 0)) >= $this->age) {
                $count = \is_array($crop['count']) ? \mt_rand(($crop['min'] ?? 1), ($crop['max'] ?? 1)) : $crop['count'];
                $result[] = $crop_item->setCount(($crop['fortune'] ?? false) ? FortuneDropHelper::binomial($item, $count) : $count);
            }
        }
        return $result;
    }

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
        return $this->behavior->getPocketmineComponentNested('pocketmine:crop.max_age', 7);
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

    private function canBeSupportedAt(Block $block): bool
    {
        if (($raw_facing = $this->behavior->getPocketmineComponentNested('pocketmine:crop.facing', 'down')) === 'any') return true;
        $facing = match ($raw_facing) {
            'down' => Facing::DOWN,
            'up' => Facing::UP,
            default => Facing::DOWN
        };
        $blocks = (is_array($b = $this->behavior->getPocketmineComponentNested('pocketmine:crop.can_be_supported_at', ['minecraft:farmland'])) ? $b : [$b]);
        return \in_array(GlobalBlockStateHandlers::getSerializer()->serialize($block->getSide($facing)->getStateId())->getName(), $blocks) or \count(\array_filter($blocks, fn(string $tag): bool => $block->hasTypeTag($tag))) > 0;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($this->age < $this->getMaxAge() && $item instanceof Fertilizer) {
            $block = clone $this;
            $tempAge = $block->age + mt_rand(2, 5);
            if ($tempAge > $this->getMaxAge()) {
                $tempAge = $this->getMaxAge();
            }
            $block->age = $tempAge;
            if (BlockEventHelper::grow($this, $block, $player)) {
                $item->pop();
            }

            return true;
        }

        return false;
    }

    public function ticksRandomly(): bool
    {
        return $this->age < $this->getMaxAge();
    }

    public function onRandomTick(): void
    {
        if ($this->age < $this->getMaxAge() && CropGrowthHelper::canGrow($this)) {
            $block = clone $this;
            ++$block->age;
            BlockEventHelper::grow($this, $block, null);
        }
    }

    public function canBeFlowedInto(): bool
    {
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

    public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock): bool
    {
        return (!$this->canBeFlowedInto() || !$blockReplace instanceof Liquid) &&
            parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
    }

    protected function recalculateCollisionBoxes(): array
    {
        return [];
    }

    public function getSupportType(int $facing): SupportType
    {
        return SupportType::NONE();
    }
}
