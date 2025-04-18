<?php

namespace astreal\block;

use astreal\libraries\vanilla\block\BlockComponents;
use astreal\libraries\vanilla\block\BlockComponentsTrait;
use astreal\libraries\vanilla\block\component\CollisionBoxComponent;
use astreal\libraries\vanilla\block\component\GeometryComponent;
use astreal\libraries\vanilla\block\component\MaterialInstancesComponent;
use astreal\libraries\vanilla\block\component\SelectionBoxComponent;
use astreal\libraries\vanilla\block\Material;
use astreal\libraries\vanilla\CustomBlockTypeNames;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Transparent;
use pocketmine\math\Vector3;

class FishingFrame extends Transparent implements BlockComponents
{
    use BlockComponentsTrait;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->initComponent(CustomBlockTypeNames::BACKPACK);
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, CustomBlockTypeNames::FISHING_FRAME, Material::RENDER_METHOD_ALPHA_TEST)]));
        $this->addComponent(new GeometryComponent('geometry.fishing.frame'));
        $this->addComponent(new SelectionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 16.0, 16.0)));
        $this->addComponent(new CollisionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 16.0, 16.0)));
    }
}
