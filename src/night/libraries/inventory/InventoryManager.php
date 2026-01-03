<?php

namespace night\libraries\inventory;

use night\libraries\inventory\interfaces\FakeBlockInventory;
use night\libraries\inventory\interfaces\ListenerInventory;
use night\libraries\inventory\interfaces\TranslatableInventory;
use night\libraries\inventory\MenuInventory;
use night\libraries\inventory\utils\ListenerTransaction;
use night\libraries\managers\BaseManager;
use Closure;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

class InventoryManager extends BaseManager
{
    use SingletonTrait;

    private Closure $open_callback;
    private array $wait = [];

    public static function getInstance(): InventoryManager
    {
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct('InventoryManager');
        self::setInstance($this);
        $this->nullOpenCallback();

        $r_e = $this->getServer()->getPluginManager()->registerEvent(...);
        $r_e(InventoryTransactionEvent::class, $this->onInventoryTransaction(...), EventPriority::HIGHEST, $this->getLoader());
        $r_e(InventoryCloseEvent::class, $this->onInventoryClose(...), EventPriority::MONITOR, $this->getLoader());
        $r_e(InventoryOpenEvent::class, $this->onInventoryOpen(...), EventPriority::HIGHEST, $this->getLoader());
    }

    private function nullOpenCallback(): void
    {
        $this->open_callback = static fn(int $window_id, Inventory $inventory): ?array => null;
    }

    public function sendInventory(Player $player, Inventory $inventory): bool
    {
        if (isset($this->wait[$player->getName()])) return false;
        $callbacks = $player->getNetworkSession()->getInvManager()?->getContainerOpenCallbacks();
        if ($callbacks === null) return false;
        $this->wait[$player->getName()] = time();
        $callbacks->remove($this->open_callback);
        $previous = $callbacks->toArray();
        $callbacks->clear();
        $callbacks->add($this->open_callback = function (int $window_id, Inventory $inv) use ($inventory, $previous, $callbacks): ?array {
            $callbacks->remove($this->open_callback);
            $this->nullOpenCallback();
            if ($inv === $inventory) {
                $packets = null;
                foreach ($previous as $callback) {
                    $packets = $callback($window_id, $inv);
                    if ($packets !== null) break;
                }
                $packets ??= [ContainerOpenPacket::blockInv($window_id, WindowTypes::CONTAINER, ($inventory instanceof BlockInventory ? BlockPosition::fromVector3($inventory->getHolder()) : new BlockPosition(0, 0, 0)))];
                if ($inventory instanceof TranslatableInventory) foreach ($packets as $packet) if ($packet instanceof ContainerOpenPacket) $inventory->translate($packet);
                return $packets;
            }
            return null;
        }, ...$previous);
        $time = 10;
        if ($inventory instanceof MenuInventory) {
            if ($inventory->isDouble()) $time = 10;
            $inventory->show_menu($player);
        }
        if ($inventory instanceof FakeBlockInventory) $inventory->beforeOpeningInventory($player);
        $fn = fn() => $player->setCurrentWindow($inventory);
        $this->getLoader()->getScheduler()->scheduleDelayedTask(new ClosureTask($fn), $time);
        return true;
    }

    private function onInventoryOpen(InventoryOpenEvent $ev): void
    {
        $player = $ev->getPlayer();
        $inventory = $ev->getInventory();
        if ($inventory instanceof MenuInventory && $inventory->get_spawn_holder($player) === null) $ev->cancel();
    }

    private function onInventoryClose(InventoryCloseEvent $ev): void
    {
        $player = $ev->getPlayer();
        $inventory = $ev->getInventory();
        if (isset($this->wait[$player->getName()])) unset($this->wait[$player->getName()]);
        if ($inventory instanceof ListenerInventory) $inventory->close($player);
    }

    private function onInventoryTransaction(InventoryTransactionEvent $ev): void
    {
        $transaction = $ev->getTransaction();
        $player = $transaction->getSource();
        $inventory = $player->getCurrentWindow();
        if (!$inventory instanceof ListenerInventory) return;
        foreach ($transaction->getActions() as $action) {
            if (!($action instanceof SlotChangeAction) || $action->getInventory() !== $inventory) continue;
            if ($inventory->transaction(ListenerTransaction::create($player, $action->getSourceItem(), $action->getTargetItem(), $action, $transaction))) {
                $ev->cancel();
                break;
            }
        }
    }
}
