<?php

namespace astreal;

use astreal\block\inventory\BackPackInventory;
use astreal\libraries\inventory\InventoryManager;
use pocketmine\block\tile\Container;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\Position;

class AstrealListener implements Listener
{

    public function onPlayerDropItem(PlayerDropItemEvent $ev): void
    {
        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $nbt = $item->getCustomBlockData();
        if ($nbt === null) return;
        $backpack = $nbt->getCompoundTag('BackPack');
        if ($backpack == null) return;
        if ($backpack->getByte('Open', 0) === 0) return;
        $ev->cancel();
    }

    public function onPlayerItemUse(PlayerItemUseEvent $ev): void
    {
        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $nbt = $item->getCustomBlockData();
        if (($backpack = ($nbt ??= CompoundTag::create())?->getCompoundTag('BackPack') ?? CompoundTag::create())?->getString('Uuid', '') === '') return;
        $backpack->setByte('Open', 1);
        $nbt->setTag('BackPack', $backpack);
        $item->setCustomBlockData($nbt);
        $player->getInventory()->setItemInHand($item);
        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $nbt, $backpack) {
            $uuid = $backpack->getString('Uuid', '');
            $match = current(array_filter($player->getInventory()->getContents(), fn(Item $i) => $i->getCustomBlockData()?->getCompoundTag('BackPack')?->getString('Uuid', '') === $uuid));
            if (!$match) return;
            $inventory = new BackPackInventory(Position::fromObject($player->getPosition()->down()->north(2)->floor(), $player->getWorld()));
            $inventory->setCloseListener(function (Player $player, BackPackInventory $inventory) use ($nbt, $backpack, $uuid) {
                $items = array_map(fn($c_item, $slot) => $c_item->nbtSerialize($slot), $inventory->getContents(), array_keys($inventory->getContents()));
                foreach ($player->getInventory()->getContents() as $slot => $macth) {
                    if ($macth->getCustomBlockData()?->getCompoundTag('BackPack')?->getString('Uuid', '') === $uuid) {
                        $backpack->removeTag('Open');
                        $nbt->setTag('BackPack', $backpack);
                        $nbt->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));
                        $macth->setCustomBlockData($nbt);
                        $player->getInventory()->setItem($slot, $macth);
                        return;
                    }
                }
            });
            if (($inventoryTag = $nbt->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound) {
                $newContents = [];
                foreach ($inventoryTag as $itemNBT) {
                    try {
                        $newContents[$itemNBT->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($itemNBT);
                    } catch (SavedDataLoadingException $e) {
                        \GlobalLogger::get()->logException($e);
                    }
                }
                $inventory->setContents($newContents);
            }
            InventoryManager::getInstance()->sendInventory($player, $inventory);
        }), 2);
    }


    /*public function onPlayerItemUse(PlayerItemUseEvent $ev): void
    {
        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $nbt = $item->getCustomBlockData();
        if ($nbt === null) return;
        $backpack = $nbt->getCompoundTag('BackPack');
        if ($backpack == null) return;
        $uuid = $backpack->getString('Uuid', '');
        if ($uuid === '') return;
        $backpack->setByte('Open', 1);
        $nbt->setTag('BackPack', $backpack);
        $item->setCustomBlockData($nbt);
        $player->getInventory()->setItemInHand($item);
        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $item, $nbt, $backpack, $uuid) {
            $items = array_filter($player->getInventory()->getContents(), function (Item $i) use ($uuid) {
                $nbt = $i->getCustomBlockData();
                if ($nbt === null) return false;
                $backpack = $nbt->getCompoundTag('BackPack');
                if ($backpack == null) return false;
                $uuid_e = $backpack->getString('Uuid', '');
                return $uuid_e === $uuid;
            });
            $macth = \array_shift($items);
            if ($macth === null) return;
            $inventory = new BackPackInventory(Position::fromObject($player->getPosition()->down()->north(2)->floor(), $player->getWorld()));
            $inventory->setCloseListener(function (Player $player, BackPackInventory $inventory) use ($item, $nbt, $backpack, $uuid) {
                $items = [];
                foreach ($inventory->getContents() as $slot => $c_item) {
                    $items[] = $c_item->nbtSerialize($slot);
                }
                for ($i = 0, $size = $player->getInventory()->getSize(); $i < $size; $i++) {
                    $macth = $player->getInventory()->getItem($i);
                    $nbt_ = $macth->getCustomBlockData();
                    if ($nbt_ === null) continue;
                    $backpack_ = $nbt_->getCompoundTag('BackPack');
                    if ($backpack_ == null) continue;
                    $uuid_e = $backpack_->getString('Uuid', '');
                    if ($uuid_e === $uuid) {
                        $backpack->removeTag('Open');
                        $nbt->setTag('BackPack', $backpack);
                        $nbt->setTag(Container::TAG_ITEMS, new ListTag($items, NBT::TAG_Compound));
                        $macth->setCustomBlockData($nbt);
                        $player->getInventory()->setItem($i, $macth);
                        return;
                    }
                }
            });
            if (($inventoryTag = $nbt->getTag(Container::TAG_ITEMS)) instanceof ListTag && $inventoryTag->getTagType() === NBT::TAG_Compound) {
                $listeners = $inventory->getListeners()->toArray();
                $inventory->getListeners()->remove(...$listeners);
                $newContents = [];
                /** @var CompoundTag $itemNBT *//*
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
        }), 2);
    }*/

    public function onPlayerJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setJoinMessage("§7[§a+§7]§2 " . $player->getName() . " §7joined the server.");
        $player->getNetworkSession()->sendDataPacket(\pocketmine\network\mcpe\protocol\GameRulesChangedPacket::create(["showcoordinates" => new \pocketmine\network\mcpe\protocol\types\BoolGameRule(true, false)]));
    }


    public function onPlayerQuit(PlayerQuitEvent $ev): void
    {
        $player = $ev->getPlayer();
        $ev->setQuitMessage("§7[§c-§7]§6 " . $player->getName() . " §7left the server.");
    }
}
