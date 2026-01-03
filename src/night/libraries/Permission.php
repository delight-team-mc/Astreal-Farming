<?php

namespace night\libraries;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissionManager;

final class Permission extends \pocketmine\permission\Permission
{
    const DEFAULT_OP = "op";
    const DEFAULT_NOT_OP = "not_op";

    private string $default = self::DEFAULT_OP;

    public static function create(string $name, string $default = self::DEFAULT_OP, ?string $description = null, array $children = []): self
    {
        return new self($name, $default, $description, $children);
    }

    public function __construct(string $name, string $default = self::DEFAULT_OP, ?string $description = null, array $children = [])
    {
        $this->default = $default;
        parent::__construct($name, $description, $children);
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function build(): void
    {
        ($op_root = ($pm = PermissionManager::getInstance())->getPermission(DefaultPermissions::ROOT_OPERATOR));
        $not_op_oot = $pm->getPermission(DefaultPermissions::ROOT_USER);
        if (is_null($pm->getPermission($this->getName()))) {
            $pm->addPermission($this);
            if ($this->getDefault() === self::DEFAULT_OP) {
                $op_root->addChild($this->getName(), true);
            } else {
                $op_root->addChild($this->getName(), true);
                $not_op_oot->addChild($this->getName(), true);
            }
        }
    }
}
