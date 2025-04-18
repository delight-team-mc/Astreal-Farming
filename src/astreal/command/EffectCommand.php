<?php

namespace astreal\command;

use astreal\libraries\commands\args\EnumArg;
use astreal\libraries\commands\args\IntArg;
use astreal\libraries\commands\args\TargetArg;
use astreal\libraries\commands\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;

class EffectCommand extends BaseCommand
{

    public function __construct()
    {
        parent::__construct("effect", KnownTranslationFactory::pocketmine_command_effect_description(), KnownTranslationFactory::commands_effect_usage());
        $this->setPermissions([DefaultPermissionNames::COMMAND_EFFECT_SELF, DefaultPermissionNames::COMMAND_EFFECT_OTHER]);
        $this->registerArg(...[
            new TargetArg('player'),
            new EnumArg('effect', false, 'Effect', fn() => array_map(strtolower(...), array_merge(['clear'], StringToEffectParser::getInstance()->getKnownAliases()))),
            new IntArg('seconds', true),
            new IntArg('amplifier', true),
            new EnumArg('hidenParticles', true, 'boolean', fn() => ['true', 'false'])
        ]);
    }

    public function execute_command(CommandSender $sender, string $label, array $args)
    {
        if (count($args) < 2) throw new InvalidCommandSyntaxException();
        /** @var Living[] $selects */
        $selects = array_filter($this->getArgsParser()::parserTarget($sender, $args[0], false, true), fn($select): bool => $select instanceof Living);
        if (count($selects) === 0) return true;
        foreach ($selects as $player) {
            $effectManager = $player->getEffects();
            if (strtolower($args[1]) === "clear") {
                $effectManager->clear();
                $sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed_all($player->getDisplayName()));
                return true;
            }
            $effect = StringToEffectParser::getInstance()->parse($args[1]);
            if ($effect === null) {
                $sender->sendMessage(KnownTranslationFactory::commands_effect_notFound($args[1])->prefix(TextFormat::RED));
                return true;
            }
            $amplification = 0;
            if (count($args) >= 3) {
                if (($d = $this->getBoundedInt($sender, $args[2], 0, (int)(Limits::INT32_MAX / 20))) === null) return false;
                $duration = $d * 20;
            } else {
                $duration = null;
            }
            if (count($args) >= 4) {
                $amplification = $this->getBoundedInt($sender, $args[3], 0, 255);
                if ($amplification === null) return false;
            }
            $visible = true;
            if (count($args) >= 5 && $this->getArgsParser()::parserBoolean($args[4], false)) $visible = false;
            if ($duration === 0) {
                if (!$effectManager->has($effect)) {
                    if (count($effectManager->all()) === 0) {
                        $sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive_all($player->getDisplayName()));
                    } else {
                        $sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive($effect->getName(), $player->getDisplayName()));
                    }
                    return true;
                }
                $effectManager->remove($effect);
                $sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed($effect->getName(), $player->getDisplayName()));
            } else {
                $instance = new EffectInstance($effect, $duration, $amplification, $visible);
                $effectManager->add($instance);
                self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success($effect->getName(), (string)$instance->getAmplifier(), $player->getDisplayName(), (string)($instance->getDuration() / 20)));
            }
        }
        return true;
    }
}
