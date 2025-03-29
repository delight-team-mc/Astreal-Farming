<?php

declare(strict_types=1);
/*
 * 
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║   
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║   
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║   
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝   
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 */

namespace astreal\libraries\vanilla\item;

use astreal\libraries\vanilla\item\component\AllowOffHandComponent;
use astreal\libraries\vanilla\item\component\CanDestroyInCreativeComponent;
use astreal\libraries\vanilla\item\component\CooldownComponent;
use astreal\libraries\vanilla\item\component\CreativeCategoryComponent;
use astreal\libraries\vanilla\item\component\CreativeGroupComponent;
use astreal\libraries\vanilla\item\component\DamageComponent;
use astreal\libraries\vanilla\item\component\DisplayNameComponent;
use astreal\libraries\vanilla\item\component\DurabilityComponent;
use astreal\libraries\vanilla\item\component\FoodComponent;
use astreal\libraries\vanilla\item\component\FuelComponent;
use astreal\libraries\vanilla\item\component\HandEquippedComponent;
use astreal\libraries\vanilla\item\component\IconComponent;
use astreal\libraries\vanilla\item\component\ItemComponent;
use astreal\libraries\vanilla\item\component\MaxStackSizeComponent;
use astreal\libraries\vanilla\item\component\ProjectileComponent;
use astreal\libraries\vanilla\item\component\ThrowableComponent;
use astreal\libraries\vanilla\item\component\UseAnimationComponent;
use astreal\libraries\vanilla\item\component\UseDurationComponent;
use astreal\libraries\vanilla\item\component\WearableComponent;
use pocketmine\entity\Consumable;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Food;
use pocketmine\item\ProjectileItem;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\nbt\tag\CompoundTag;

trait ItemComponentsTrait
{

	/** @var ItemComponent[] */
	private array $components = [];

	public function addComponent(ItemComponent $component): self
	{
		$this->components[$component->getName()] = $component;
		return $this;
	}

	public function hasComponent(string $name): bool
	{
		return isset($this->components[$name]);
	}

	public function getComponents(): CompoundTag
	{
		$components = CompoundTag::create();
		$properties = CompoundTag::create();
		foreach ($this->components as $component) {
			if ($component->isProperty()) {
				$properties->setTag($component->getName(), $component->getValue());
				continue;
			}
			$components->setTag($component->getName(), $component->getValue());
		}
		$components->setTag("item_properties", $properties);
		return CompoundTag::create()->setTag("components", $components);
	}

	public function initComponents(string $texture, ?CreativeInventoryInfo $creativeInfo = null): void
	{
		$creativeInfo ??= CreativeInventoryInfo::DEFAULT();
		$this->addComponent(new CreativeCategoryComponent($creativeInfo));
		$this->addComponent(new CreativeGroupComponent($creativeInfo));
		$this->addComponent(new CanDestroyInCreativeComponent());
		$this->addComponent(new IconComponent($texture));
		$this->addComponent(new MaxStackSizeComponent($this->getMaxStackSize()));

		if ($this instanceof Armor) {
			$slot = match ($this->getArmorSlot()) {
				ArmorInventory::SLOT_HEAD => WearableComponent::SLOT_ARMOR_HEAD,
				ArmorInventory::SLOT_CHEST => WearableComponent::SLOT_ARMOR_CHEST,
				ArmorInventory::SLOT_LEGS => WearableComponent::SLOT_ARMOR_LEGS,
				ArmorInventory::SLOT_FEET => WearableComponent::SLOT_ARMOR_FEET,
				default => WearableComponent::SLOT_ARMOR
			};
			$this->addComponent(new WearableComponent($slot, $this->getDefensePoints()));
		}

		if ($this instanceof Consumable) {
			if (($food = $this instanceof Food)) {
				$this->addComponent(new FoodComponent(!$this->requiresHunger()));
			}
			$this->addComponent(new UseAnimationComponent($food ? UseAnimationComponent::ANIMATION_EAT : UseAnimationComponent::ANIMATION_DRINK));
			$this->setUseDuration(20);
		}

		if ($this instanceof Durable) {
			$this->addComponent(new DurabilityComponent($this->getMaxDurability()));
		}

		if ($this instanceof ProjectileItem) {
			$this->addComponent(new ProjectileComponent(1.25, "projectile"));
			$this->addComponent(new ThrowableComponent(true));
		}

		if ($this->getName() !== "Unknown") {
			$this->addComponent(new DisplayNameComponent($this->getName()));
		}

		if ($this->getFuelTime() > 0) {
			$this->addComponent(new FuelComponent($this->getFuelTime()));
		}

		if ($this->getAttackPoints() > 0) {
			$this->addComponent(new DamageComponent($this->getAttackPoints() - 1));
		}

		if ($this instanceof Tool) {
			$this->addComponent(new HandEquippedComponent());
			if ($this instanceof Sword) {
				$this->addComponent(new CanDestroyInCreativeComponent(false));
			}
		}
	}

	protected function allowOffHand(bool $offHand = true): void
	{
		$this->addComponent(new AllowOffHandComponent($offHand));
	}

	protected function setUseCooldown(float $duration, string $category = ""): void
	{
		$this->addComponent(new CooldownComponent($category !== "" ? $category : $this->getName(), $duration));
	}

	protected function setUseDuration(int $ticks): void
	{
		$this->addComponent(new UseDurationComponent($ticks));
	}
}
