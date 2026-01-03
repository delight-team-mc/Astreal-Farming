<?php

namespace night\system\behavior;

use night\system\behavior\Item as BehaviorItem;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\ItemComponents;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

class ItemDurableSimple extends Durable implements ItemComponents
{

    private BehaviorItem $behavior;

    public function setBehavior(BehaviorItem $item): self
    {
        $this->behavior = $item;
        return $this;
    }

    public function getEnchantability(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:enchantable.value', parent::getEnchantability());
    }

    public function getMaxDurability(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:durability.max_durability', 64);
    }

    public function applyDamage(int $amount): bool
    {
        if (!$this->behavior->hasRawComponent('minecraft:durability')) return parent::applyDamage($amount);
        if ($this->isUnbreakable() || $this->isBroken()) {
            return false;
        }

        $udr = function (int $amount): int {
            if (($unbreakingLevel = $this->getEnchantmentLevel(VanillaEnchantments::UNBREAKING())) > 0) {
                $component = $this->behavior->getRawComponentNested('minecraft:durability.damage_chance', ['min' => 0, 'max' => 100]);
                $baseChance = mt_rand(($component['min'] ?? 0), $component['max'] ?? 100) / 100;
                $unbreakingChance = 1 / ($unbreakingLevel + 1);
                $finalChance = max(($component['min'] ?? 0) / 100, min($baseChance * $unbreakingChance, ($component['max'] ?? 100) / 100));
                $negated = 0;
                for ($i = 0; $i < $amount; $i++) {
                    if (Utils::getRandomFloat() > $finalChance) {
                        $negated++;
                    }
                }
                return $negated;
            }
            return 0;
        };

        $amount -= $udr($amount);

        $this->damage = min($this->damage + $amount, $this->getMaxDurability());
        if ($this->isBroken()) {
            $this->onBroken();
        }

        return true;
    }

    public function getCooldownTicks(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.duration', 0);
    }

    public function getCooldownTag(): ?string
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.category', null);
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
