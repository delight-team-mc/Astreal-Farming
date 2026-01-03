<?php

declare(strict_types=1);
/*
 * 
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║   
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║   
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║   
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝   
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 */

namespace night\libraries\vanilla\item\component;

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;

final class UseAnimationComponent implements ItemComponent
{

	public const ANIMATION_NONE = 0;
	public const ANIMATION_EAT = 1;
	public const ANIMATION_DRINK = 2;
	public const ANIMATION_BLOCK = 3;
	public const ANIMATION_BOW = 4;
	public const ANIMATION_CAMERA = 5;
	public const ANIMATION_SPEAR = 6;
	public const ANIMATION_CROSSBOW = 9;
	public const ANIMATION_SPYGLASS = 10;
	public const ANIMATION_BRUSH = 12;

	private int $animation;

	public function __construct(string|int $animation)
	{
		$this->animation = is_numeric($animation) ? $animation : match ($animation) {
			'none' => self::ANIMATION_NONE,
			'eat' => self::ANIMATION_EAT,
			'drink' => self::ANIMATION_DRINK,
			'block' => self::ANIMATION_BLOCK,
			'bow' => self::ANIMATION_BOW,
			'camera' => self::ANIMATION_CAMERA,
			'spear' => self::ANIMATION_SPEAR,
			'crossbow' => self::ANIMATION_CROSSBOW,
			'spyglass' => self::ANIMATION_SPYGLASS,
			'brush' => self::ANIMATION_BRUSH,
			default => self::ANIMATION_NONE
		};
	}

	public function getName(): string
	{
		return "use_animation";
	}

	public function getValue(): Tag
	{
		return new IntTag($this->animation);
	}

	public function isProperty(): bool
	{
		return true;
	}
}
