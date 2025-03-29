<?php

namespace astreal\libraries;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\AttributeMap;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\player\Player;
use pocketmine\Server;

class BossBar
{

	public static $instance;
	private array $players = [];
	private string $title = "";
	private string $subTitle = "";
	public ?int $actorId = null;
	private AttributeMap $attributeMap;
	protected EntityMetadataCollection $propertyManager;
	private int $color = 0;
	private int $overlay = 0;

	public function __construct(?int $actorId = null)
	{
		$this->actorId = is_null($actorId) ? Entity::nextRuntimeId() : $actorId;
		$this->attributeMap = new AttributeMap();
		$this->getAttributeMap()->add(AttributeFactory::getInstance()->mustGet(Attribute::HEALTH)->setMaxValue(100.0)->setMinValue(0.0)->setDefaultValue(100.0));
		$this->propertyManager = new EntityMetadataCollection();
		$this->propertyManager->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, false);
		$this->propertyManager->setGenericFlag(EntityMetadataFlags::NO_AI, false);
		$this->propertyManager->setGenericFlag(EntityMetadataFlags::IMMOBILE, true);
		$this->propertyManager->setGenericFlag(EntityMetadataFlags::INVISIBLE, false);
		$this->propertyManager->setGenericFlag(EntityMetadataFlags::SILENT, false);
		$this->propertyManager->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 0);
		$this->propertyManager->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()));
		$this->propertyManager->setString(EntityMetadataProperties::NAMETAG, $this->getFullTitle());
		$this->propertyManager->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
		$this->propertyManager->setFloat(EntityMetadataProperties::SCALE, 0.01);
		$this->propertyManager->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
		$this->propertyManager->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
	}

	public function getPlayers(): array
	{
		return $this->players;
	}

	/**
	 * @param Player[] $players
	 */
	public function addPlayers(array $players): BossBar
	{
		foreach ($players as $player) {
			$this->addPlayer($player);
		}
		return $this;
	}

	public function addPlayer(Player $player): BossBar
	{
		if (isset($this->players[$player->getName()])) return $this;
		$this->sendActorPacket([$player]);
		$this->sendBossPacket([$player]);
		$this->players[$player->getName()] = $player;
		return $this;
	}

	public function removePlayer(Player $player): BossBar
	{
		if (!isset($this->players[$player->getName()])) return $this;
		$this->sendRemoveBossPacket([$player]);
		unset($this->players[$player->getName()]);
		return $this;
	}

	/**
	 * @param Player[] $players
	 */
	public function removePlayers(array $players): BossBar
	{
		foreach ($players as $player) {
			$this->removePlayer($player);
		}
		return $this;
	}

	public function removeAllPlayers(): BossBar
	{
		foreach ($this->getPlayers() as $player) $this->removePlayer($player);
		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title = ""): BossBar
	{
		$this->title = $title;
		$this->sendBossTextPacket($this->getPlayers());
		return $this;
	}

	public function getSubTitle(): string
	{
		return $this->subTitle;
	}

	public function setSubTitle(string $subTitle = ""): BossBar
	{
		$this->subTitle = $subTitle;
		$this->sendBossTextPacket($this->getPlayers());
		return $this;
	}

	public function getFullTitle(): string
	{
		$text = $this->title;
		if (!empty($this->subTitle)) {
			$text .= "\n\n" . $this->subTitle;
		}
		return mb_convert_encoding($text, 'UTF-8');
	}

	public function setPercentage(float $percentage): BossBar
	{
		if ($percentage < 0) $percentage = 0;
		if ($this->getAttributeMap()->get(Attribute::HEALTH)->getMaxValue() < $percentage) $percentage = $this->getAttributeMap()->get(Attribute::HEALTH)->getMaxValue();
		$this->getAttributeMap()->get(Attribute::HEALTH)->setValue($percentage, true, true);
		$this->sendAttributesPacket($this->getPlayers());
		$this->sendBossHealthPacket($this->getPlayers());
		return $this;
	}

	public function getPercentage(): float
	{
		return $this->getAttributeMap()->get(Attribute::HEALTH)->getValue() / 100;
	}

	/**
	 * @param Player[] $players
	 */
	public function hideFrom(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_HIDE;
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($this->addDefaults($pk));
		}
	}

	public function hideFromAll(): void
	{
		$this->hideFrom($this->getPlayers());
	}

	/**
	 * @param Player[] $players
	 */
	public function showTo(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_SHOW;
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($this->addDefaults($pk));
		}
	}

	public function showToAll(): void
	{
		$this->showTo($this->getPlayers());
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendActorPacket(array $players): void
	{
		if ($this->actorId === null) return;
		$pk = AddActorPacket::create($this->actorId, $this->actorId, EntityIds::FALLING_BLOCK, Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->add(0, 6, 0), null, 0, 0, 0, 0, array_map(function (Attribute $attr): NetworkAttribute {
			return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
		}, $this->attributeMap->getAll()), $this->getPropertyManager()->getAll(), new PropertySyncData([], []), []);
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendBossPacket(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_SHOW;
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($this->addDefaults($pk));
		}
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendRemoveBossPacket(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_HIDE;
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendBossTextPacket(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_TITLE;
		$pk->title = $this->getFullTitle();
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendAttributesPacket(array $players): void
	{
		if ($this->actorId === null) return;
		$pk = UpdateAttributesPacket::create($this->actorId, array_map(fn(Attribute $attr) => new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []), $this->getAttributeMap()->needSend()), 0);
		foreach ($players as $n => $p) {
			if ($p->isOnline()) $p->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param Player[] $players
	 */
	protected function sendBossHealthPacket(array $players): void
	{
		$pk = new BossEventPacket();
		$pk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
		$pk->healthPercent = $this->getPercentage();
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = (is_null($this->actorId) ? $player->getId() : $this->actorId);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	private function addDefaults(BossEventPacket $pk): BossEventPacket
	{
		$pk->title = $this->getFullTitle();
		$pk->healthPercent = $this->getPercentage();
		$pk->darkenScreen = false;
		$pk->color = $this->color;
		$pk->overlay = $this->overlay;
		return $pk;
	}

	public function getColor(): int
	{
		return $this->color;
	}

	public function setColor(int $color): BossBar
	{
		$this->color = $color;
		return $this;
	}

	public function getOverlay(): int
	{
		return $this->overlay;
	}

	public function setOverlay(int $color): BossBar
	{
		$this->overlay = $color;
		return $this;
	}

	public function __toString(): string
	{
		return __CLASS__ . " ID: $this->actorId, Players: " . count($this->players) . ", Title: \"$this->title\", Subtitle: \"$this->subTitle\", Percentage: \"" . $this->getPercentage() . "\"";
	}

	public function getAttributeMap(): AttributeMap
	{
		return $this->attributeMap;
	}

	protected function getPropertyManager(): EntityMetadataCollection
	{
		return $this->propertyManager;
	}

	private function broadcastPacket(array $players, BossEventPacket $pk)
	{
		foreach ($players as $player) {
			if (!$player->isConnected()) continue;
			$pk->bossActorUniqueId = $player->getId();
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}
}
