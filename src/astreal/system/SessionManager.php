<?php

namespace astreal\system;

use astreal\libraries\managers\BaseDatManager;
use astreal\libraries\TranslateMessage;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

class SessionManager extends BaseDatManager
{
    use SingletonTrait {
        getInstance as SI;
    }

    /** @var Session[] */
    private array $sessions = [];

    public static function getInstance(): SessionManager
    {
        return self::SI();
    }

    public function __construct(string $path)
    {
        parent::__construct('SessionManager', $path, new BigEndianNbtSerializer(), '.session', true);
        self::setInstance($this);
        @mkdir($path);
        foreach (DataProvider::getInstance()->getFilesByPath($this->getPath()) as $file) {
            $session = new Session($this->load(str_replace($file->getExtension(), '', $file->getFilename())) ?? CompoundTag::create());
            $this->sessions[$session->getXuid()] = $session;
        }
        ($plugin_manager = $this->getServer()->getPluginManager())->registerEvent(PlayerJoinEvent::class, $this->__join(...), EventPriority::HIGHEST, $this->getLoader());
        $plugin_manager->registerEvent(PlayerQuitEvent::class, $this->__quit(...), EventPriority::HIGHEST, $this->getLoader());
    }

    private function __join(PlayerJoinEvent $ev): void
    {
        if (!$this->exits(($player = $ev->getPlayer())->getXuid())) {
            $session = Session::create($player);
            $session->save();
            $this->sessions[$session->getXuid()] = $session;
        }
        $session = $this->getSessionByXuid($player->getXuid());
        $session->setPlayer($player);
    }

    private function __quit(PlayerQuitEvent $ev): void
    {
        if ($this->exits(($player = $ev->getPlayer())->getXuid())) $this->getSessionByXuid($player->getXuid())?->save();
    }

    public function getSessionByXuid(string $xuid): ?Session
    {
        return $this->sessions[$xuid] ?? null;
    }

    public function exits(string $xuid): bool
    {
        return isset($this->sessions[$xuid]);
    }

    public function saveAll(): void
    {
        foreach ($this->sessions as $session) $this->save($session->getXuid(), $session->getRoot());
    }
}
class Session
{
    private Player $player;

    public static function create(Player $player): self
    {
        $root = CompoundTag::create();
        $root->setString('Xuid', $player->getXuid());
        $root->setString('Name', $player->getName());
        $root->setString('Date', date("d/m/Y H:i:s"));
        $root->setString('lang', TranslateMessage::EN_US);
        return new self($root);
    }

    public function __construct(private CompoundTag $root) {}

    public function getXuid(): string
    {
        return $this->root->getString('Xuid', '');
    }

    public function getName(): string
    {
        return $this->root->getString('Name', '');
    }

    public function getDate(): string
    {
        return $this->root->getString('Date', '');
    }

    public function setLang(string $lang): void
    {
        $this->root->setString('Lang', $lang);
    }

    public function getLang(): string
    {
        return $this->root->getString('Lang', TranslateMessage::EN_US);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;
        return $this;
    }

    public function getRoot(): CompoundTag
    {
        return $this->root;
    }

    public function save(): void
    {
        SessionManager::getInstance()->save($this->getXuid(), $this->getRoot());
    }
}
