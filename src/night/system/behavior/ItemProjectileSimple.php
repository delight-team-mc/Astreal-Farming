<?php

namespace night\system\behavior;

use night\system\behavior\Item as BehaviorItem;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\ItemComponents;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ProjectileItem;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;

class ItemProjectileSimple extends ProjectileItem implements ItemComponents
{

    private BehaviorItem $behavior;

    public function setBehavior(BehaviorItem $item): self
    {
        $this->behavior = $item;
        return $this;
    }

    public function getCooldownTicks(): int
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.duration', 0);
    }

    public function getCooldownTag(): ?string
    {
        return $this->behavior->getRawComponentNested('minecraft:cooldown.category', null);
    }

    public function getThrowForce(): float
    {
        return $this->behavior->getRawComponentNested('minecraft:throwable.max_launch_power', 0.5);
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        $reflect = new \ReflectionClass(EntityFactory::class);
        $creationFuncs = $reflect->getProperty('creationFuncs')->getValue(EntityFactory::getInstance());
        $creationFunc = ($creationFuncs[$this->behavior->getRawComponentNested('minecraft:projectile.projectile_entity', '')] ?? null);
        if (is_null($creationFunc)) return throw new \Exception('no entity found');
        $entity = $creationFunc($location->getWorld(), CompoundTag::create()->setTag(Entity::TAG_POS, new ListTag([new DoubleTag($location->x), new DoubleTag($location->y), new DoubleTag($location->z)]))->setTag(Entity::TAG_MOTION, new ListTag([new DoubleTag(0), new DoubleTag(0), new DoubleTag(0)]))->setTag(Entity::TAG_ROTATION, new ListTag([new FloatTag($location->yaw), new FloatTag($location->pitch)])));
        return $entity;
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
