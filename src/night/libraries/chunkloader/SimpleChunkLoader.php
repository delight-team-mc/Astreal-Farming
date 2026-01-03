<?php

namespace night\libraries\chunkloader;

use Closure;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;

class SimpleChunkLoader extends BaseChunkLoader
{
    private ?Closure $chunk_changed = null, $chunk_loaded = null, $chunk_unloaded = null, $chunk_populated = null, $block_changed = null;

    public function setChunkChangedClosure(?Closure $closure): void
    {
        $this->chunk_changed = $closure;
    }

    public function setChunkLoadedClosure(?Closure $closure): void
    {
        $this->chunk_loaded = $closure;
    }

    public function setChunkUnLoadedClosure(?Closure $closure): void
    {
        $this->chunk_unloaded = $closure;
    }

    public function setChunkPopulatedClosure(?Closure $closure): void
    {
        $this->chunk_populated = $closure;
    }

    public function setBlockChangedClosure(?Closure $closure): void
    {
        $this->block_changed = $closure;
    }

    public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        $closure = $this->chunk_changed;
        if ($closure instanceof Closure) $closure($this, $chunk);
    }

    public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        $closure = $this->chunk_loaded;
        if ($closure instanceof Closure) $closure($this, $chunk);
    }

    public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        $closure = $this->chunk_unloaded;
        if ($closure instanceof Closure) $closure($this);
    }

    public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        $closure = $this->chunk_populated;
        if ($closure instanceof Closure) $closure($this, $chunk);
    }

    public function onBlockChanged(Vector3 $block): void
    {
        $closure = $this->block_changed;
        if ($closure instanceof Closure) $closure($this, $block);
    }
}
