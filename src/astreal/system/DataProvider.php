<?php

namespace astreal\system;

use Closure;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use SplFileInfo;
use InvalidArgumentException;
use astreal\libraries\managers\BaseManager;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ResourcePackException;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

class DataProvider extends BaseManager
{
    use SingletonTrait;

    const EXTENSION_PROPERTIES = "properties";
    const EXTENSION_CNF = "cnf";
    const EXTENSION_CONF = "conf";
    const EXTENSION_CONFIG = "config";
    const EXTENSION_JSON = "json";
    const EXTENSION_JS = "js";
    const EXTENSION_YML = "yml";
    const EXTENSION_YAML = "yaml";
    const EXTENSION_SL = "sl";
    const EXTENSION_SERIALIZE = "serialize";
    const EXTENSION_TXT = "txt";
    const EXTENSION_LIST = "list";
    const EXTENSION_ENUM = "enum";
    const EXTENSION_ZIP = "zip";

    private string $behavior_folder, $resources_folder;
    private Config $setting;

    public static function getInstance(): self
    {
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct("DataProvider");
        self::setInstance($this);
        $this->init();
    }

    public function init()
    {
        $this->behavior_folder = $this->getServer()->getDataPath() . 'Behavior' . DIRECTORY_SEPARATOR;
        $this->resources_folder = $this->getServer()->getDataPath() . 'Resources' . DIRECTORY_SEPARATOR;
        foreach ([$this->behavior_folder, $this->resources_folder] as $path) @mkdir($path);
        $this->setting = new Config($this->getLoader()->getDataFolder() . "setting.json", Config::JSON);
        $this->getLogger()->info('DataProvider enable!');
        $this->loadResourcePacks();
    }

    public function createDir(string $path): void
    {
        $fullPath = $this->getLoader()->getDataFolder() . $path;

        if (!is_dir($fullPath) && !mkdir($fullPath, 0777, true) && !is_dir($fullPath)) {
            throw new RuntimeException("No se pudo crear el directorio: $fullPath");
        }
    }


    public function getFilesByPath(string $path, ?Closure $process = null): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($iterator as $file) if ($file->isFile()) $files[] = $process ? $process($file->getPathname()) : $file->getPathname();
        return $files;
    }


    public function saveZip(string $fileName, string $fromPath, string $toPath): void
    {
        $fromPath = realpath($fromPath);
        $zip = new ZipArchive();
        $zip->open(Path::join($toPath, $fileName . '.zip'), $zip::CREATE | $zip::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fromPath), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $relativePath = substr($file, strlen($fromPath) + 1);
                $zip->addFile($file, $relativePath);
            }
        }
        $zip->close();
    }

    public function extractZip(string $fileName, string $fromPath, string $toPath): bool
    {
        if (!file_exists(Path::join(($filePath = $fromPath . $fileName . '.zip')))) {
            $zip = new ZipArchive();
            $zip->open($filePath);
            $zip->extractTo($toPath);
            $zip->close();
            return true;
        }
        return false;
    }

    public function removeContainerFiles(string $path, bool $removeDir = true): int
    {
        $RemovedFiles = 0;
        if (!is_dir($path)) return -1;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath());
            } else if ($removeDir) rmdir($file->getRealPath());
            $RemovedFiles++;
        }
        return $RemovedFiles;
    }

    public function checkFileExtension(string $file, string $type): bool
    {
        return (strtolower((new SplFileInfo($file))->getExtension()) === $type);
    }

    public function saveConfig(string $path): void
    {
        $this->getLoader()->saveResource($path);
    }

    public function getCustomConfig(string $path, array $default = []): Config
    {
        return new Config($path, Config::DETECT, $default);
    }

    //--------------------------------------------------------------------/

    private function loadResourcePacks(): void
    {
        $this->getLogger()->info("Loading resource packs");
        /** @var ResourcePack[] */
        $packs = [];
        try {
            foreach ($this->getFilesByPath($this->resources_folder) as $file) if (file_exists($this->resources_folder . $file) && !is_dir($this->resources_folder . $file) && in_array((new SplFileInfo($this->resources_folder . $file))->getExtension(), [self::EXTENSION_ZIP, "mcpack"])) $packs[] = new ZippedResourcePack($this->resources_folder . $file);
            ($resource_manager = $this->getServer()->getResourcePackManager())->setResourceStack(array_merge($packs, $resource_manager->getResourceStack()));
        } catch (ResourcePackException | InvalidArgumentException $th) {
            $this->getLogger()->error($th->getMessage());
        }
    }

    //-------------------------------------------------------------------------/

    public function getBehaviorFolder(): string
    {
        return $this->behavior_folder;
    }

    public function getResourceFolder(): string
    {
        return $this->resources_folder;
    }
}
