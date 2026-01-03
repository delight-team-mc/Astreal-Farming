<?php

namespace night\libraries\vanilla\block\component;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

class TagsComponent implements BlockComponent
{

    /** @var string[] */
    public function __construct(private array $tags) {}

    public function getName(): string
    {
        return 'blockTags';
    }

    public function getValue(): Tag
    {
        $list = [];
        foreach ($this->tags as $tag) $list[] = new StringTag(substr($tag, 4));
        return new ListTag($list);
    }
}
