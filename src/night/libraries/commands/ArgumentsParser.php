<?php

namespace night\libraries\commands;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;

final class ArgumentsParser
{
    private static ?self $instance = null;

    public static function get(): self
    {
        return (self::$instance ??= new self());
    }

    public static function parserBoolean(string $value, ?bool $default = false): ?bool
    {
        if (strtolower($value) === 'true') return true;
        elseif (strtolower($value) === 'false') return false;
        return $default;
    }

    public static function parserPosition(CommandSender $sender, int $index, array $args): ?Vector3
    {
        if (isset($args[$index])) {
            if ($args[$index] === '~~~') return ($sender instanceof Player ? $sender->getPosition() : null);
            if (!isset($args[$index + 2])) return null;
            $filter_coord = function (string|float|int $coord, string $type) use ($sender): int|float|string {
                $type_fn = fn(string $type, Vector3 $pos): float|int => match ($type) {
                    'x' => $pos->getX(),
                    'y' => $pos->getY(),
                    'z' => $pos->getZ(),
                    default => 0,
                };
                if (!is_numeric($coord)) {
                    if ($coord === '~' && $sender instanceof Player) {
                        $coord = $type_fn($type, $sender->getPosition());
                    } elseif (str_contains($coord, '~') && $sender instanceof Player) {
                        $value = substr($coord, 1);
                        if (is_numeric($value)) {
                            $coord = $type_fn($type, $sender->getPosition());
                            if ($value <= -1) {
                                $coord -= abs((float)$value - 1);
                            } elseif ($value >= 0) {
                                $coord += (float)$value;
                            }
                        }
                    }
                }
                return $coord;
            };
            $x = $filter_coord($args[$index], 'x');
            $y = $filter_coord($args[$index + 1], 'y');
            $z = $filter_coord($args[$index + 2], 'z');
            foreach ([$x, $y, $z] as $coord) if (!is_numeric($coord)) return null;
            return new Vector3(floatval($x), floatval($y), floatval($z));
        }
        return null;
    }

    /**
     * @return Entity[]|Player|Player[]|CommandSender[]|CommandSender|null
     */
    public static function parserTarget(CommandSender $sender, string $selectType, bool $exact = false, bool $returnArray = false)
    {
        switch ($selectType) {
            case '@a':
                return Server::getInstance()->getOnlinePlayers();
                break;
            case '@e':
                $entities = [];
                foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) $entities = array_merge($entities, $world->getEntities());
                return $entities;
                break;
            case '@p':
                if (!$sender instanceof Player) return null;
                $chosen = null;
                $lastDistance = 0;
                foreach ($sender->getWorld()->getPlayers() as $p) {
                    $distance = $p->getPosition()->distance($sender->getPosition());
                    if ($p->getName() !== $sender->getName() && ($chosen === null || $distance <= $lastDistance)) {
                        $chosen = $p;
                    }
                }
                if ($chosen === null) $chosen = $sender;
                return $returnArray ? [$chosen] : $chosen;
                break;
            case '@r':
                $select = count($selects = Server::getInstance()->getOnlinePlayers()) > 0 ? $selects[array_rand($selects)] : null;
                return ($select === null ? null : ($returnArray ? [$select] : $select));
                break;
            case '@s':
                return ($returnArray ? [$sender] : $sender);
                break;
            default:
                $p = $exact ? Server::getInstance()->getPlayerExact($selectType) : Server::getInstance()->getPlayerByPrefix($selectType);
                if ($returnArray && !is_null($p)) $p = [$p];
                return $p;
                break;
        }
    }
}
