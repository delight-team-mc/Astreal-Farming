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

final class ProjectileComponent implements ItemComponent
{

	private float $minimumCriticalPower;
	private string $projectileEntity;

	public function __construct(float $minimumCriticalPower, string $projectileEntity)
	{
		$this->minimumCriticalPower = $minimumCriticalPower;
		$this->projectileEntity = $projectileEntity;
	}

	public function getName(): string
	{
		return "minecraft:projectile";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()->setFloat("minimum_critical_power", $this->minimumCriticalPower)->setString("projectile_entity", $this->projectileEntity);
	}

	public function isProperty(): bool
	{
		return false;
	}
}
