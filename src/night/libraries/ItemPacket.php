<?php

namespace night\libraries;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;

final class ItemPacket
{
    private EntityMetadataCollection $metadata;
    private int $id = 0;
    private int $index = 0;
    private bool $nameTagVisible = true;

    public static function create(Position $position, Item $item): self
    {
        return new self($position, $item);
    }

    public function __construct(public Position $position, public Item $item)
    {
        $this->id = Entity::nextRuntimeId();
        $this->metadata = new EntityMetadataCollection();
    }

    public function setPosition(Position $position): ItemPacket
    {
        $this->position = $position;
        return $this;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIndex(int $index): ItemPacket
    {
        $this->index = $index;
        return $this;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function setItem(Item $item): ItemPacket
    {
        $this->item = $item;
        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function isNameTagVisible(): bool
    {
        return $this->nameTagVisible;
    }

    public function setNameTagVisible(bool $value = true): void
    {
        $this->nameTagVisible = $value;
    }

    public function sendRespawnPacket(Player $player): void
    {
        $this->sendDespawnPacket($player);
        $this->sendSpawnPacket($player);
    }

    public function sendDespawnPacket(Player $player): void
    {
        $player->getNetworkSession()->sendDataPacket(RemoveActorPacket::create($this->getId()));
    }

    public function getMetadata(): EntityMetadataCollection
    {
        $this->metadata->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, true);
        $this->metadata->setGenericFlag(EntityMetadataFlags::NO_AI, false);
        $this->metadata->setGenericFlag(EntityMetadataFlags::IMMOBILE, 1);
        $this->metadata->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
        $this->metadata->setGenericFlag(EntityMetadataFlags::SILENT, false);
        $this->metadata->setGenericFlag(EntityMetadataFlags::CAN_CLIMB, false);
        $this->metadata->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, false);
        $this->metadata->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->isNameTagVisible() ? 1 : 0);
        $this->metadata->setString(EntityMetadataProperties::NAMETAG, $this->item->getName());
        $this->metadata->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
        $this->metadata->setFloat(EntityMetadataProperties::SCALE, 0.01);
        $this->metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
        $this->metadata->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
        return $this->metadata;
    }

    public function sendSpawnPacket(Player $player): void
    {
        ($session = $player->getNetworkSession())->sendDataPacket(AddItemActorPacket::create(
            $this->getId(), //TODO: entity unique ID
            $this->getId(),
            ItemStackWrapper::legacy($session->getTypeConverter()->coreItemStackToNet($this->getItem())),
            $this->position->asVector3(),
            new Vector3(0, 0, 0),
            $this->getMetadata()->getAll(),
            false //TODO: I have no idea what this is needed for, but right now we don't support fishing anyway
        ));
    }
}
