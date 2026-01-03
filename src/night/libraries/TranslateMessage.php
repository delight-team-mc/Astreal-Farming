<?php

namespace night\libraries;

use night\Loader;
use pocketmine\utils\TextFormat;
use Symfony\Component\Filesystem\Path;

class TranslateMessage
{

    const EN_US = 'en_us';
    const ES_ES = 'es_es';

    public static function create(string $translate, string $language = TranslateMessage::EN_US): self
    {
        return new self($translate, $language);
    }

    public function __construct(private string $translate, private string $language = TranslateMessage::EN_US) {}

    public function translate(array $search = [], array $replace = []): string
    {
        $translations = $this->load();
        return str_replace(array_merge(['&'], $search), array_merge([TextFormat::ESCAPE], $replace), $translations[$this->translate] ?? $this->translate);
    }

    private function load(): array
    {
        $translations = [];
        $file = Path::join(Loader::getInstance()->getDataFolder(), 'lang', $this->language . '.lang');
        if (!file_exists($file)) return $translations;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $translations[trim($key)] = trim($value);
        }
        return $translations;
    }

    public function __toString()
    {
        return $this->translate();
    }
}
