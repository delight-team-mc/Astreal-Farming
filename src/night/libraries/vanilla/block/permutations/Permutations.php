<?php
declare(strict_types=1);

namespace night\libraries\vanilla\block\permutations;

use Exception;
use function array_map;
use function count;
use function current;
use function next;
use function reset;

class Permutations {
	
	public static function fromMeta(Permutable $block, int $meta): array {
		$properties = self::getCartesianProduct(array_map(static fn(BlockProperty $blockProperty) => $blockProperty->getValues(), $block->getBlockProperties()))[$meta] ?? null;
		if($properties === null) {
			throw new Exception("Unable to calculate permutations from block meta: " . $meta);
		}
		return $properties;
	}

	public static function toMeta(Permutable $block): int {
		$properties = self::getCartesianProduct(array_map(static fn(BlockProperty $blockProperty) => $blockProperty->getValues(), $block->getBlockProperties()));
		foreach($properties as $meta => $permutations){
			if($permutations === $block->getCurrentBlockProperties()) {
				return $meta;
			}
		}
		throw new Exception("Unable to calculate block meta from current permutations");
	}

	public static function getStateBitmask(Permutable $block): int {
		$possibleValues = array_map(static fn(BlockProperty $blockProperty) => $blockProperty->getValues(), $block->getBlockProperties());
		return count(self::getCartesianProduct($possibleValues)) - 1;
	}

	public static function getCartesianProduct(array $arrays): array {
		$result = [];
		$count = count($arrays) - 1;
		$combinations = array_product(array_map(static fn(array $array) => count($array), $arrays));
		for($i = 0; $i < $combinations; $i++){
			$result[] = array_map(static fn(array $array) => current($array), $arrays);
			for($j = $count; $j >= 0; $j--){
				if(next($arrays[$j])) {
					break;
				}
				reset($arrays[$j]);
			}
		}
		return $result;
	}
}