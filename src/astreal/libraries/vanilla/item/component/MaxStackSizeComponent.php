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

use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;

final class MaxStackSizeComponent implements ItemComponent {

	private int $maxStackSize;

	public function __construct(int $maxStackSize) {
		$this->maxStackSize = $maxStackSize;
	}

	public function getName(): string {
		return "max_stack_size";
	}

	public function getValue(): Tag {
		return new IntTag($this->maxStackSize);
	}

	public function isProperty(): bool {
		return true;
	}
}