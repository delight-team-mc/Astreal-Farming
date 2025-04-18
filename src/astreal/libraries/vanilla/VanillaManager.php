<?php

namespace astreal\libraries\vanilla;

use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ItemRegistryPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use astreal\libraries\managers\BaseManager;
use astreal\libraries\vanilla\block\BlockFactory;
use astreal\libraries\vanilla\item\ItemFactory;

class VanillaManager extends BaseManager
{
	use SingletonTrait {
		getInstance as SgetInstance;
	}

	/** @var ItemTypeEntry[] */
	private array $cachedItemTable = [];
	/** @var BlockPaletteEntry[] */
	private array $cachedBlockPalette = [];
	private Experiments $experiments;

	public static function getInstance(): VanillaManager
	{
		return self::SgetInstance();
	}

	public function __construct()
	{
		parent::__construct('VanillaManager');
		$this->experiments = new Experiments([
			"data_driven_items" => true,
			"upcoming_creator_features" => true,
		], true);
		$this->registerEvent(DataPacketSendEvent::class, $this->onDataPacketSend(...));
		$cachePath = $this->getLoader()->getDataFolder() . "idcache";
		$this->getLoader()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($cachePath): void {
			BlockFactory::getInstance()->addWorkerInitHook($cachePath);
			VanillaManager::getInstance()->getLogger()->notice('Loading BlockFactory');
		}), 0);
	}

	private function onDataPacketSend(DataPacketSendEvent $event): void
	{
		foreach ($event->getPackets() as $packet) {
			if ($packet instanceof ItemRegistryPacket) {
				$reflec = new \ReflectionClass(ItemRegistryPacket::class);
				$entries_p = $reflec->getProperty('entries');
				$entries_p->setAccessible(true);
				$entries = $entries_p->getValue($packet);
				$entries_p->setValue($packet, array_merge($entries, ItemFactory::getInstance()->getItemTableEntries()));
			}
			if ($packet instanceof StartGamePacket) {
				if (count($this->cachedBlockPalette) === 0) {
					$this->cachedBlockPalette = BlockFactory::getInstance()->getBlockPaletteEntries();
				}
				$packet->levelSettings->experiments = $this->experiments;
				$packet->blockPalette = $this->cachedBlockPalette;
			} elseif ($packet instanceof ResourcePackStackPacket) {
				$packet->experiments = $this->experiments;
			}
		}
	}
}
