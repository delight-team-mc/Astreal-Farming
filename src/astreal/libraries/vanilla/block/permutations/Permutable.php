<?php
declare(strict_types=1);

namespace astreal\libraries\vanilla\block\permutations;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;

interface Permutable {

	/**
	 * @return BlockProperty[]
	 */
	public function getBlockProperties(): array;
	/**
	 * @return Permutation[]
	 */
	public function getPermutations(): array;
	public function getCurrentBlockProperties(): array;
	public function serializeState(BlockStateWriter $blockStateOut): void;
	public function deserializeState(BlockStateReader $blockStateIn): void;
}
