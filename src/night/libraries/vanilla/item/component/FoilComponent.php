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

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Tag;

final class FoilComponent implements ItemComponent {

	private bool $foil;

	public function __construct(bool $foil = true) {
		$this->foil = $foil;
	}

	public function getName(): string {
		return "foil";
	}

	public function getValue(): Tag {
		return new ByteTag($this->foil ? 1 : 0);
	}

	public function isProperty(): bool {
		return true;
	}
}