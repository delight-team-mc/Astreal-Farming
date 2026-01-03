<?php

namespace night\system\behavior;

use night\libraries\vanilla\block\BlockFactory;
use night\libraries\vanilla\entity\EntityFactory;
use night\system\behavior\Item as BehaviorItem;
use night\libraries\vanilla\item\component\ItemComponent;
use night\libraries\vanilla\item\ItemComponents;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemUseResult;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

class ItemSimple extends Item implements ItemComponents
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

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        if (!$this->behavior->hasRawComponent('minecraft:entity_placer')) return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector, $returnedItems);
        //$entity = $this->createEntity($player->getWorld(), $blockReplace->getPosition()->add(0.5, 0, 0.5), Utils::getRandomFloat() * 360, 0);
        $reflect = new \ReflectionClass(EntityFactory::class);
        $creationFuncs = $reflect->getProperty('creationFuncs')->getValue(EntityFactory::getInstance());
        $creationFunc = ($creationFuncs[$this->behavior->getRawComponentNested('minecraft:entity_placer.entity', '')] ?? null);
        if (is_null($creationFunc)) return throw new \Exception('no entity found');
        $entity = $creationFunc($player->getWorld(), CompoundTag::create()->setTag(Entity::TAG_POS, new ListTag([new DoubleTag($blockReplace->getPosition()->x), new DoubleTag($blockReplace->getPosition()->y), new DoubleTag($blockReplace->getPosition()->z)]))->setTag(Entity::TAG_MOTION, new ListTag([new DoubleTag(0), new DoubleTag(0), new DoubleTag(0)]))->setTag(Entity::TAG_ROTATION, new ListTag([new FloatTag(Utils::getRandomFloat() * 360), new FloatTag(0)])));
        if ($this->hasCustomName()) {
            $entity->setNameTag($this->getCustomName());
        }
        $this->pop();
        $entity->spawnToAll();
        //TODO: what if the entity was marked for deletion?
        return ItemUseResult::SUCCESS;
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
