<?php
declare(strict_types=1);

namespace night\libraries\vanilla\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

interface BlockComponent {

	/**
	 * Returns the name of the component
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Returns the value of the component
	 * @return CompoundTag|Tag
	 */
	public function getValue(): Tag|CompoundTag;
}