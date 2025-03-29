<?php

namespace astreal\libraries\managers;

use Exception;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;

abstract class BaseDatManager extends BaseManager
{

	public function __construct(
		string $name,
		private string $path,
		private LittleEndianNbtSerializer|BigEndianNbtSerializer $endianNbtSerializer,
		private string $file_extension = '.dat',
		private bool $compressed = true
	) {
		parent::__construct($name);
	}

	public function getPath(): string
	{
		return $this->path;
	}

	private function getDataPath(string $file): string
	{
		return Path::join($this->path, strtolower($file) . $this->file_extension);
	}

	private function handleCorruptedData(string $file): void
	{
		$path = $this->getDataPath($file);
		rename($path, $path . '.bak');
	}

	public function has(string $file): bool
	{
		return file_exists($this->getDataPath($file));
	}

	public function load(string $file): ?CompoundTag
	{

		$file = strtolower($file);
		$path = $this->getDataPath($file);
		if (!file_exists($path)) return null;
		try {
			$raw = Filesystem::fileGetContents($path);
		} catch (\RuntimeException $e) {
			throw new Exception("Failed to read data file \"$path\": " . $e->getMessage(), 0, $e);
		}
		try {
			$decompressed = $this->compressed ? ErrorToExceptionHandler::trapAndRemoveFalse(fn() => zlib_decode($raw)) : $raw;
		} catch (\ErrorException $e) {
			$this->handleCorruptedData($file);
			throw new Exception("Failed to decompress raw data for \"$file\": " . $e->getMessage(), 0, $e);
		}

		try {
			return $this->endianNbtSerializer->read($decompressed)->mustGetCompoundTag();
		} catch (NbtDataException $e) {
			$this->handleCorruptedData($file);
			throw new Exception("Failed to decode NBT data for \"$file\": " . $e->getMessage(), 0, $e);
		}
	}

	public function save(string $file, CompoundTag $data): void
	{
		try {
			$contents = $this->compressed ? Utils::assumeNotFalse(zlib_encode($this->endianNbtSerializer->write(new TreeRoot($data)), ZLIB_ENCODING_GZIP), "zlib_encode() failed unexpectedly") : $this->endianNbtSerializer->write(new TreeRoot(($data)));
			Filesystem::safeFilePutContents($this->getDataPath($file), $contents);
		} catch (\RuntimeException $e) {
			throw new Exception("Failed to write data file: " . $e->getMessage(), 0, $e);
		}
	}
}
