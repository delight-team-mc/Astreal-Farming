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

final class DisplayNameComponent implements ItemComponent {

	private string $name;

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function getName(): string {
		return "minecraft:display_name";
	}

	public function getValue(): Tag {
		return CompoundTag::create()->setString("value", $this->name);
	}

	public function isProperty(): bool {
		return false;
	}
}