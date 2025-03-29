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

namespace astreal\libraries\vanilla\item\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;

final class DurabilityComponent implements ItemComponent
{

	private int $maxDurability;
	private int $minDamageChance;
	private int $maxDamageChance;

	public function __construct(int $maxDurability, int $minDamageChance = 100, int $maxDamageChance = 100)
	{
		$this->maxDurability = $maxDurability;
		$this->minDamageChance = $minDamageChance;
		$this->maxDamageChance = $maxDamageChance;
	}

	public function getName(): string
	{
		return "minecraft:durability";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()->setInt("max_durability", $this->maxDurability)->setTag("damage_chance", CompoundTag::create()->setInt("min", $this->minDamageChance)->setInt("max", $this->maxDamageChance));
	}

	public function isProperty(): bool
	{
		return false;
	}
}
