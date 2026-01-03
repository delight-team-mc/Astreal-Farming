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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class CooldownComponent implements ItemComponent
{
	public const CATEGORY_SHIELD = "minecraft:shield";
	public const CATEGORY_PEARL = "minecraft:ender_pearl";
	public const CATEGORY_HORN = "minecraft:goat_horn";
	public const CATEGORY_WINDCHARGE = "minecraft:wind_charge";
	public const CATEGORY_CHORUS = "minecraft:chorusfruit";

	private string $category;
	private float $duration;

	public function __construct(string $category, float $duration)
	{
		$this->category = $category;
		$this->duration = $duration;
	}

	public function getName(): string
	{
		return "minecraft:cooldown";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()->setString("category", $this->category)->setFloat("duration", $this->duration);
	}

	public function isProperty(): bool
	{
		return false;
	}
}
