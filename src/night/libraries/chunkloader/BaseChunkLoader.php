<?php

namespace night\libraries\chunkloader;

use pocketmine\math\Vector3;
use pocketmine\world\{ChunkLoader, ChunkListener};
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

abstract class BaseChunkLoader implements ChunkListener, ChunkLoader
{
    public function __construct(private World $world, private int $chunkX, private int $chunkZ, private int $x, private int $z) {}

    final public function closeChunkLoader()
    {
        $this->world->unregisterChunkLoader($this, $this->chunkX, $this->chunkZ);
        $this->world->unregisterChunkListener($this, $this->chunkX, $this->chunkZ);
    }

    public function getChunkX(): int
    {
        return $this->chunkX;
    }

    public function getChunkZ(): int
    {
        return $this->chunkZ;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getZ(): int
    {
        return $this->z;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    abstract public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk): void;
    abstract public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk): void;
    abstract public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk): void;
    abstract public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk): void;
    abstract public function onBlockChanged(Vector3 $block): void;
}
