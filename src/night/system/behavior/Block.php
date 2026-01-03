<?php

namespace night\system\behavior;

use night\libraries\vanilla\block\component\BlockComponent;
use night\libraries\vanilla\block\component\BreathabilityComponent;
use night\libraries\vanilla\block\permutations\BlockProperty;
use night\libraries\vanilla\block\permutations\Permutation;
use night\system\BehaviorManager;

class Block implements \JsonSerializable
{

    /** @var mixed[] */
    private array $nestedCache = [];
    /** @var BlockComponent[] $components */
    private array $components = [];
    private array $data;

    public function __construct(string $path)
    {
        $this->data = json_decode(\file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
        $this->addComponent(new BreathabilityComponent());
    }

    public function getIdentifier(): string
    {
        return $this->data['minecraft:block']['description']['identifier'];
    }

    public function getMenuCategory(): array
    {
        return $this->data['minecraft:block']['description']['menu_category'] ?? [];
    }

    /** @return BlockProperty[] */
    public function getStates(): array
    {
        return \array_map(fn(string $name, array $values): BlockProperty => new BlockProperty($name, $values), \array_keys($this->data['minecraft:block']['description']['states'] ?? []), \array_values($this->data['minecraft:block']['description']['states'] ?? []));
    }

    /** @return Permutation[] */
    public function getPermutations(): array
    {
        return \array_map(function (array $permutation): Permutation {
            $components = BehaviorManager::getBlockComponents();
            $perm = (new Permutation($permutation['condition']));
            foreach (($permutation['components'] ?? []) as $component => $value) $perm->withComponent($component, isset($components[$component]) ? $components[$component]($value)->getValue() : $value);
            return $perm;
        }, $this->data['minecraft:block']['permutations'] ?? []);
    }

    public function getPocketmineProperties(?string $property = null, mixed $default = []): mixed
    {
        $propertys = (($this->data['pocketmine:block'] ?? [])['properties'] ?? []);
        if (\is_null($property)) return $propertys;
        return ($propertys[$property] ?? $default);
    }

    public function hasRawComponent(string $component): bool
    {
        return isset($this->getRawComponents()[$component]);
    }

    public function getRawComponent(string $component, mixed $default = null): array|float|int|string|bool|null
    {
        return (($this->data['minecraft:block']['components'] ?? [])[$component] ?? $default);
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

    public function getRawComponents(): array
    {
        return ($this->data['minecraft:block']['components'] ?? []);
    }

    public function hasPocketmineComponent(string $component): bool
    {
        return isset($this->getPocketmineComponents()[$component]);
    }

    public function getPocketmineComponent(string $component, mixed $default = null): array|float|int|string|bool|null
    {
        return (($this->data['pocketmine:block']['components'] ?? [])[$component] ?? $default);
    }

    public function getPocketmineComponentNested(string $component, mixed $default = null): mixed
    {

        if (isset($this->nestedCache[$component])) return $this->nestedCache[$component];
        $vars = explode(".", $component, limit: PHP_INT_MAX);
        $base = array_shift($vars);
        if ($this->hasPocketmineComponent($base)) {
            $base = $this->getPocketmineComponents()[$base];
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

    public function getPocketmineComponents(): array
    {
        return ($this->data['pocketmine:block']['components'] ?? []);
    }

    public function addComponent(BlockComponent $component): void
    {
        $this->components[$component->getName()] = $component;
    }

    /** @return BlockComponent[] */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
