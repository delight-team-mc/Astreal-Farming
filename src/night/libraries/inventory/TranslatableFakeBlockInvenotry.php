<?php

namespace night\libraries\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\Tile;
use pocketmine\inventory\SimpleInventory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use night\libraries\inventory\interfaces\FakeBlockInventory;
use night\libraries\inventory\interfaces\NameableFakeBlockInventory;

abstract class TranslatableFakeBlockInvenotry extends SimpleInventory implements BlockInventory,FakeBlockInventory{
    private ?CompoundTag $tile=null;

    public static function createTile(string $tile_id):CompoundTag{
        return CompoundTag::create()->setString(Tile::TAG_ID,$tile_id);
    }

    public function setTile(?CompoundTag $tag):void{
        $this->tile=$tag;
    }

    public function beforeOpeningInventory(Player $player):void{
        $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->getHolder()),TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->getBlock()->getStateId()),UpdateBlockPacket::FLAG_NETWORK,UpdateBlockPacket::DATA_LAYER_NORMAL));
        if($this instanceof NameableFakeBlockInventory&&!is_null($this->tile)){
            $this->tile->setString($this::TAG_CUSTOM_NAME,$this->getName());
            $player->getNetworkSession()->sendDataPacket(BlockActorDataPacket::create(BlockPosition::fromVector3($this->getHolder()),new CacheableNbt($this->tile)));
        }
    }

    public function onClose(Player $who):void{
        parent::onClose($who);
        $send_data_packet=$who->getNetworkSession()->sendDataPacket(...);
        $send_data_packet(UpdateBlockPacket::create(blockPosition::fromVector3($this->getHolder()),TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->getHolder()->getWorld()->getBlock($this->getHolder())->getStateId()),UpdateBlockPacket::FLAG_NETWORK,UpdateBlockPacket::DATA_LAYER_NORMAL),true);
        $tile=$this->getHolder()->getWorld()->getTile($this->getHolder());
        if($tile instanceof Spawnable)$send_data_packet(BlockActorDataPacket::create(BlockPosition::fromVector3($this->getHolder()),$tile->getSerializedSpawnCompound()),true);
    }

    public function translate(ContainerOpenPacket $packet):void{
        $packet->blockPosition=BlockPosition::fromVector3($this->getHolder());
        $packet->windowType=$this->getWindowType();
    }
}