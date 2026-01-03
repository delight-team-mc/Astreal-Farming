<?php

namespace night\libraries;

use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\TileFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class TileTransaction
{
    private array $blocks = [];

    public function __construct(private ChunkManager $world) {}

    public function addTile(Vector3 $pos, CompoundTag $nbt): self
    {
        return $this->addTileAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $nbt);
    }

    public function addTileAt(int $x, int $y, int $z, CompoundTag $nbt): self
    {
        $this->blocks[$x][$y][$z] = $nbt;
        return $this;
    }

    public function apply(): bool
    {
        if (!$this->world instanceof World) return false;
        $changedTiles = 0;
        foreach ($this->getTiles() as [$x, $y, $z, $nbt]) {
            $oldTile = $this->world->getTileAt($x, $y, $z);
            try {
                $tile = TileFactory::getInstance()->createFromData($this->world, $nbt);
                if ($oldTile instanceof $tile) {
                    if ($oldTile instanceof Spawnable) $oldTile->clearSpawnCompoundCache();
                    $oldTile->readSaveData($nbt);
                    $changedTiles++;
                }
            } catch (\Throwable $e) {
            }
        }
        return $changedTiles !== 0;
    }

    public function getTiles(): \Generator
    {
        foreach ($this->blocks as $x => $yLine) {
            foreach ($yLine as $y => $zLine) {
                foreach ($zLine as $z => $block) {
                    yield [$x, $y, $z, $block];
                }
            }
        }
    }
}
