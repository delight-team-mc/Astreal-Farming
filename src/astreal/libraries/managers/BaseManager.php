<?php

namespace astreal\libraries\managers;

use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\Server;
use PrefixedLogger;
use astreal\Loader;

class BaseManager
{
    private PrefixedLogger $logger;

    public function __construct(private string $name)
    {
        $this->logger = new PrefixedLogger($this->getServer()->getLogger(), $name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogger(): PrefixedLogger
    {
        return $this->logger;
    }

    public function getServer(): Server
    {
        return Server::getInstance();
    }

    public function getLoader(): Loader
    {
        return Loader::getInstance();
    }

    public function registerEvent(string $event, \Closure $handler, int $priority = EventPriority::NORMAL): void
    {
        $this->getServer()->getPluginManager()->registerEvent($event, $handler, $priority, $this->getLoader());
    }

    public function registerEvents(Listener $listener): void
    {
        $this->getServer()->getPluginManager()->registerEvents($listener, $this->getLoader());
    }
}
