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
 * @Date: 
 */

namespace night\libraries\entity\utils;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;

class Offer
{
    const TAG_USES = "uses";
    const TAG_MAX_USES = "maxUses";
    const TAG_REWARD_EXP = "rewardExp";
    const TAG_TRADER_EXP = "traderExp";
    const TAG_PRICE_MULTIPLIER_A = "priceMultiplierA";
    const TAG_PRICE_MULTIPLIER_B = "priceMultiplierB";
    const TAG_SELL = "sell";
    const TAG_BUY_A = "buyA";
    const TAG_BUY_B = "buyB";
    const TAG_TIER = "tier";

    private Item $input;
    private ?Item $input2;
    private Item $result;
    public CompoundTag $namedtag;

    public function __construct(
        string|Item $input,
        null|string|Item $input2,
        string|Item $result,
        private int $traderExp = 0,
        private bool $rewardExp = false,
        private float $priceMultiplierA = 0,
        private float $priceMultiplierB = 0,
        private int $maxUses = 100,
        private int $uses = 0
    ) {
        $this->setInput($input, $input2);
        $this->setResult($result);
        $this->initializeNBT();
    }

    public function initializeNBT(): void
    {
        $this->namedtag = new CompoundTag();
        $this->namedtag->setInt(self::TAG_USES, $this->uses);
        $this->namedtag->setInt(self::TAG_MAX_USES, $this->maxUses);
        $this->namedtag->setInt(self::TAG_TRADER_EXP, $this->traderExp);
        $this->namedtag->setFloat(self::TAG_PRICE_MULTIPLIER_A, $this->priceMultiplierA);
        $this->namedtag->setFloat(self::TAG_PRICE_MULTIPLIER_B, $this->priceMultiplierB);
        $this->namedtag->setByte(self::TAG_REWARD_EXP, $this->rewardExp);
    }

    public function serialize(): ?CompoundTag
    {
        $input = $this->input;
        $input2 = $this->input2;
        $result = $this->result;

        if ($input !== null && $result !== null) {
            $nbt = clone $this->namedtag;
            $nbt->setTag(self::TAG_SELL, $result->nbtSerialize());
            $nbt->setTag(self::TAG_BUY_A, $input->nbtSerialize());
            if ($input2 !== null) {
                $nbt->setTag(self::TAG_BUY_B, $input2->nbtSerialize());
            }
            return $nbt;
        }
        return null;
    }

    public static function deserialize(CompoundTag $nbt): self
    {
        $input = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_BUY_A));
        $input2 = $nbt->getTag(self::TAG_BUY_B);

        if ($input2 instanceof CompoundTag) {
            $input2 = Item::nbtDeserialize($input2);
        } else {
            $input2 = null;
        }
        $result = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_SELL));
        $traderExp = $nbt->getInt(self::TAG_TRADER_EXP);
        $rewardExp = $nbt->getByte(self::TAG_REWARD_EXP);
        $priceMultiplierA = $nbt->getFloat(self::TAG_PRICE_MULTIPLIER_A);
        $priceMultiplierB = $nbt->getFloat(self::TAG_PRICE_MULTIPLIER_B);
        $maxUses = $nbt->getInt(self::TAG_MAX_USES);
        $uses = $nbt->getInt(self::TAG_USES);
        return new self($input, $input2, $result, $traderExp, $rewardExp, $priceMultiplierA, $priceMultiplierB, $maxUses, $uses);
    }

    public function getUses(): int
    {
        return $this->uses;
    }

    public function setUses(int $uses): void
    {
        $this->uses = $uses;
        $this->initializeNBT();
    }

    public function getMaxUses(): int
    {
        return $this->maxUses;
    }

    public function getBuyA(): Item
    {
        return $this->getInput();
    }

    public function getBuyB(): ?Item
    {
        return $this->getInput2();
    }

    public function getInput(): Item
    {
        return clone $this->input;
    }

    public function getInput2(): ?Item
    {
        return is_null($this->input2) ? null : clone $this->input2;
    }

    public function hasBuyB(): bool
    {
        return !is_null($this->input2);
    }

    public function getTraderExp(): int
    {
        return $this->traderExp;
    }

    public function setInput(null|string|Item $input, null|string|Item $input2 = null): void
    {
        if (is_string($input)) $input = StringToItemParser::getInstance()->parse($input);
        if (is_string($input2)) $input2 = StringToItemParser::getInstance()->parse($input2);
        if (is_null($input)) throw new \Exception("input item is null");
        $this->input = $input;
        $this->input2 = $input2;
    }

    public function getResult(): Item
    {
        return clone $this->result;
    }

    public function setResult(string|Item $result): void
    {
        if (is_string($result)) $result = StringToItemParser::getInstance()->parse($result);
        if (is_null($result)) throw new \Exception("result item is null");
        $this->result = $result;
    }
}
