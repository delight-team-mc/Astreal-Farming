<?php
declare(strict_types=1);

namespace astreal\libraries\vanilla\block;

use pocketmine\nbt\tag\CompoundTag;

final class Material {

	public const TARGET_ALL = "*";
	public const TARGET_SIDES = "sides";
	public const TARGET_UP = "up";
	public const TARGET_DOWN = "down";
	public const TARGET_NORTH = "north";
	public const TARGET_EAST = "east";
	public const TARGET_SOUTH = "south";
	public const TARGET_WEST = "west";

	public const RENDER_METHOD_ALPHA_TEST = "alpha_test";
	public const RENDER_METHOD_BLEND = "blend";
	public const RENDER_METHOD_OPAQUE = "opaque";

	public static function create(string $target, string $texture, string $renderMethod, bool $faceDimming = true, bool $ambientOcclusion = true):self{
		return new Material($target, $texture, $renderMethod, $faceDimming, $ambientOcclusion); 
	}

	public function __construct(
		private readonly string $target,
		private readonly string $texture,
		private readonly string $renderMethod,
		private readonly bool   $faceDimming = true,
		private readonly bool   $ambientOcclusion = true
	){}

	public function getTarget(): string {
		return $this->target;
	}

	public function toNBT(): CompoundTag {
		return CompoundTag::create()->setString("texture", $this->texture)->setString("render_method", $this->renderMethod)->setByte("face_dimming", $this->faceDimming ? 1 : 0)->setByte("ambient_occlusion", $this->ambientOcclusion ? 1 : 0);
	}
}
