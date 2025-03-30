<?php
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
namespace delight\inventory\transaction;

use delight\event\block\AnvilCombineItemEvent;
use delight\utils\AnvilHelper;
use delight\utils\AnvilResult;
use pocketmine\block\Anvil;
use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\AnvilUseSound;

class AnvilTransaction extends InventoryTransaction{
	private ?Item $baseItem = null;
	private ?Item $materialItem = null;

	public function __construct(
		Player $source,
		private readonly AnvilResult $expectedResult,
		private readonly ?string $customName
	) {
		parent::__construct($source);
	}

	private function validateFiniteResources(int $xpSpent) : void{
		$expectedXpCost = $this->expectedResult->getRepairCost();
		if($xpSpent !== $expectedXpCost){
			throw new TransactionValidationException("Expected the amount of xp spent to be $expectedXpCost, but received $xpSpent");
		}

		$xpLevel = $this->source->getXpManager()->getXpLevel();
		if($xpLevel < $expectedXpCost){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $expectedXpCost");
		}
	}

	private function validateInputs(Item $base, Item $material, Item $expectedOutput) : ?AnvilResult {
		$calculAttempt = AnvilHelper::getResult($this->source, $base, $material, $this->customName);
		if($calculAttempt === null){
			return null;
		}
		$result = $calculAttempt->getResult();
		if($result === null || !$result->equalsExact($expectedOutput)){
			return null;
		}
		$this->baseItem = $base;
		$this->materialItem = $material;
		return $calculAttempt;
	}

	public function validate() : void{
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}
		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);
		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$outputItem = $outputs[0];
		if(($inputCount = count($inputs)) < 1){
			throw new TransactionValidationException("Expected at least 1 input item, but received $inputCount");
		}
		if($inputCount > 2){
			throw new TransactionValidationException("Expected at most 2 input items, but received $inputCount");
		}
		if(count($inputs) < 2){
			$attempt = $this->validateInputs($inputs[0], VanillaItems::AIR(), $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		}else{
			$attempt = $this->validateInputs($inputs[0], $inputs[1], $outputItem) ??
				$this->validateInputs($inputs[1], $inputs[0], $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		}
		if($this->source->hasFiniteResources()){
			$this->validateFiniteResources($attempt->getRepairCost());
		}
	}

	public function execute() : void{
		parent::execute();
		if($this->source->hasFiniteResources()){
			$this->source->getXpManager()->subtractXpLevels($this->expectedResult->getRepairCost());
		}
		$inventory = $this->source->getCurrentWindow();
		if($inventory instanceof AnvilInventory){
			$world = $inventory->getHolder()->getWorld();
			if(mt_rand(0, 12) === 0){
				$anvilBlock = $world->getBlock($inventory->getHolder());
				if($anvilBlock instanceof Anvil){
					$newDamage = $anvilBlock->getDamage() + 1;
					if($newDamage > Anvil::VERY_DAMAGED){
						$newBlock = VanillaBlocks::AIR();
						$world->addSound($inventory->getHolder(), new AnvilBreakSound());
					}else{
						$newBlock = $anvilBlock->setDamage($newDamage);
					}
					$world->setBlock($inventory->getHolder(), $newBlock);
				}
			}
			$world->addSound($inventory->getHolder(), new AnvilUseSound());
		}
	}

	protected function callExecuteEvent() : bool{
		if($this->baseItem === null){
			throw new AssumptionFailedError("Expected that baseItem are not null before executing the event");
		}

		$ev = new AnvilCombineItemEvent($this->source, $this->baseItem, $this->materialItem, $this->expectedResult->getResult() ?? throw new \AssertionError(
			"Expected that the expected result is not null"
		), $this->customName, $this->expectedResult->getRepairCost());
		$ev->call();
		return !$ev->isCancelled();
	}
}