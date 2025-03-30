<?php

namespace astreal;

use astreal\libraries\inventory\InventoryManager;
use astreal\libraries\inventory\MenuInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;

class AstrealListener implements Listener
{

    public function onPlayerItemUse(PlayerItemUseEvent $ev): void
    {
        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $nbt = $item->getCustomBlockData();
        if ($nbt === null) return;
        $backpack = $nbt->getCompoundTag('BackPack');
        if ($backpack == null) return;
        $inventory = new MenuInventory(27, VanillaBlocks::CHEST(), WindowTypes::CONTAINER);
        $inventory->setCloseListener(function (Player $player, MenuInventory $inventory) use ($item, $nbt, $backpack) {
            $items = [];
            foreach ($inventory->getContents() as $slot => $item) {
                $items[] = $item->nbtSerialize($slot);
            }
            $nbt->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));
            $item->setNamedTag(CompoundTag::create()->setTag("BlockEntityTag", $nbt)->setTag('BackPack', $backpack));
        });
        if (($inventoryTag = $nbt->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound) {
            $listeners = $inventory->getListeners()->toArray();
            $inventory->getListeners()->remove(...$listeners);
            $newContents = [];
            /** @var CompoundTag $itemNBT */
            foreach ($inventoryTag as $itemNBT) {
                try {
                    $newContents[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($itemNBT);
                } catch (SavedDataLoadingException $e) {
                    \GlobalLogger::get()->logException($e);
                    continue;
                }
            }
            $inventory->setContents($newContents);
            $inventory->getListeners()->add(...$listeners);
        }
        InventoryManager::getInstance()->sendInventory($player, $inventory);
    }

    public function onPlayerJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setJoinMessage("§7[§a+§7]§2 " . $player->getName() . " §7joined the server.");
    }


    public function onPlayerQuit(PlayerQuitEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setQuitMessage("§7[§c-§7]§6 " . $player->getName() . " §7left the server.");
    }
}
