<?php

namespace astreal\libraries;

use astreal\libraries\managers\BaseDatManager;
use Exception;
use GlobalLogger;
use LogicException;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\TileFactory;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\block\convert\UnsupportedBlockStateException;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\World;

class StructureBlockManager extends BaseDatManager
{
    use SingletonTrait {
        getInstance as IS;
    }

    public static function getInstance(): StructureBlockManager
    {
        return self::IS();
    }

    public function __construct(string $path)
    {
        parent::__construct('StrcutureBlock', $path, new LittleEndianNbtSerializer(), '.mcstructure', false);
        self::setInstance($this);
        @mkdir($path);
    }

    private static function FixMisingProperty(BlockStateData $data): BlockStateData
    {
        switch ($data->getName()) {
            case BlockTypeNames::CHAIN:
                if ($data->getState(BlockStateNames::PILLAR_AXIS) === null) {
                    $nbt = $data->toVanillaNbt();
                    $nbt->getCompoundTag('states')->setString(BlockStateNames::PILLAR_AXIS, 'y');
                    $data = BlockStateData::fromNbt($nbt);
                }
                break;
        }
        return $data;
    }

    public static function load_tehread(string $file, $compressed = false): ?CompoundTag
    {
        if (!file_exists($file)) return null;
        try {
            $raw = Filesystem::fileGetContents($file);
        } catch (\RuntimeException $e) {
            throw new Exception("Failed to read data file \"$file\": " . $e->getMessage(), 0, $e);
        }
        try {
            $decompressed = $compressed ? ErrorToExceptionHandler::trapAndRemoveFalse(fn() => zlib_decode($raw)) : $raw;
        } catch (\ErrorException $e) {
            throw new Exception("Failed to decompress raw data for \"$file\": " . $e->getMessage(), 0, $e);
        }

        try {
            return (new LittleEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
        } catch (NbtDataException $e) {
            throw new Exception("Failed to decode NBT data for \"$file\": " . $e->getMessage(), 0, $e);
        }
    }

    public static function load_structure_thread(string $name, ChunkManager|World $world, Vector3 $position, int $rotation = Facing::NORTH, bool $tiles = false): void
    {
        if (!file_exists($name)) {
            throw new LogicException("El archivo {$name} no existe.");
            return;
        }

        $nbt = self::load_tehread($name);
        $structure = $nbt->getCompoundTag('structure');
        if ($structure === null) {
            throw new NbtException("No se encontró la estructura en el archivo.");
            return;
        }

        $sizeTag = $nbt->getListTag('size');
        if ($sizeTag === null || count($sizeTag->getAllValues()) !== 3) {
            throw new NbtException("Error al leer el tamaño de la estructura.");
            return;
        }

        $size = $sizeTag->getAllValues();
        $palette = $structure->getCompoundTag('palette');
        $palette_default = $palette->getCompoundTag('default');
        $block_palette = $palette_default->getListTag('block_palette');
        $block_position_data = $palette_default->getCompoundTag('block_position_data');
        $blockIndices = $structure->getListTag('block_indices');

        if ($blockIndices === null) {
            throw new NbtException("No se encontró 'block_indices' en el archivo NBT.");
            return;
        }

        $bt = new BlockTransaction($world);
        $btt = new class($world) {
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
        };

        foreach ($blockIndices->getValue() as $list) {
            $indexCounter = 0;
            foreach ($list->getValue() as $tag) {
                $index = $tag->getValue();
                if ($index === -1) {
                    $indexCounter++;
                    continue;
                }

                $z = (int)($indexCounter % $size[2]);
                $y = (int)(floor($indexCounter / $size[2]) % $size[1]);
                $x = (int)($indexCounter / ($size[2] * $size[1]));
                $indexCounter++;
                [$newX, $newZ] = match ($rotation) {
                    Facing::EAST => [$z, -$x],   // 90°
                    Facing::SOUTH => [-$x, -$z], // 180°
                    Facing::WEST => [-$z, $x],   // 270°
                    default => [$x, $z],         // Sin rotación
                };

                try {
                    /** @var CompoundTag $block_nbt */
                    $block_nbt = $block_palette->get($index);
                    $block = GlobalBlockStateHandlers::getDeserializer()->deserializeBlock(
                        self::FixMisingProperty(
                            GlobalBlockStateHandlers::getUpgrader()->upgradeBlockStateNbt($block_nbt)
                        )
                    );

                    $bt->addBlock($position->add($newX, $y, $newZ), $block);
                    $block_entity_data = $block_position_data->getCompoundTag((string)($indexCounter - 1));

                    if ($block_entity_data !== null && $tiles) {
                        $nbt = $block_entity_data->getCompoundTag('block_entity_data');
                        if ($nbt !== null) {
                            $btt->addTile($position->add($newX, $y, $newZ), $nbt);
                        }
                    }
                } catch (\Throwable $th) {
                    GlobalLogger::get()->error((new UnsupportedBlockStateException(
                        $block_nbt->getString('name') . ' invalid data: ' . $th->getMessage(),
                        0
                    ))->getMessage());

                    $bt->addBlock($position->add($newX, $y, $newZ), GlobalBlockStateHandlers::getDeserializer()->deserializeBlock(GlobalBlockStateHandlers::getUnknownBlockStateData()));
                }
            }
        }

        $bt->apply();
        $btt->apply();
    }

    public function load_structure(string $name, ChunkManager|World $world, Vector3 $position, int $rotation = Facing::NORTH, bool $tiles = false): void
    {
        if (!$this->has($name)) {
            throw new LogicException("El archivo {$name} no existe.");
            return;
        }

        $nbt = $this->load($name);
        $structure = $nbt->getCompoundTag('structure');
        if ($structure === null) {
            throw new NbtException("No se encontró la estructura en el archivo.");
            return;
        }

        $sizeTag = $nbt->getListTag('size');
        if ($sizeTag === null || count($sizeTag->getAllValues()) !== 3) {
            throw new NbtException("Error al leer el tamaño de la estructura.");
            return;
        }

        $size = $sizeTag->getAllValues();
        $palette = $structure->getCompoundTag('palette');
        $palette_default = $palette->getCompoundTag('default');
        $block_palette = $palette_default->getListTag('block_palette');
        $block_position_data = $palette_default->getCompoundTag('block_position_data');
        $blockIndices = $structure->getListTag('block_indices');

        if ($blockIndices === null) {
            throw new NbtException("No se encontró 'block_indices' en el archivo NBT.");
            return;
        }

        $bt = new BlockTransaction($world);
        $btt = new class($world) {
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
        };

        foreach ($blockIndices->getValue() as $list) {
            $indexCounter = 0;
            foreach ($list->getValue() as $tag) {
                $index = $tag->getValue();
                if ($index === -1) {
                    $indexCounter++;
                    continue;
                }

                $z = (int)($indexCounter % $size[2]);
                $y = (int)(floor($indexCounter / $size[2]) % $size[1]);
                $x = (int)($indexCounter / ($size[2] * $size[1]));
                $indexCounter++;
                [$newX, $newZ] = match ($rotation) {
                    Facing::EAST => [$z, -$x],   // 90°
                    Facing::SOUTH => [-$x, -$z], // 180°
                    Facing::WEST => [-$z, $x],   // 270°
                    default => [$x, $z],         // Sin rotación
                };

                try {
                    /** @var CompoundTag $block_nbt */
                    $block_nbt = $block_palette->get($index);
                    $block = GlobalBlockStateHandlers::getDeserializer()->deserializeBlock(
                        self::FixMisingProperty(
                            GlobalBlockStateHandlers::getUpgrader()->upgradeBlockStateNbt($block_nbt)
                        )
                    );

                    $bt->addBlock($position->add($newX, $y, $newZ), $block);
                    $block_entity_data = $block_position_data->getCompoundTag((string)($indexCounter - 1));

                    if ($block_entity_data !== null && $tiles) {
                        $nbt = $block_entity_data->getCompoundTag('block_entity_data');
                        if ($nbt !== null) {
                            $btt->addTile($position->add($newX, $y, $newZ), $nbt);
                        }
                    }
                } catch (\Throwable $th) {
                    $this->getLogger()->error((new UnsupportedBlockStateException(
                        $block_nbt->getString('name') . ' invalid data: ' . $th->getMessage(),
                        0
                    ))->getMessage());

                    $bt->addBlock($position->add($newX, $y, $newZ), GlobalBlockStateHandlers::getDeserializer()->deserializeBlock(GlobalBlockStateHandlers::getUnknownBlockStateData()));
                }
            }
        }

        $bt->apply();
        $btt->apply();
    }


    /*
    public function load_structure(string $name, World $world, Vector3 $position): void
    {
        if (!$this->has($name)) {
            throw new LogicException("El archivo {$name} no existe.");
            return;
        }
        $nbt = $this->load($name);
        $structure = $nbt->getCompoundTag('structure');
        if ($structure === null) {
            throw new NbtException("No se encontró la estructura en el archivo.");
            return;
        }
        $sizeTag = $nbt->getListTag('size');
        if ($sizeTag === null || count($sizeTag->getAllValues()) !== 3) {
            throw new NbtException("Error al leer el tamaño de la estructura.");
            return;
        }
        $size = $sizeTag->getAllValues();
        $palette = $structure->getCompoundTag('palette');
        $palette_default = $palette->getCompoundTag('default');
        $block_palette = $palette_default->getListTag('block_palette');
        $block_position_data = $palette_default->getCompoundTag('block_position_data');
        $blockIndices = $structure->getListTag('block_indices');
        if ($blockIndices === null) {
            throw new NbtException("No se encontró 'block_indices' en el archivo NBT.");
            return;
        }
        $bt = new BlockTransaction($world);
        $btt = new class($world) {
            /**
             * @var CompoundTag[][][]
             */
    /*private array $blocks = [];

            public function __construct(private World $world) {}

            public function addTile(Vector3 $pos, CompoundTag $nbt): self
            {
                return $this->addTileAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $nbt);
            }

            /**
             * @return $this
             */
    /*public function addTileAt(int $x, int $y, int $z, CompoundTag $nbt): self
            {
                $this->blocks[$x][$y][$z] = $nbt;
                return $this;
            }

            public function apply(): bool
            {
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
        };
        foreach ($blockIndices->getValue() as $list) {
            $indexCounter = 0;
            foreach ($list->getValue() as $tag) {
                $index = $tag->getValue();
                if ($index === -1) {
                    $indexCounter++;
                    continue;
                }
                $z = (int)($indexCounter % $size[2]);
                $y = (int)(floor($indexCounter / $size[2]) % $size[1]);
                $x = (int)($indexCounter / ($size[2] * $size[1]));
                $indexCounter++;
                try {
                    /** @var CompoundTag $block_nbt */
    /*$block_nbt = $block_palette->get($index);
                    $block = GlobalBlockStateHandlers::getDeserializer()->deserializeBlock($this->FixMisingProperty(GlobalBlockStateHandlers::getUpgrader()->upgradeBlockStateNbt($block_nbt)));
                    $bt->addBlock($position->add($x, $y, $z), $block);
                    $block_entity_data = $block_position_data->getCompoundTag((string)$indexCounter - 1);
                    if ($block_entity_data !== null) {
                        $nbt = $block_entity_data->getCompoundTag('block_entity_data');
                        if ($nbt !== null) {
                            $btt->addTile($position->add($x, $y, $z), $nbt);
                        }
                    }
                } catch (\Throwable $th) {
                    $this->getLogger()->error((new UnsupportedBlockStateException($block_nbt->getString('name') . ' invalid data: ' . $th->getMessage(), 0))->getMessage());
                    //throw new UnsupportedBlockStateException($block_nbt->getString('name') . ' invalid data: ' . $th->getMessage(), 0 );
                    $bt->addBlock($position->add($x, $y, $z), GlobalBlockStateHandlers::getDeserializer()->deserializeBlock(GlobalBlockStateHandlers::getUnknownBlockStateData()));
                }
            }
        }
        $bt->apply();
        $btt->apply();
    }*/
}
