<?php

namespace night\libraries\chunkloader;

use Closure;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\ChunkSelector;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use PrefixedLogger;
use night\libraries\managers\BaseManager;

final class ChunkManager extends BaseManager
{
	use SingletonTrait;

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}

	public function __construct()
	{
		parent::__construct('ChunkManager');
	}

	public function registerWorldChunkLoader(World $world, int $x, int $z, string $cl_class, ?Closure $create_fn = null): void
	{
		Utils::testValidInstance($cl_class, BaseChunkLoader::class);
		/** @var BaseChunkLoader $chunk_loader */
		$chunk_loader = new $cl_class($world, $x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE, $x, $z);
		if (!is_null($create_fn)) $create_fn($chunk_loader);
		if ($world->isChunkPopulated($chunk_loader->getChunkX(), $chunk_loader->getChunkZ())) {
			$chunk_loader->onChunkPopulated($chunk_loader->getChunkX(), $chunk_loader->getChunkZ(), $world->getChunk($chunk_loader->getChunkX(), $chunk_loader->getChunkZ()));
			return;
		}
		$world->registerChunkListener($chunk_loader, $chunk_loader->getChunkX(), $chunk_loader->getChunkZ());
		$world->registerChunkLoader($chunk_loader, $chunk_loader->getChunkX(), $chunk_loader->getChunkZ(), true);
		$world->orderChunkPopulation($chunk_loader->getChunkX(), $chunk_loader->getChunkZ(), $chunk_loader);
	}

	public function onGenerateWorldChunks(World|string $world, int $x, int $z, int $selector = 8): void
	{
		$worldManage = $this->getServer()->getWorldManager();
		$logger = is_string($world) ? new PrefixedLogger($this->getLogger(), $world) : $world->getLogger();
		$world = is_string($world) ? $worldManage->getWorldByName($world) : $world;
		if (!$world instanceof World) return;
		$logger->notice($this->getServer()->getLanguage()->translate(KnownTranslationFactory::pocketmine_level_backgroundGeneration((is_string($world) ? $world : $world->getFolderName()))));
		$centerX = $x >> Chunk::COORD_BIT_SIZE;
		$centerZ = $z >> Chunk::COORD_BIT_SIZE;
		$selected = iterator_to_array((new ChunkSelector())->selectChunks($selector, $centerX, $centerZ), preserve_keys: false);
		$done = 0;
		$total = count($selected);
		foreach ($selected as $index) {
			World::getXZ($index, $chunkX, $chunkZ);
			$world->orderChunkPopulation($chunkX, $chunkZ, null)->onCompletion(
				static function () use ($world, &$done, $total, $logger): void {
					$oldProgress = (int) floor(($done / $total) * 100);
					$newProgress = (int) floor((++$done / $total) * 100);
					if (intdiv($oldProgress, 10) !== intdiv($newProgress, 10) || $done === $total || $done === 1) $logger->info(Server::getInstance()->getLanguage()->translate(KnownTranslationFactory::pocketmine_level_spawnTerrainGenerationProgress(strval($done), strval($total), strval($newProgress))));
				},
				static function (): void {}
			);
		}
	}
}
