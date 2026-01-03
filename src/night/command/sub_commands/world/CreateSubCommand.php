<?php

namespace night\command\sub_commands\world;

use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use night\libraries\chunkloader\ChunkManager;
use night\libraries\commands\args\EnumArg;
use night\libraries\commands\args\IntArg;
use night\libraries\commands\args\StringArg;
use night\libraries\commands\BaseSubCommand;
use night\libraries\form\simple\FormAPI;
use night\libraries\TranslateMessage;
use night\system\SessionManager;
use pocketmine\player\Player;

class CreateSubCommand extends BaseSubCommand
{
    use FormAPI;

    public function __construct()
    {
        parent::__construct('create');
        $this->setPermission('world.create.command');
        $this->registerArg(...[
            new StringArg('fordel_name', true),
            new EnumArg('difficulty', true, 'world difficulty', fn() => ['peaceful', 'easy', 'normal', 'hard']),
            new EnumArg('generator', true, 'generator type', fn() => array_map(strtolower(...), GeneratorManager::getInstance()->getGeneratorList())),
            new IntArg('chunk_selector', true)
        ]);
    }

    public function execute_command(CommandSender $sender, string $parent_label, string $label, array $args)
    {
        if (!$sender instanceof Player) return $sender->sendMessage(TranslateMessage::create('commands.not.in.game'));
        if (!isset($args[0])) return self::CreateWorldForm($sender);
        $lang = ($sender instanceof Player ? SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang() : TranslateMessage::EN_US);
        $name = $args[0];
        $difficulty = $args[1] ?? 'normal';
        $generator = GeneratorManager::getInstance()->getGenerator(($args[2] ?? 'normal'));
        $chunk_selector = ((isset($args[3]) && is_numeric($args[3]) && intval($args[3]) < 257) ? intval($args[3]) : 4);
        if ($name === '') return $sender->sendMessage(TranslateMessage::create('commands.world.invalid.name', $lang));
        if (is_dir(Server::getInstance()->getDataPath() . "/worlds/" . $name)) return $sender->sendMessage(TranslateMessage::create('commands.world.already.name.use', $lang));
        if (is_null($generator)) return $sender->sendMessage(TranslateMessage::create('commands.world.invalid.generator', $lang));
        if (Server::getInstance()->getWorldManager()->generateWorld($name, WorldCreationOptions::create()->setDifficulty(match (strtolower($difficulty)) {
            'peaceful' => World::DIFFICULTY_PEACEFUL,
            'easy' => World::DIFFICULTY_EASY,
            'normal' => World::DIFFICULTY_NORMAL,
            'hard' => World::DIFFICULTY_HARD,
            default => World::DIFFICULTY_HARD
        })->setSpawnPosition(new Vector3(0, 70, 0))->setGeneratorClass($generator->getGeneratorClass()), false)) {
            ChunkManager::getInstance()->onGenerateWorldChunks($name, 0, 0, intval($chunk_selector));
            $sender->sendMessage(TranslateMessage::create('commands.world.generating.world', $lang));
        }
    }

    public static function CreateWorldForm(Player $sender, string $error = ''): void
    {
        $form = self::createCustomForm(function (Player $sender, ?array $data = null) {
            if (is_null($data)) return;
            $lang = SessionManager::getInstance()->getSessionByXuid($sender->getXuid())->getLang();
            if ($data[1] === "") return self::CreateWorldForm($sender, TranslateMessage::create('commands.world.invalid.name', $lang));
            if (is_dir(Server::getInstance()->getDataPath() . "/worlds/" . $data[1])) return self::CreateWorldForm($sender, TranslateMessage::create('commands.world.already.name.use', $lang));
            var_dump($data);
            if (Server::getInstance()->getWorldManager()->generateWorld(
                $data[1],
                WorldCreationOptions::create()->setDifficulty(match ($data[2]) {
                    'Peaceful' => World::DIFFICULTY_PEACEFUL,
                    'Easy' => World::DIFFICULTY_EASY,
                    'Normal' => World::DIFFICULTY_NORMAL,
                    'Hard' => World::DIFFICULTY_HARD,
                    default => World::DIFFICULTY_HARD
                })->setSpawnPosition(new Vector3(0, 70, 0))
                //->setGeneratorClass(GeneratorManager::getInstance()->getGenerator(GeneratorManager::getInstance()->getGeneratorList()[$data[3]])->getGeneratorClass())
                ,
                false
            )) {
                ChunkManager::getInstance()->onGenerateWorldChunks($data[1], 0, 0, intval($data[4] ?? 4));
                $sender->sendMessage(TranslateMessage::create('commands.world.generating.world', $lang));
            }
        });
        $form->setTitle("§eWorld Creator");
        $form->addLabel('Label: ', $error);
        $form->addInput("Name", "FolderName");
        $form->addDropdown("Dificulty", [0 => "Peaceful", 1 => "Easy", 2 => "Normal", 3 => "Hard"], 3);
        $form->addDropdown("Generator", GeneratorManager::getInstance()->getGeneratorList());
        $form->addSlider('ChunkSelector', 1, 256, -1, 4);
        $form->sendToPlayer($sender);
    }
}
