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

class Professions
{
    const TIER_NOVICE = 0;
    const TIER_APPRENTICE = 1;
    const TIER_JOURNEYMAN = 2;
    const TIER_EXPERT = 3;
    const TIER_MASTER = 4;

    const BIOME_PLAINS = 0;
    const BIOME_DESERT = 1;
    const BIOME_JUNGLE = 2;
    const BIOME_SAVANNA = 3;
    const BIOME_SNOW = 4;
    const BIOME_SWAMP = 5;
    const BIOME_TAIGA = 6;

    const PROFESSION_UNEMPLOYED = 0;
    const PROFESSION_FARMER = 1;
    const PROFESSION_FISHERMAN = 2;
    const PROFESSION_SHEPHERD = 3;
    const PROFESSION_FLETCHER = 4;
    const PROFESSION_LIBRARIAN = 5;
    const PROFESSION_CARTOGRAPHER = 6;
    const PROFESSION_CLERIC = 7;
    const PROFESSION_ARMORER = 8;
    const PROFESSION_WEAPON_SMITH = 9;
    const PROFESSION_TOOL_SMITH = 10;
    const PROFESSION_BUTCHER = 11;
    const PROFESSION_LEATHER_WORKER = 12;
    const PROFESSION_MASON = 14;
    const PROFESSION_NITWIT = 15;

    const TAG_PROFESSION = "Profession";
    const TAG_OFFER_BUFFER = "OfferBuffer";
    const TAG_TIER = "Tier";
    const TAG_EXPERIENCE = "Experience";
    const TAG_BIOME = "Biome";
}