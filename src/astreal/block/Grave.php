<?php

namespace astreal\block;

use astreal\block\permutation\GraveTypeTrait;
use astreal\block\tile\Grave as TileGrave;
use astreal\block\utils\GraveType;
use astreal\libraries\vanilla\block\BlockComponents;
use astreal\libraries\vanilla\block\BlockComponentsTrait;
use astreal\libraries\vanilla\block\component\CollisionBoxComponent;
use astreal\libraries\vanilla\block\component\GeometryComponent;
use astreal\libraries\vanilla\block\component\MaterialInstancesComponent;
use astreal\libraries\vanilla\block\component\SelectionBoxComponent;
use astreal\libraries\vanilla\block\Material;
use astreal\libraries\vanilla\block\permutations\Permutable;
use astreal\libraries\vanilla\block\permutations\RotatableTrait;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Selective;

class Grave extends Transparent implements Permutable, BlockComponents
{
    use BlockComponentsTrait;
    use RotatableTrait;

    private GraveType $type;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
        $this->initComponent('astreal:stone_grave');
        $this->addComponent(new SelectionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 13.0, 16.0)));
        $this->addComponent(new CollisionBoxComponent(true, new Vector3(-8.0, 0.0, -8.0), new Vector3(16.0, 13.0, 16.0)));
        $this->type = GraveType::STONE();
    }

    public function setTexture(string $texture, string $geometry): Grave
    {
        $this->addComponent(new MaterialInstancesComponent([Material::create(Material::TARGET_ALL, $texture, Material::RENDER_METHOD_ALPHA_TEST)]));
        $this->addComponent(new GeometryComponent($geometry));
        return $this;
    }

    public function setType(GraveType $type): Grave
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): GraveType
    {
        return $this->type;
    }

    public function getFrictionFactor(): float
    {
        return 0.4;
    }

    public function getLightFilter(): int
    {
        return 3;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player !== null) {
            $tile = $this->position->getWorld()->getTile($this->position);
            if ($tile instanceof TileGrave && $tile->canOpenWith($player->getName())) {
                $tile->getMenu()->send($player, $tile->getName());
                return true;
            }
        }
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }
}
