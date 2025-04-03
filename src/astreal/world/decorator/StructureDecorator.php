<?php

namespace astreal\world\decorator;

use astreal\libraries\StructureBlockManager;
use astreal\world\decorator\types\StructureDecoration;
use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\format\Chunk;
use Symfony\Component\Filesystem\Path;

class StructureDecorator extends Decorator
{

    /** @var StructureDecoration[] */
    private array $structures = [];

    final public function setStructures(StructureDecoration ...$structures): void
    {
        $this->structures = $structures;
    }

    /**
     * @param StructureDecoration[] $decorations
     */
    private static function getRandomStructure(Random $random, array $decorations): ?StructureDecoration
    {
        $total_weight = 0;
        foreach ($decorations as $decoration) {
            $total_weight += $decoration->weight;
        }
        if ($total_weight > 0) {
            $weight = $random->nextBoundedInt($total_weight);
            foreach ($decorations as $decoration) {
                $weight -= $decoration->weight;
                if ($weight < 0) {
                    return $decoration;
                }
            }
        }
        return null;
    }

    public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk): void
    {
        $structure_decoration = $this->getRandomStructure($random, $this->structures);
        if ($structure_decoration !== null) {
            $source_y = $chunk->getHighestBlockAt($chunk_x, $chunk_z);
            $source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
            $source_z = $chunk_z << Chunk::COORD_BIT_SIZE;
            $facing = $structure_decoration->rotations[array_rand($structure_decoration->rotations)] ?? Facing::NORTH;
            if ($structure_decoration->structure_rule->canPlaceOn($world->getBlockAt($source_x, $source_y, $source_z))) {
                StructureBlockManager::load_structure_thread(Path::join(getcwd(), 'Behavior', 'structures', strtolower($structure_decoration->structure_path)), $world, ($pos = new Vector3($source_x, $source_y, $source_z)), $facing);
            }
        }
    }
}
