<?php

namespace night\block\utils;

use pocketmine\utils\LegacyEnumShimTrait;

/**
 * @method static GraveType STONE()
 * @method static GraveType GRAVEL()
 */
enum GraveType
{
    use LegacyEnumShimTrait;

    case STONE;
    case GRAVEL;

    private static function meta(string $displayName, string $graveValue): array
    {
        return [$displayName, $graveValue];
    }

    private function getMetadata(): array
    {
        static $cache = [];
        return $cache[spl_object_id($this)] ??= match ($this) {
            self::STONE => self::meta("Stone", "stone"),
            self::GRAVEL => self::meta("Gravel", "gravel"),
        };
    }

    public function getDisplayName(): string
    {
        return $this->getMetadata()[0];
    }

    public function getGraveValue(): string
    {
        return $this->getMetadata()[1];
    }
};
