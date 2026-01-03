<?php
declare(strict_types=1);

namespace night\libraries\vanilla\block\permutations;

use night\libraries\vanilla\util\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function array_map;

final class BlockProperty {

	public function __construct(private readonly string $name, private readonly array $values) { }

	public function getName(): string {
		return $this->name;
	}

	public function getValues(): array {
		return $this->values;
	}

	public function toNBT(): CompoundTag {
		$values = array_map(static fn($value) => NBT::getTagType($value), $this->values);
		return CompoundTag::create()->setString("name", $this->name)->setTag("enum", new ListTag($values));
	}
}