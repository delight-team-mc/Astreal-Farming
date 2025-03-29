<?php

namespace astreal\libraries;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use astreal\libraries\managers\BaseManager;

final class ScoreAPI extends BaseManager
{
	use SingletonTrait;

	private array $scoreboards = [];

	public static function make(): self
	{
		return new self();
	}

	public static function get(): ScoreAPI
	{
		return self::$instance;
	}

	public function __construct()
	{
		parent::__construct('ScoreAPI');
		self::setInstance($this);
		$this->registerEvent(PlayerQuitEvent::class, fn(PlayerQuitEvent $ev) => $this->unsetScore($ev->getPlayer()));
	}

	public function isScore(Player $player): bool
	{
		return isset($this->scoreboards[$player->getUniqueId()->toString()]);
	}

	public function unsetScore(Player $player): void
	{
		unset($this->scoreboards[$player->getUniqueId()->toString()]);
	}

	public function newScore(Player $player, string $title): void
	{
		if (isset($this->scoreboards[$player->getUniqueId()->toString()])) return;
		$player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, 'objective', $title, 'dummy', SetDisplayObjectivePacket::SORT_ORDER_ASCENDING));
		$this->scoreboards[$player->getUniqueId()->toString()] = "objective";
	}

	public function removeScoreboard(Player $player): void
	{
		$player->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create('objective'));
		unset($this->scoreboards[$player->getUniqueId()->toString()]);
	}

	public function clear(Player $player): void
	{
		for ($line = 0; $line <= 15; $line++) $this->removeLine($player, $line);
	}


	public function removeLine(Player $player, int $line): void
	{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "objective";
		$entry->score = 15 - $line;
		$entry->scoreboardId = ($line);
		$player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_REMOVE, [$entry]));
	}

	public function setLine(Player $player, int $score, string $line): void
	{
		if (!isset($this->scoreboards[$player->getUniqueId()->toString()])) return;
		if ($score > 15 || $score < 1) return;
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "objective";
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $line;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$entry->actorUniqueId = $player->getId();
		$player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_CHANGE, [$entry]));
	}

	public function setEmptyLine(Player $player, int $line): void
	{
		$this->setLine($player, $line, str_repeat(" ", $line));
	}
}
