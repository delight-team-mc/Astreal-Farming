<?php

namespace astreal\libraries\webhook;

class Component implements \JsonSerializable
{

    protected array $data = [];

    public function addLinkButton(string $text, string $link)
    {
        if (!isset($this->data["type"])) $this->data["type"] = 1;
        if (!isset($this->data["components"])) $this->data["components"] = [];
        $this->data["components"]["type"] = 5;
        $this->data["components"]["label"] = $text;
        $this->data["components"]["url"] = $link;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
