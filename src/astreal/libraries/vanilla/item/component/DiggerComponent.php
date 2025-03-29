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

use astreal\libraries\vanilla\util\NBT;
use pocketmine\block\Block;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\nbt\tag\Tag;
use function array_map;
use function implode;

final class DiggerComponent implements ItemComponent
{

	private array $destroySpeeds;
	private bool $useEfficiency;

	public function __construct(bool $useEfficiency)
	{
		$this->useEfficiency = $useEfficiency;
	}

	public function getName(): string
	{
		return "minecraft:digger";
	}

	public function getValue(): Tag
	{
		return NBT::getTagType([
			"use_efficiency" => $this->useEfficiency,
			"destroy_speeds" => $this->destroySpeeds
		]);
	}

	public function isProperty(): bool
	{
		return false;
	}

	public function withBlocks(int $speed, Block ...$blocks): DiggerComponent
	{
		foreach ($blocks as $block) {
			$this->destroySpeeds[] = [
				"block" => [
					"name" => GlobalBlockStateHandlers::getSerializer()->serialize($block->getStateId())->getName()
				],
				"speed" => $speed
			];
		}
		return $this;
	}

	public function withTags(int $speed, string ...$tags): DiggerComponent
	{
		$query = implode(",", array_map(fn($tag) => "'" . $tag . "'", $tags));
		$this->destroySpeeds[] = [
			"block" => [
				"tags" => "query.any_tag(" . $query . ")"
			],
			"speed" => $speed
		];
		return $this;
	}
}
