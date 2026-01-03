<?php

namespace night\listeners;

use pocketmine\event\Listener;
use pocketmine\event\world\ChunkPopulateEvent;

class StructureListener implements Listener
{

    public function onChunkPopulate(ChunkPopulateEvent $ev): void
    {
        $world = $ev->getWorld();
        $chunk = $ev->getChunk();
        $chunkX = $ev->getChunkX();
        $chunkZ = $ev->getChunkZ();
    }
}
