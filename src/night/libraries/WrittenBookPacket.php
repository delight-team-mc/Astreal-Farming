<?php

namespace night\libraries;

use pocketmine\item\WrittenBook;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\PredictedResult;
use pocketmine\network\mcpe\protocol\types\inventory\TriggerType;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\player\Player;

class WrittenBookPacket
{

    public static function send(Player $player, WrittenBook $book_original): void
    {
        ($book = clone $book_original)->setCustomName("");
        $oldItem = $player->getInventory()->getItemInHand();
        $networkSession = $player->getNetworkSession();
        $player->getInventory()->setItemInHand($book);
        $networkSession->getInvManager()->syncSlot(
            $player->getInventory(),
            $player->getInventory()->getHeldItemIndex(),
            $networkSession->getTypeConverter()->coreItemStackToNet($book)
        );

        $networkSession->sendDataPacket(InventoryTransactionPacket::create(
            0,
            [],
            UseItemTransactionData::new(
                [],
                UseItemTransactionData::ACTION_CLICK_AIR,
                TriggerType::PLAYER_INPUT,
                new BlockPosition(0, 0, 0),
                0,
                $player->getInventory()->getHeldItemIndex(), //0
                ItemStackWrapper::legacy(ItemStack::null()),
                Vector3::zero(),
                Vector3::zero(),
                0,
                PredictedResult::SUCCESS
            )
        ));
        $player->getInventory()->setItemInHand($oldItem);
    }
}
