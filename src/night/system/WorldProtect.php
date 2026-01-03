<?php

namespace night\system;

use night\libraries\managers\BaseManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;

class WorldProtect extends BaseManager
{
    use SingletonTrait {
        getInstance as IS;
    }

    /** @var Protect[] */
    private array $protects = [];

    public static function getInstance(): self
    {
        return self::IS();
    }

    public function __construct()
    {
        parent::__construct('WorldProtect');
        self::setInstance($this);
        $this->getServer()->getPluginManager()->registerEvent(WorldLoadEvent::class, function (WorldLoadEvent $ev) {
            $world = $ev->getWorld();
            $this->protects[$world->getFolderName()] = new Protect($world);
            CommandManager::getInstance()->getCommand('worldprotect')?->update_enums();
        }, 0, $this->getLoader());
        $this->getServer()->getPluginManager()->registerEvent(WorldUnloadEvent::class, function (WorldUnloadEvent $ev) {
            $world = $ev->getWorld();
            $this->protects[$world->getFolderName()]?->save();
            CommandManager::getInstance()->getCommand('worldprotect')?->update_enums();
        }, 0, $this->getLoader());
        $this->getLoader()->getScheduler()->scheduleDelayedTask(new ClosureTask($this->search_worlds(...)), 0);
        $r = $this->getServer()->getPluginManager()->registerEvent(...);
        $r(PlayerInteractEvent::class, $this->event_interact(...), EventPriority::NORMAL, $this->getLoader());
        $r(PlayerExhaustEvent::class, $this->event_hunger(...), EventPriority::NORMAL, $this->getLoader());
        $r(EntityDamageEvent::class, $this->event_damage(...), EventPriority::NORMAL, $this->getLoader());
        $r(PlayerDropItemEvent::class, $this->event_drop(...), EventPriority::NORMAL, $this->getLoader());
        $r(BlockBreakEvent::class, $this->event_break(...), EventPriority::NORMAL, $this->getLoader());
        $r(BlockPlaceEvent::class, $this->event_place(...), EventPriority::NORMAL, $this->getLoader());
    }

    private function search_worlds(): void
    {
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) $this->protects[$world->getFolderName()] = new Protect($world);
    }

    public function createProtect(World $world): Protect
    {
        $this->protects[$world->getFolderName()] = new Protect($world);
        return $this->protects[$world->getFolderName()];
    }

    public function getProtectByWorld(World $world): ?Protect
    {
        return $this->protects[$world->getFolderName()] ?? null;
    }

    public function getProtectByName(string $folder): ?Protect
    {
        return $this->protects[$folder] ?? null;
    }

    private function event_drop(PlayerDropItemEvent $ev): void
    {
        $player = $ev->getPlayer();
        $protect = $this->getProtectByWorld($player->getWorld());
        if ($protect === null or $protect->getFlag(Protect::DROP_FLAG)) return;
        $ev->cancel();
    }

    private function event_interact(PlayerInteractEvent $ev): void
    {
        $player = $ev->getPlayer();
        $protect = $this->getProtectByWorld($player->getWorld());
        if ($protect === null or $protect->getFlag(Protect::INTERACT_FLAG)) return;
        $ev->cancel();
    }

    private function event_hunger(PlayerExhaustEvent $ev): void
    {
        $player = $ev->getPlayer();
        $protect = $this->getProtectByWorld($player->getWorld());
        if ($protect === null or $protect->getFlag(Protect::HUNGER_FLAG)) return;
        $ev->cancel();
    }

    private function event_damage(EntityDamageEvent $ev): void
    {
        $entity = $ev->getEntity();
        $protect = $this->getProtectByWorld($entity->getWorld());
        if ($protect === null or $protect->getFlag(Protect::DAMAGE_FLAG)) {
            if (!$ev instanceof EntityDamageByEntityEvent) return;
            if (!$ev->getDamager() instanceof Player) return;
            if ($protect->getFlag(Protect::PVP_FLAG)) return;
        }
        $ev->cancel();
    }

    private function event_break(BlockBreakEvent $ev): void
    {
        $block = $ev->getBlock();
        $protect = $this->getProtectByWorld($block->getPosition()->getWorld());
        if ($protect === null or $protect->getFlag(Protect::BREAK_FLAG)) return;
        if (!in_array(GlobalBlockStateHandlers::getSerializer()->serializeBlock($block)->getName(), $protect->getListFlag(Protect::BLOCK_BREAK_LIST))) $ev->cancel();
    }

    private function event_place(BlockPlaceEvent $ev): void
    {
        $block = $ev->getBlockAgainst();
        $protect = $this->getProtectByWorld($block->getPosition()->getWorld());
        if ($protect === null or $protect->getFlag(Protect::PLACE_FLAG)) return;
        foreach ($ev->getTransaction()->getBlocks() as [$x, $y, $z, $block]) if (!in_array(GlobalBlockStateHandlers::getSerializer()->serializeBlock($block)->getName(), $protect->getListFlag(Protect::BLOCK_PLACE_LIST))) $ev->cancel();
    }
}
class Protect
{
    const BLOCK_PLACE_LIST = 'block_place_list';
    const BLOCK_BREAK_LIST = 'block_break_list';
    const INTERACT_FLAG = 'interact';
    const HUNGER_FLAG = 'hunger';
    const DAMAGE_FLAG = 'damage';
    const BREAK_FLAG = 'break';
    const PLACE_FLAG = 'place';
    const DROP_FLAG = 'drop';
    const PVP_FLAG = 'pvp';
    const AVAIABLES_FLAGS = [
        self::BLOCK_BREAK_LIST,
        self::BLOCK_PLACE_LIST,
        self::INTERACT_FLAG,
        self::HUNGER_FLAG,
        self::DAMAGE_FLAG,
        self::BREAK_FLAG,
        self::PLACE_FLAG,
        self::DROP_FLAG,
        self::PVP_FLAG
    ];
    private string $protect_file;
    private CompoundTag $nbt;

    public function __construct(private World $world)
    {
        $this->protect_file = Path::join(Server::getInstance()->getDataPath(), 'worlds', $world->getFolderName(), 'protect.dat');
        if (file_exists($this->protect_file)) $this->nbt = (new LittleEndianNbtSerializer())->read(Filesystem::fileGetContents($this->protect_file))->mustGetCompoundTag();
        else {
            $this->nbt = CompoundTag::create();
            foreach (self::AVAIABLES_FLAGS as $flag) $this->setFlag($flag, 1);
            $this->save();
        }
    }

    public function getRoot(): CompoundTag
    {
        return $this->nbt;
    }

    public function setRoot(CompoundTag $nbt): void
    {
        $this->nbt = $nbt;
    }

    public function getFlag(string $flag): bool
    {
        return $this->nbt->getByte($flag, 1) === 1;
    }

    public function setFlag(string $flag, bool $value): void
    {
        $this->nbt->setByte($flag, $value ? 1 : 0);
    }

    public function getListFlag(string $flag): array
    {
        return array_map(fn(Tag $tag) => $tag->getValue(), $this->nbt->getListTag($flag)?->getValue() ?? []);
    }

    public function save(): void
    {
        Filesystem::safeFilePutContents($this->protect_file, (new LittleEndianNbtSerializer())->write(new TreeRoot($this->nbt)));
    }
}
