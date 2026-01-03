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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

class OffersMap
{
    const TAG_RECIPES = "Recipes";
    const TAG_TIER_EXP_REQUIREMENT = "TierExpRequirements";

    /** @var CompoundTag[] */
    private array $tierExp = [];

    /** @param Offer[][] $offers */
    public function __construct(private array $offers = [])
    {
        for ($i = 0; $i <= 4; $i++) {
            $nbt = new CompoundTag();
            $nbt->setInt(strval($i), $i);
            $this->tierExp[] = $nbt;
        }
    }

    public function getNbt(): CacheableNbt
    {
        $recipes = [];

        foreach ($this->offers as $tier => $offers) {
            foreach ($offers as $offer) {
                $nbt = $offer->serialize();
                if (!is_null($nbt)) {
                    $nbt->setInt(Professions::TAG_TIER, $tier);
                    $recipes[] = $nbt;
                }
            }
        }
        $root = new CompoundTag();
        $root->setTag(self::TAG_RECIPES, new ListTag($recipes));
        $root->setTag(self::TAG_TIER_EXP_REQUIREMENT, new ListTag($this->tierExp));
        return new CacheableNbt($root);
    }

    public function resetOffers(): void
    {
        $this->offers = [];
    }

    public function removeOffer(int $tier, int $index): void
    {
        unset($this->offers[$tier][$index]);
        $offers = [];
        foreach (($this->offers[$tier] ?? []) as $offer) $offers[] = $offer;
        $this->offers[$tier] = $offers;
    }

    /**
     * @param int $tier
     * @param Offer|Offer[] $offers
     */
    public function setOffers(int $tier, array|Offer $offers): void
    {
        if (!is_array($offers)) $offers = [$offers];
        foreach ($offers as $offer) $this->offers[$tier] = $offer;
    }

    /**
     * @param int $tier
     * @param Offer|Offer[] $offers
     */
    public function addOffer(int $tier, array|Offer $offers): void
    {
        if (!is_array($offers)) $offers = [$offers];
        foreach ($offers as $offer) $this->offers[$tier][] = $offer;
    }

    /** @return Offer[][] */
    public function getOffers(): array
    {
        return $this->offers;
    }

    public function getOffer(int $recipe): ?Offer
    {
        $recipes = [];
        foreach ($this->offers as $tier => $offers) foreach ($offers as $offer) $recipes[] = $offer;
        return $recipes[$recipe] ?? null;
    }

    public function getOfferByIndex(int $tier, int $index): ?Offer
    {
        return $this->offers[$tier][$index] ?? null;
    }
}
