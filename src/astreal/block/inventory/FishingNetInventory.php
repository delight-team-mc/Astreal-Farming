<?php

namespace astreal\block\inventory;

use astreal\libraries\inventory\interfaces\ListenerInventory;
use astreal\libraries\inventory\TranslatableEntityInventory;
use astreal\libraries\inventory\utils\ListenerTransaction;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;

class FishingNetInventory extends TranslatableEntityInventory implements ListenerInventory
{
    private $custom_name = 'Fishing Net';
    public function __construct()
    {
        parent::__construct(Entity::nextRuntimeId(), 27);
        ($lock = VanillaItems::WHEAT()->setCustomName('§r'))->getNamedTag()->setByte('lock', 1);
        $this->setItem(7, $lock);
        $this->setItem(16, $lock);
        $this->setItem(25, $lock);
    }

    public function getWindowType(): int
    {
        return WindowTypes::CONTAINER;
    }

    public function sendActor(Player $who): void
    {
        $properties = new EntityMetadataCollection();
        $properties->setByte(EntityMetadataProperties::CONTAINER_TYPE, WindowTypes::MINECART_CHEST);
        $properties->setInt(EntityMetadataProperties::CONTAINER_BASE_SIZE, $this->getSize());
        $properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
        $properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
        $properties->setFloat(EntityMetadataProperties::SCALE, 0.01);
        $properties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $properties->setLong(EntityMetadataProperties::OWNER_EID, -1);
        $properties->setLong(EntityMetadataProperties::TARGET_EID, 0);
        $properties->setString(EntityMetadataProperties::NAMETAG, $this->custom_name);
        $properties->setString(EntityMetadataProperties::SCORE_TAG, '');
        $properties->setByte(EntityMetadataProperties::COLOR, 0);
        $properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
        $properties->setGenericFlag(EntityMetadataFlags::CAN_CLIMB, false);
        $properties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, false);
        $properties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, false);
        $properties->setGenericFlag(EntityMetadataFlags::NO_AI, true);
        $properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
        $properties->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $properties->setGenericFlag(EntityMetadataFlags::WALLCLIMBING, false);
        $who->getNetworkSession()->sendDataPacket(AddActorPacket::create($this->getActorId(), $this->getActorId(), EntityIds::AXOLOTL, $who->getPosition(), null, 0.0, 0.0, 0.0, 0.0, [], $properties->getAll(), new PropertySyncData([], []), []));
    }

    public function onClose(Player $who): void
    {
        parent::onClose($who);
        $who->getNetworkSession()->sendDataPacket(RemoveActorPacket::create($this->getActorId()));
    }

    public function setCustomName(string $custom_name): void
    {
        $this->custom_name = $custom_name;
    }

    public function getUpdradeA(): Item
    {
        return $this->getItem(7);
    }

    public function getUpdradeB(): Item
    {
        return $this->getItem(16);
    }

    public function getUpdradeC(): Item
    {
        return $this->getItem(25);
    }

    public function setContents(array $items): void
    {
        ($lock = VanillaItems::WHEAT()->setCustomName('§r'))->getNamedTag()->setByte('lock', 1);
        $items[7] = $lock;
        $items[16] = $lock;
        $items[25] = $lock;
        parent::setContents($items);
    }

    public function isSlotEmpty(int $index): bool
    {
        if (in_array($index, [8, 17, 26])) return false;
        return parent::isSlotEmpty($index);
    }

    public function close(Player $player): void {}

    public function transaction(ListenerTransaction $transaction): bool
    {
        return in_array($transaction->getAction()->getSlot(), [7, 16, 25]);
    }
}
