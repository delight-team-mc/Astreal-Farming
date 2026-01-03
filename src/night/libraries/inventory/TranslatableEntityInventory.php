<?php

namespace night\libraries\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use night\libraries\inventory\interfaces\TranslatableInventory;

abstract class TranslatableEntityInventory extends SimpleInventory implements TranslatableInventory
{

    public function __construct(private int $actor_id, int $size)
    {
        parent::__construct($size);
    }

    abstract public function getWindowType(): int;

    public function getActorId(): int
    {
        return $this->actor_id;
    }

    public function translate(ContainerOpenPacket $packet): void
    {
        $packet->blockPosition = BlockPosition::fromVector3(Vector3::zero());
        $packet->actorUniqueId = $this->actor_id;
        $packet->windowType = $this->getWindowType();
    }
}
