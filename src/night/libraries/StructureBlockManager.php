<?php

namespace night\libraries;

use night\libraries\managers\BaseDatManager;
use Exception;
use GlobalLogger;
use LogicException;
use pocketmine\block\Block;
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
use pocketmine\network\mcpe\convert\BlockStateDictionaryEntry;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\World;
use pocketmine\data\bedrock\block\convert\BlockStateToObjectDeserializer;
use pocketmine\math\Axis;
use pocketmine\nbt\tag\IntTag;

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
        $map = array_filter(TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->getStates(), fn(BlockStateDictionaryEntry $entry) => $entry->getStateName() === $data->getName())[0] ?? null;
        $valid = true;
        if ($map !==  null) foreach ($map->generateStateData()->getStates() as $name => $state) {
            if ($data->getState($name) === null) {
                $valid = false;
                break;
            }
        }
        return ($valid ? $data : ($map === null ? $data : $map->generateStateData()));
    }

    public static function apply_rotation(BlockStateData $data, int $rotation): BlockStateData
    {
        //trapdor = BlockStateNames::DIRECTION
        $states = $data->getStates();
        foreach ($states as $name => $tag) {
            if ($name === BlockStateNames::FACING_DIRECTION) {
                $states[$name] = new IntTag(match ($rotation) {
                    Facing::EAST => match ($tag->getValue()) {
                        Facing::NORTH => Facing::EAST,
                        Facing::EAST => Facing::SOUTH,
                        Facing::SOUTH => Facing::WEST,
                        Facing::WEST => Facing::NORTH,
                        default => $tag->getValue()
                    },
                    Facing::SOUTH => match ($tag->getValue()) {
                        Facing::NORTH => Facing::SOUTH,
                        Facing::EAST => Facing::WEST,
                        Facing::SOUTH => Facing::NORTH,
                        Facing::WEST => Facing::EAST,
                        default => $tag->getValue()
                    },
                    Facing::WEST => match ($tag->getValue()) {
                        Facing::NORTH => Facing::WEST,
                        Facing::EAST => Facing::NORTH,
                        Facing::SOUTH => Facing::EAST,
                        Facing::WEST => Facing::SOUTH,
                        default => $tag->getValue()
                    },
                    default => $tag->getValue()
                });
            }
            if ($name === BlockStateNames::PILLAR_AXIS or $name === BlockStateNames::PORTAL_AXIS) {
                $axis = $tag->getValue();
                if ($axis === Axis::X || $axis === Axis::Z) {
                    $states[$name] = new IntTag(match ($rotation) {
                        Facing::EAST, Facing::WEST => $axis === Axis::X ? Axis::Z : Axis::X,
                        default => $axis,
                    });
                }
            }
            if ($name === BlockStateNames::ROTATION) {
                $rotationValue = $tag->getValue();
                $step = match ($rotation) {
                    Facing::EAST => 4,
                    Facing::SOUTH => 8,
                    Facing::WEST => 12,
                    default => 0
                };
                $states[$name] = new IntTag('rotation', ($rotationValue + $step) % 16);
            }
            /*if ($states->hasTag('shape')) {
                $shape = $states->getString('shape');
                $states->setTag(new StringTag('shape', match ($rotation) {
                    Facing::EAST => match ($shape) {
                        'straight' => 'straight',
                        'inner_left' => 'inner_right',
                        'inner_right' => 'inner_left',
                        'outer_left' => 'outer_right',
                        'outer_right' => 'outer_left',
                        default => $shape
                    },
                    Facing::SOUTH => match ($shape) {
                        'inner_left' => 'inner_right',
                        'inner_right' => 'inner_left',
                        'outer_left' => 'outer_right',
                        'outer_right' => 'outer_left',
                        default => $shape
                    },
                    Facing::WEST => match ($shape) {
                        'inner_left' => 'inner_right',
                        'inner_right' => 'inner_left',
                        'outer_left' => 'outer_right',
                        'outer_right' => 'outer_left',
                        default => $shape
                    },
                    default => $shape
                }));
            }

            // Puertas y trampillas: hinge y open
            if ($states->hasTag('hinge')) {
                $hinge = $states->getString('hinge');
                $states->setTag(new StringTag('hinge', $hinge === 'left' ? 'right' : 'left'));
            }

            if ($states->hasTag('open')) {
                $open = $states->getString('open');
                // open generalmente es booleano (en string)
                $states->setTag(new StringTag('open', $open)); // mantener igual, salvo que se quiera forzar
            }*/
        }
        return new BlockStateData($data->getName(), $states, $data->getVersion());
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
        $btt = new TileTransaction($world);

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
        $btt = new TileTransaction($world);

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
                        self::apply_rotation(self::FixMisingProperty(
                            GlobalBlockStateHandlers::getUpgrader()->upgradeBlockStateNbt($block_nbt)
                        ), $rotation)
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
}
