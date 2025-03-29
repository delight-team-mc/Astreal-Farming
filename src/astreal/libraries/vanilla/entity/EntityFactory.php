<?php
/*
 * 
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║   
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║   
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║   
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝   
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 */

namespace astreal\libraries\vanilla\entity;

use pocketmine\entity\EntityFactory as PmmPEntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\utils\SingletonTrait;

class EntityFactory
{
    use SingletonTrait {
        getInstance as SgetInstance;
    }

    public static function getInstance(): EntityFactory
    {
        return self::SgetInstance();
    }

    public function register_vanilla(string $className, \Closure $creationFunc, array $saveNames): void
    {
        PmmPEntityFactory::getInstance()->register($className, $creationFunc, $saveNames);
    }

    public function register(string $network_type_id, bool $hasspawnegg, string $className, \Closure $creationFunc, array $saveNames): void
    {
        PmmPEntityFactory::getInstance()->register($className, $creationFunc, $saveNames);
        $instance = StaticPacketCache::getInstance();
        $staticPacketCache = new \ReflectionClass($instance);
        $property = $staticPacketCache->getProperty("availableActorIdentifiers");
        $property->setAccessible(true);
        /** @var AvailableActorIdentifiersPacket $packet */
        $packet = $property->getValue($instance);
        /** @var CompoundTag $root */
        $root = $packet->identifiers->getRoot();
        $idList = $root->getListTag("idlist") ?? new ListTag();
        $idList->push(CompoundTag::create()->setString('id', $network_type_id)->setString('bid', '')->setByte('hasspawnegg', $hasspawnegg ? 1 : 0));
        $packet->identifiers = new CacheableNbt($root);
    }
}
