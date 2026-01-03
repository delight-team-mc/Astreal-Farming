<?php

namespace night\libraries;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;

class Particle implements \pocketmine\world\particle\Particle
{

    public function __construct(private string $name) {}

    public function encode(Vector3 $pos): array
    {
        return [SpawnParticleEffectPacket::create(DimensionIds::OVERWORLD, -1, $pos, $this->name, null)];
    }

}