<?php

namespace night\system\behavior;

use night\libraries\vanilla\item\component\ItemComponent;

class Item implements \JsonSerializable
{
    /** @var mixed[] */
    private array $nestedCache = [];
    /** @var ItemComponent[] $components */
    private array $components = [];
    private array $data;

    public function __construct(string $path)
    {
        $this->data = json_decode(\file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
    }

    public function getIdentifier(): string
    {
        return $this->data['minecraft:item']['description']['identifier'];
    }

    public function getMenuCategory(): array
    {
        return $this->data['minecraft:item']['description']['menu_category'] ?? [];
    }

    public function getRawComponent(string $component, mixed $default = null): array|float|int|string|bool|null
    {
        return (($this->data['minecraft:item']['components'] ?? [])[$component] ?? $default);
    }

    public function getRawComponentNested(string $component, mixed $default = null): mixed
    {
        
        if (isset($this->nestedCache[$component])) return $this->nestedCache[$component];
        $vars = explode(".", $component, limit: PHP_INT_MAX);
        $base = array_shift($vars);
        if ($this->hasRawComponent($base)) {
            $base = $this->getRawComponents()[$base];
        } else return $default;
        while (count($vars) > 0) {
            $baseKey = array_shift($vars);
            if (is_array($base) && isset($base[$baseKey])) {
                $base = $base[$baseKey];
            } else {
                return $default;
            }
        }

        return $this->nestedCache[$component] = $base;
    }

    public function hasRawComponent(string $component): bool
    {
        return isset($this->getRawComponents()[$component]);
    }

    public function getRawComponents(): array
    {
        return ($this->data['minecraft:item']['components'] ?? []);
    }

    public function addComponent(ItemComponent $component): self
    {
        $this->components[$component->getName()] = $component;
        return $this;
    }

    /** @return ItemComponent[] */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function getPocketmineProperties(?string $property = null): ?array
    {
        $propertys = (($this->data['pocketmine:item'] ?? [])['properties'] ?? []);
        if (\is_null($property)) return $propertys;
        return ($propertys[$property] ?? []);
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
