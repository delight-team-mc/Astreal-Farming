<?php

namespace night\system\behavior;

use night\libraries\LootTableManager;
use night\system\behavior\Block as BehaviorBlock;
use night\libraries\vanilla\block\BlockComponents;
use night\libraries\vanilla\block\component\BlockComponent;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\item\Item;

class BlockSimple extends Block implements BlockComponents
{

    protected BehaviorBlock $behavior;

    public function __construct(BlockIdentifier $id, string $name, BlockTypeInfo $info, BehaviorBlock $behavior)
    {
        $this->behavior = $behavior;
        parent::__construct($id, $name, $info);
    }

    public function getLightLevel(): int
    {
        $component = $this->behavior->getRawComponent('minecraft:light_emission');
        return $component ?? parent::getLightLevel();
    }

    public function getLightFilter(): int
    {
        $component = $this->behavior->getRawComponent('minecraft:light_dampening');
        return $component ?? parent::getLightFilter();
    }

    public function getFrictionFactor(): float
    {
        $component = $this->behavior->getRawComponent('minecraft:friction');
        return $component ?? parent::getFrictionFactor();
    }

    public function hasEntityCollision(): bool
    {
        $component = $this->behavior->getRawComponent('minecraft:collision_box');
        return \is_array($component) ? true : (\is_bool($component) ? \boolval($component) : parent::hasEntityCollision());
    }

    public function getFlammability(): int
    {
        $component = $this->behavior->getRawComponent('minecraft:flammable');
        return $component ?? parent::getFlammability();
    }

    public function isAffectedBySilkTouch(): bool
    {
        return $this->behavior->getPocketmineProperties('is_affected_by_silk_touch', parent::isAffectedBySilkTouch());
    }

    public function getXpDropAmount(): int
    {
        return $this->behavior->getPocketmineProperties('xp_drop_amount', parent::getXpDropAmount());
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        if (!$this->behavior->hasRawComponent('minecraft:loot')) return parent::getDropsForCompatibleTool($item);
        $table = LootTableManager::getInstance()->load($this->behavior->getRawComponent('minecraft:loot', ''));
        if (is_null($table)) return [];
        return $table->get();
    }

    public function setBehavior(BehaviorBlock $block): self
    {
        $this->behavior = $block;
        return $this;
    }

    public function addComponent(BlockComponent $component): void
    {
        $this->behavior->addComponent($component);
    }

    public function hasComponent(string $name): bool
    {
        return isset($this->behavior->getComponents()[$name]);
    }

    /**
     * @return BlockComponent[]
     */
    public function getComponents(): array
    {
        return $this->behavior->getComponents();
    }

    public function __clone()
    {
        parent::__clone();
        $this->behavior = clone $this->behavior;
    }
}
