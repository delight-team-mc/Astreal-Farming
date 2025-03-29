<?php
declare(strict_types=1);

namespace astreal\libraries\vanilla\block\permutations;

use astreal\libraries\vanilla\util\NBT;
use pocketmine\nbt\tag\CompoundTag;

final class Permutation {

	private CompoundTag $components;

	public function __construct(private readonly string $condition) {
		$this->components = CompoundTag::create();
	}

	public function withComponent(string $component, mixed $value) : self {
		$this->components->setTag($component, NBT::getTagType($value));
		return $this;
	}

	public function toNBT(): CompoundTag {
		return CompoundTag::create()->setString("condition", $this->condition)->setTag("components", $this->components);
	}
}