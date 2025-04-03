<?php

namespace astreal\system;

use astreal\libraries\managers\BaseDatManager;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
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
        }
    }
}
class Session
{
    public function __construct(private CompoundTag $root) {}
}
