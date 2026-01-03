<?php

declare(strict_types=1);

namespace night\libraries\vanilla\block\properties;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

/**
 * Represents a box with origin and size for teh CollisionBox component.
 * Origin must be in range (-8, 0, -8) to (7, 23, 7).
 * Size must be in range (1, 1, 1) to (16, 24, 16).
 * Origin + size must be in range (-8, 0, -8) to (8, 24, 8).
 */
class Box
{

	private Vector3 $origin;
	private Vector3 $size;

	public function __construct(Vector3 $origin, Vector3 $size)
	{
		// Clamp origin
		$originX = max(-8, min(7, $origin->x));
		$originY = max(0, min(23, $origin->y));
		$originZ = max(-8, min(7, $origin->z));

		// Clamp size
		$sizeX = max(1, min(16, $size->x));
		$sizeY = max(1, min(24, $size->y));
		$sizeZ = max(1, min(16, $size->z));

		// Clamp to ensure origin + size is valid
		$sizeX = min($sizeX, 8 - $originX);
		$sizeY = min($sizeY, 24 - $originY);
		$sizeZ = min($sizeZ, 8 - $originZ);

		$this->origin = new Vector3($originX, $originY, $originZ);
		$this->size = new Vector3($sizeX, $sizeY, $sizeZ);
	}

	public static function fromAxisAlignedBB(AxisAlignedBB $bb): self
	{
		return new self(
			new Vector3($bb->minX, $bb->minY, $bb->minZ),
			new Vector3($bb->maxX - $bb->minX, $bb->maxY - $bb->minY, $bb->maxZ - $bb->minZ)
		);
	}

	public function getOrigin(): Vector3
	{
		return $this->origin;
	}
	public function getSize(): Vector3
	{
		return $this->size;
	}
	public function getMax(): Vector3
	{
		return $this->origin->addVector($this->size);
	}

	/**
	 * Convert to NBT output format.
	 */
	public function toNbtArray(): array
	{
		$max = $this->getMax();
		return [
			"minX" => (float)$this->origin->x + 8,
			"minY" => (float)$this->origin->y,
			"minZ" => (float)$this->origin->z + 8,
			"maxX" => (float)$max->x + 8,
			"maxY" => (float)$max->y,
			"maxZ" => (float)$max->z + 8,
		];
	}

	public function toAxisAlignedBB(): AxisAlignedBB
	{
		$max = $this->getMax();
		return new AxisAlignedBB(
			$this->origin->x,
			$this->origin->y,
			$this->origin->z,
			$max->x,
			$max->y,
			$max->z
		);
	}
}
