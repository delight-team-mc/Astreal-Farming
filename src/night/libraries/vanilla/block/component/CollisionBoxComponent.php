<?php

namespace night\libraries\vanilla\block\component;

use night\libraries\vanilla\block\properties\Box;
use night\libraries\vanilla\util\NBT;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;

class CollisionBoxComponent implements BlockComponent
{

	private bool $enabled;
	/** @var Box[] */
	private array $boxes = [];

	/**
	 * Defines the area of the block that collides with entities.
	 * @param bool $enabled If collision should be enabled
	 */
	public function __construct(bool $enabled = true)
	{
		$this->enabled = $enabled;
	}

	/**
	 * Add a collision box.
	 * @param Box $box The box to add
	 * @return $this
	 */
	public function addBox(Box $box): self
	{
		$this->boxes[] = $box;
		return $this;
	}

	/**
	 * Add collision boxes.
	 * @param Box[] $boxes The boxes to add
	 * @return $this
	 */
	public function addBoxes(array $boxes): self
	{
		foreach ($boxes as $box) {
			$this->boxes[] = $box;
		}
		return $this;
	}

	public function getName(): string
	{
		return "minecraft:collision_box";
	}

	public function getValue(): CompoundTag
	{
		if(empty($this->boxes))$this->boxes[] = new Box(new Vector3(-8, 0, -8), new Vector3(16, 16, 16));
		return NBT::getTagType([
			"enabled" => $this->enabled ? 1 : 0,
			"boxes" => array_map(fn(Box $box): array => $box->toNbtArray(), $this->boxes)
		]);
	}
}
