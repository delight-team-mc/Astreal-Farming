<?php

namespace night\system\behavior;

use night\libraries\vanilla\block\BlockFactory;
use night\system\behavior\Item as BehaviorItem;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\ItemComponents;
use pocketmine\block\Block;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use pocketmine\item\Food;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Utils;

class ItemFoodSimple extends Food implements ItemComponents
{

    private BehaviorItem $behavior;

    public function setBehavior(BehaviorItem $item): self
    {
        $this->behavior = $item;
        return $this;
    }

    public function getBlock(?int $clickedFace = null): Block
    {
        $item = BlockFactory::getInstance()->get($this->behavior->getRawComponentNested('minecraft:block_placer.block', '')) ?? StringToItemParser::getInstance()->parse($this->behavior->getRawComponentNested('minecraft:block_placer.block', ''));
        return ($item instanceof Block ? $item : ($item instanceof ItemBlock ? $item->getBlock($clickedFace) : parent::getBlock($clickedFace)));
    }

    public function requiresHunger(): bool
    {
        return !$this->behavior->getRawComponentNested('minecraft:food.can_always_eat', true);
    }

    public function getResidue(): Item
    {
        return (StringToItemParser::getInstance()->parse($this->behavior->getRawComponentNested('minecraft:food.using_converts_to', 'minecraft:air')) ?? VanillaItems::AIR());
    }

    public function getAdditionalEffects(): array
    {
        return array_map(
            fn(array $effect): EffectInstance => new EffectInstance(StringToEffectParser::getInstance()->parse($effect['name']), ($effect['duration'] ?? 1) * 20, ($effect['amplifier'] ?? 0), ($effect['visible'] ?? true), ($effect['ambient'] ?? false)),
            \array_filter(
                \array_filter($this->behavior->getRawComponentNested('minecraft:food.effects', []), fn(array $effect): bool => StringToEffectParser::getInstance()->parse(($effect['name'] ?? '')) !== null),
                fn(array $effect): bool => Utils::getRandomFloat() <= ($effect['chance'] ?? 1.0)
            )
        );
    }

    public function onConsume(Living $consumer): void
    {
        foreach (\array_map(fn(array $effect): Effect => StringToEffectParser::getInstance()->parse(($effect['name'] ?? '')), \array_filter($this->behavior->getRawComponentNested('minecraft:food.remove_effects', []), fn(array $effect): bool => StringToEffectParser::getInstance()->parse(($effect['name'] ?? '')) !== null)) as $effect) $consumer->getEffects()->remove($effect);
    }

    public function getFoodRestore(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:food.nutrition', 0);
    }

    public function getSaturationRestore(): float
    {
        return $this->behavior->getRawComponentNested('minecraft:food.saturation_modifier', 0);
    }

    public function getMaxStackSize(): int
    {
        return $this->behavior->getRawComponent('minecraft:max_stack_size', parent::getMaxStackSize());
    }

    public function getAttackPoints(): int
    {
        return $this->behavior->getRawComponent('minecraft:damage', parent::getAttackPoints());
    }

    public function getFuelTime(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:fuel.duraction', parent::getFuelTime());
    }

    public function getCooldownTicks(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.duration', 0);
    }

    public function getCooldownTag(): ?string
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.category', null);
    }

    public function addComponent(ItemComponent $component): self
    {
        $this->behavior->addComponent($component);
        return $this;
    }

    public function hasComponent(string $name): bool
    {
        return isset($this->behavior->getComponents()[$name]);
    }

    /**
     * @return ItemComponent[]
     */
    public function getComponents(): array
    {
        return $this->behavior->getComponents();
    }
}
