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

final class ThrowableComponent implements ItemComponent
{

	private bool $doSwingAnimation;
	private float $launchPowerScale;
	private float $maxDrawDuration;
	private float $maxLaunchPower;
	private float $minDrawDuration;
	private bool $scalePowerByDrawDuration;

	public function __construct(bool $doSwingAnimation = false, float $launchPowerScale = 1.0, float $maxDrawDuration = 0.0, float $maxLaunchPower = 1.0, float $minDrawDuration = 0.0, bool $scalePowerByDrawDuration = false)
	{
		$this->doSwingAnimation = $doSwingAnimation;
		$this->launchPowerScale = $launchPowerScale;
		$this->maxDrawDuration = $maxDrawDuration;
		$this->maxLaunchPower = $maxLaunchPower;
		$this->minDrawDuration = $minDrawDuration;
		$this->scalePowerByDrawDuration = $scalePowerByDrawDuration;
	}

	public function getName(): string
	{
		return "minecraft:throwable";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()
			->setByte("do_swing_animation", $this->doSwingAnimation ? 1 : 0)
			->setFloat("launch_power_scale",  $this->launchPowerScale)
			->setFloat("max_draw_duration",  $this->maxDrawDuration)
			->setFloat("max_launch_power",  $this->maxLaunchPower)
			->setFloat("min_draw_duration",  $this->minDrawDuration)
			->setByte("scale_power_by_draw_duration",  $this->scalePowerByDrawDuration ? 1 : 0);
	}

	public function isProperty(): bool
	{
		return false;
	}
}
