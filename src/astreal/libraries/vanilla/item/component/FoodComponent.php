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

final class FoodComponent implements ItemComponent
{

	private bool $canAlwaysEat;
	private int $nutrition;
	private float $saturationModifier;
	private string $usingConvertsTo;

	public function __construct(bool $canAlwaysEat = false, int $nutrition = 0, float $saturationModifier = 0.6, string $usingConvertsTo = "")
	{
		$this->canAlwaysEat = $canAlwaysEat;
		$this->nutrition = $nutrition;
		$this->saturationModifier = $saturationModifier;
		$this->usingConvertsTo = $usingConvertsTo;
	}

	public function getName(): string
	{
		return "minecraft:food";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()
			->setByte("can_always_eat", $this->canAlwaysEat ? 1 : 0)
			->setInt("nutrition", $this->nutrition)
			->setFloat("saturation_modifier", $this->saturationModifier)
			->setTag("using_converts_to", CompoundTag::create()->setString("name", $this->usingConvertsTo));
	}

	public function isProperty(): bool
	{
		return false;
	}
}
