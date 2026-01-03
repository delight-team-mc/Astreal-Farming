<?php

declare(strict_types=1);

namespace night\libraries\vanilla\block\permutations;

use night\libraries\vanilla\block\component\MaterialInstancesComponent;
use night\libraries\vanilla\block\component\SelectionBoxComponent;
use night\libraries\vanilla\block\Material;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\math\Vector3;

trait BrushCropTrait
{
    public ?string $stage1 = null, $stage2 = null, $stage3 = null, $stage4 = null, $stage5 = null, $stage6 = null, $stage7 = null;

    public function setStages(?string $stage1 = null, ?string $stage2 = null, ?string $stage3 = null, ?string $stage4 = null, ?string $stage5 = null, ?string $stage6 = null, ?string $stage7 = null): void
    {
        $this->stage1 = $stage1;
        $this->stage2 = $stage2;
        $this->stage3 = $stage3;
        $this->stage4 = $stage4;
        $this->stage5 = $stage5;
        $this->stage6 = $stage6;
        $this->stage7 = $stage7;
    }

    /**
     * @return BlockProperty[]
     */
    public function getBlockProperties(): array
    {
        return [new BlockProperty("vanilla:growth", [0, 1, 2, 3, 4, 5, 6, 7])];
    }

    /**
     * @return Permutation[]
     */
    public function getPermutations(): array
    {
        $p = [
            (new Permutation("q.block_property('vanilla:growth') == 0"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 1.6, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 1"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 3.2, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 2"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 4.8, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 3"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 6.4, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 4"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 8, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 5"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 9.6, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 6"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 11.2, 16)))->getValue()),
            (new Permutation("q.block_property('vanilla:growth') == 7"))->withComponent("minecraft:selection_box", (new SelectionBoxComponent(size: new Vector3(16, 12.8, 16)))->getValue()),
        ];
        foreach (array_filter([1 => $this->stage1, 2 => $this->stage2, 3 => $this->stage3, 4 => $this->stage4, 5 => $this->stage5, 6  => $this->stage6, 7  => $this->stage7], fn($s) => !is_null($s)) as $stage => $texture) $p[] = (new Permutation("q.block_property('vanilla:growth') >= {$stage}"))
            ->withComponent('minecraft:material_instances', (new MaterialInstancesComponent([
                Material::create(Material::TARGET_ALL, $texture, Material::RENDER_METHOD_BLEND)
            ]))->getValue());
        return $p;
    }

    public function getCurrentBlockProperties(): array
    {
        return [$this->age];
    }

    protected function writeStateToMeta(): int
    {
        return Permutations::toMeta($this);
    }

    public function readStateFromData(int $id, int $stateMeta): void
    {
        $blockProperties = Permutations::fromMeta($this, $stateMeta);
        $this->age = $blockProperties[0] ?? 0;
    }

    public function getStateBitmask(): int
    {
        return Permutations::getStateBitmask($this);
    }

    public function serializeState(BlockStateWriter $out): void
    {
        $out->writeInt("vanilla:growth", $this->age);
    }

    public function deserializeState(BlockStateReader $in): void
    {
        $this->age = $in->readInt("vanilla:growth");
    }
}
