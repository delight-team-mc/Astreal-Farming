<?php
/*
 * ██████╗ ███████╗██╗     ██╗ ██████╗ ██╗  ██╗████████╗  ██████╗  █████╗ ██╗  ██╗██████╗ ██╗   ██╗██████╗ 
 * ██╔══██╗██╔════╝██║     ██║██╔════╝ ██║  ██║╚══██╔══╝  ██╔══██╗██╔══██╗╚██╗██╔╝██╔══██╗██║   ██║██╔══██╗
 * ██║  ██║█████╗  ██║     ██║██║  ██╗ ███████║   ██║     ██████╦╝██║  ██║ ╚███╔╝ ██████╔╝╚██╗ ██╔╝██████╔╝
 * ██║  ██║██╔══╝  ██║     ██║██║  ╚██╗██╔══██║   ██║     ██╔══██╗██║  ██║ ██╔██╗ ██╔═══╝  ╚████╔╝ ██╔═══╝ 
 * ██████╔╝███████╗███████╗██║╚██████╔╝██║  ██║   ██║     ██████╦╝╚█████╔╝██╔╝╚██╗██║       ╚██╔╝  ██║     
 * ╚═════╝ ╚══════╝╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝     ╚═════╝  ╚════╝ ╚═╝  ╚═╝╚═╝        ╚═╝   ╚═╝     
 * 
 * @Author: Joshet18
 * @Discord: https://discord.gg/aqbWcsyTZv
 * @Date: 
 */

namespace delight\inventory\transaction;

use delight\entity\utils\Offer;
use delight\event\PlayerItemTradeEvent;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;

final class TradeTransaction extends InventoryTransaction
{

	private ?Item $buyA = null;
	private ?Item $buyB = null;

	private ?Item $outputItem = null;

	public function __construct(Player $source, private readonly Offer $recipe, private readonly int $repetitions = 1)
	{
		parent::__construct($source);
	}

	public function validate(): void
	{
		if (count($this->actions) < 1) throw new TransactionValidationException("Transaction must have at least one action to be executable");
		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);
		$buyA = $this->recipe->getBuyA();
		$buyB = $this->recipe->getBuyB();
		foreach ($inputs as $input) {
			\GlobalLogger::get()->notice('TypeId: ' . $input->getTypeId() . ', Name: ' . $input->getVanillaName());
			if ($input->getTypeId() === $buyA->getTypeId()) {
				$this->buyA = $input;
			} elseif ($buyB !== null && $input->getTypeId() === $buyB->getTypeId()) {
				$this->buyB = $input;
			} elseif ($this->buyA !== null && ($buyB !== null && $this->buyB !== null)) {
				throw new TransactionValidationException("Transaction has too many input items");
			}
		}
		if ($this->buyA === null || ($buyB !== null && $this->buyB === null)) throw new TransactionValidationException("No items received");
		if (($outputCount = count($outputs)) !== 1) throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		$this->outputItem = $outputs[0];

		$this->validateInputItems();
		$this->validateOutputItems();
	}

	private function validateInputItems(): void
	{
		if ($this->buyA === null || $this->outputItem === null) throw new AssumptionFailedError("Expected that buyA and outputItem are not null before validating output");
		$expectedBuyA = $this->recipe->getBuyA();
		$expectedBuyB = $this->recipe->getBuyB();
		foreach (array_filter([$expectedBuyA, $expectedBuyB], fn($item): bool => $item instanceof Item) as $item) $item->setCount($item->getCount() * $this->repetitions);
		if (!$expectedBuyA->equalsExact($this->buyA)) throw new TransactionValidationException("Invalid buyA item");
		if ($expectedBuyB !== null) {
			if ($this->buyB === null) throw new TransactionValidationException("Expected buyB item, but received nothing");
			if (!$expectedBuyB->equalsExact($this->buyB)) throw new TransactionValidationException("Invalid buyB item");
		}
	}

	private function validateOutputItems(): void
	{
		if ($this->outputItem === null) throw new AssumptionFailedError("Expected that outputItem is not null before validating output");
		$expectedOutput = $this->recipe->getResult()->setCount($this->recipe->getResult()->getCount() * $this->repetitions);
		if (!$expectedOutput->equalsExact($this->outputItem)) throw new TransactionValidationException("Invalid output item");
	}

	public function execute(): void
	{
		parent::execute();
		$this->recipe->setUses($this->recipe->getUses() + $this->repetitions);
	}

	protected function callExecuteEvent(): bool
	{
		$ev = new PlayerItemTradeEvent($this->source, $this->buyA, $this->outputItem, $this->buyB);
		$ev->call();
		return !$ev->isCancelled();
	}
}
