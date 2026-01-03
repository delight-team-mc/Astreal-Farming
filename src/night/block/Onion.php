<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace night\block;

use night\libraries\vanilla\block\BlockComponents;
use night\libraries\vanilla\block\BlockComponentsTrait;
use night\libraries\vanilla\block\component\CollisionBoxComponent;
use night\libraries\vanilla\block\component\GeometryComponent;
use night\libraries\vanilla\block\component\MaterialInstancesComponent;
use night\libraries\vanilla\block\component\SelectionBoxComponent;
use night\libraries\vanilla\block\Material;
use night\libraries\vanilla\block\permutations\CropTrait;
use night\libraries\vanilla\block\permutations\Permutable;
use night\libraries\vanilla\ExtraVanillaItems;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Crops;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class Onion extends Crops implements Permutable, BlockComponents
{
    use BlockComponentsTrait;
    use CropTrait;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->initComponent("night:onions_stage0");
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, 'night:onions_stage0', Material::RENDER_METHOD_BLEND)]));
        $this->addComponent(new GeometryComponent('geometry.crop'));
        $this->addComponent(new CollisionBoxComponent(false));
        $this->addComponent(new SelectionBoxComponent(size: new Vector3(16, 1.6, 16)));
        $this->setStages(stage2: 'night:onions_stage1', stage4: 'night:onions_stage2', stage7: 'night:onions_stage3');
    }

    public function getDropsForCompatibleTool(Item $item): array
    {
        $result = [
            ExtraVanillaItems::ONION()->setCount($this->age >= self::MAX_AGE ? FortuneDropHelper::binomial($item, 1) : 1)
        ];
        return $result;
    }
}
