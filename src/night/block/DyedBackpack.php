<?php

namespace night\block;

use night\libraries\vanilla\block\component\MaterialInstancesComponent;
use night\libraries\vanilla\block\Material;
use night\libraries\vanilla\CustomBlockTypeNames;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\utils\ColoredTrait;

class DyedBackpack extends BackPack
{
    use ColoredTrait;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, CustomBlockTypeNames::WHITE_BACKPACK, Material::RENDER_METHOD_ALPHA_TEST)]));
    }

    public function setColorTexture(string $texture): DyedBackpack
    {
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, $texture, Material::RENDER_METHOD_ALPHA_TEST)]));
        return $this;
    }
}
