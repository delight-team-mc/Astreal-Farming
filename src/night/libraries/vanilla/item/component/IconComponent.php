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

final class IconComponent implements ItemComponent
{

	private string $default_texture;
	private string $dyed_texture;
	private string $trim_texture;

	public function __construct(string $default_texture, string $dyed_texture = "", string $trim_texture = "")
	{
		$this->default_texture = $default_texture;
		$this->dyed_texture = $dyed_texture;
		$this->trim_texture = $trim_texture;
	}

	public function getName(): string
	{
		return "minecraft:icon";
	}

	public function getValue(): Tag
	{
		return CompoundTag::create()->setString('texture', $this->default_texture)->setTag("textures", CompoundTag::create()->setString("default", $this->default_texture)->setString('dyed', $this->dyed_texture)->setString('icon_trim', $this->trim_texture));
	}

	public function isProperty(): bool
	{
		return true;
	}
}
