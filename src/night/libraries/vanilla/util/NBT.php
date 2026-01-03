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

namespace night\libraries\vanilla\util;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

class NBT
{

	public static function getTagType($type): ?Tag
	{
		return match (true) {
			is_array($type) => self::getArrayTag($type),
			is_bool($type) => new ByteTag($type ? 1 : 0),
			is_float($type) => new FloatTag($type),
			is_int($type) => new IntTag($type),
			is_string($type) => new StringTag($type),
			$type instanceof CompoundTag => $type,
			default => null,
		};
	}

	public static function getArrayTag(array $array): Tag
	{
		if (array_keys($array) === range(0, count($array) - 1)) {
			return new ListTag(array_map(fn($value) => self::getTagType($value), $array));
		}
		$tag = CompoundTag::create();
		foreach ($array as $key => $value) {
			$tag->setTag($key, self::getTagType($value));
		}
		return $tag;
	}
}
