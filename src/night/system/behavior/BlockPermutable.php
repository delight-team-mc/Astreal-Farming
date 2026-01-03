<?php

namespace night\system\behavior;

use GlobalLogger;
use night\libraries\vanilla\block\permutations\BlockProperty;
use night\libraries\vanilla\block\permutations\Permutable;
use night\libraries\vanilla\block\permutations\Permutations;
use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\world\BlockTransaction;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\Player;

class BlockPermutable extends BlockSimple implements Permutable
{

    /**
     * @return BlockProperty[]
     */
    public function getBlockProperties(): array
    {
        return $this->behavior->getStates();
    }

    /**
     * @return Permutation[]
     */
    public function getPermutations(): array
    {
        return $this->behavior->getPermutations();
    }

    /*protected function writeStateToMeta(): int
	{$
		return Permutations::toMeta($this);
	}

    public function getStateBitmask(): int
	{
		return Permutations::getStateBitmask($this);
	}*/

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($player !== null && \count($properties = $this->behavior->getPocketmineProperties('block:palce')) > 0) foreach ($properties as $property => $method) $this->{$property} = $player->{$method}();
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function getCurrentBlockProperties(): array
    {
        $properties = [];
        foreach ($this->behavior->getPocketmineProperties('block:serialize_deserialize') as $property => $name) $properties[] = $this->{$property};
        return $properties;
    }

    public function serializeState(BlockStateWriter $blockStateOut): void
    {
        foreach ($this->behavior->getPocketmineProperties('block:serialize_deserialize') as $property => $name) {
            switch (true) {
                case \is_int($this->{$property}):
                    $blockStateOut->writeInt($name, $this->{$property});
                    break;
                case \is_bool($this->{$property}):
                    $blockStateOut->writeBool($name, $this->{$property});
                    break;
                case \is_string($this->{$property}):
                    $blockStateOut->writeString($name, $this->{$property});
                    break;
            }
        }
    }

    public function deserializeState(BlockStateReader $blockStateIn): void
    {
        foreach ($this->behavior->getPocketmineProperties('block:serialize_deserialize') as $property => $name) {
            $reflect = new \ReflectionClass(BlockStateReader::class);
            /** @var BlockStateData $data */
            $data = $reflect->getProperty('data')->getValue($blockStateIn);
            $tag = $data->getState($name);
            switch (true) {
                case $tag instanceof ByteTag:
                    $this->{$property} = $blockStateIn->readBool($name);
                    break;
                case $tag instanceof IntTag:
                    $this->{$property} = $blockStateIn->readInt($name);
                    break;
                case $tag instanceof StringTag:
                    $this->{$property} = $blockStateIn->readString($name);
                    break;
                default:
                    //if ($tag !== null) $blockStateIn->ignored($name);
                    break;
            }
        }
    }
}
