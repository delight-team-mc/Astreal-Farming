<?php

namespace astreal\libraries;

use astreal\Loader;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

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
        if (!file_exists(Loader::getInstance()->getDataFolder() . '/lang/' . $this->language . '.json')) return $this->translate;
        return str_replace(array_merge(['&'], $search), array_merge([TextFormat::ESCAPE], $replace), (new Config(Loader::getInstance()->getDataFolder() . '/lang/' . $this->language . '.json'))->getNested($this->translate, $this->translate));
    }

    public function __toString()
    {
        return $this->translate();
    }
}
