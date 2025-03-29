<?php

namespace astreal\libraries;

use pocketmine\world\World;
use pocketmine\world\Position;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\ByteMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;

class FloatingText
{

  protected $text = "";
  /** @var Player[] */
  protected $players = [];
  protected $position = null;
  protected $entityId = null;

  public function __construct(string $text, Position $position)
  {
    $this->text = $text;
    $this->position = $position;
    $this->entityId = Entity::nextRuntimeId();
  }

  public function getText(): string
  {
    return $this->text;
  }

  public function setText(string $text): self
  {
    $this->text = $text;
    return $this;
  }

  public function setPosition(Position $pos): self
  {
    $this->position = $pos;
    return $this;
  }

  public function getPosition(): Position
  {
    return $this->position;
  }

  public function getWorld(): World
  {
    return $this->getPosition()->getWorld();
  }

  /**
   * @param Player[] $players
   */
  public function setPlayers(array $players)
  {
    $this->players = $players;
  }

  /**
   * @return Player[]
   */
  public function getPlayers(): array
  {
    return $this->players;
  }

  public function respawnToAll(): void
  {
    $players = $this->getPlayers();
    $this->despawnToAll();
    foreach ($players as $player) $this->spawnToPlayer($player);
  }

  public function isSpawn(): bool
  {
    return count($this->players) > 0;
  }

  public function isSpawneable(int $c): bool
  {
    return count($this->players) === $c;
  }

  public function despawnToAll(): void
  {
    foreach ($this->getPlayers() as $n => $player) {
      $this->despawnToPlayer($player);
    }
  }

  public function spawnToAll()
  {
    foreach ($this->getPlayers() as $n => $player) {
      $this->spawnToPlayer($player);
    }
  }

  public function despawnToPlayer(Player $player)
  {
    $pk = RemoveActorPacket::create($this->entityId);
    if ($player->isOnline()) $player->getNetworkSession()->sendDataPacket($pk);
    unset($this->players[$player->getName()]);
  }

  public function spawnToPlayer(Player $player)
  {
    $name = $this->text;
    $actorFlags = (1 << EntityMetadataFlags::IMMOBILE);
    $actorMetadata = [
      EntityMetadataProperties::FLAGS => new LongMetadataProperty($actorFlags),
      EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01), //zero causes problems on debug builds
      EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0.0),
      EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0.0),
      EntityMetadataProperties::NAMETAG => new StringMetadataProperty($name),
      EntityMetadataProperties::VARIANT => new IntMetadataProperty(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId())),
      EntityMetadataProperties::ALWAYS_SHOW_NAMETAG => new ByteMetadataProperty(1),
    ];
    $pk = AddActorPacket::create($this->entityId, $this->entityId, EntityIds::FALLING_BLOCK, $this->getPosition(), null, 0, 0, 0, 0, [], $actorMetadata, new PropertySyncData([], []), []);
    if (!$player->isOnline()) return;
    $player->getNetworkSession()->sendDataPacket($pk);
    $this->players[$player->getName()] = $player;
  }
}
