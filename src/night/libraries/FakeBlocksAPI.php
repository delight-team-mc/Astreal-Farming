<?php

namespace night\libraries;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;

class FakeBlocksAPI
{
    private static array $sends = [];

    public static function send(Player $player, Block $block, Vector3 $pos): void
    {
        self::$sends[$player->getName()][] = $pos;
        $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($pos), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
    }

    public static function sendAll(Player $player, array $blocks): void
    {
        foreach ($blocks as [$block, $pos]) self::send($player, $block, $pos);
    }

    public static function remove(Player $player, Vector3 $pos): void
    {
        if (isset(self::$sends[$player->getName()])) {
            $index = array_search($pos, self::$sends[$player->getName()]);
            if ($index !== false) {
                unset(self::$sends[$player->getName()][$index]);
                foreach ($player->getWorld()->createBlockUpdatePackets([$pos]) as $packet) $player->getNetworkSession()->sendDataPacket($packet);
            }
        }
    }

    public static function removeAll(Player $player): void
    {
        if (isset(self::$sends[$player->getName()])) {
            foreach ($player->getWorld()->createBlockUpdatePackets(self::$sends[$player->getName()]) as $packet) $player->getNetworkSession()->sendDataPacket($packet);
            unset(self::$sends[$player->getName()]);
        }
    }
}
