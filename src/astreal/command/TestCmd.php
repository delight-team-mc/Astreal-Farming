<?php

namespace astreal\command;

use astreal\libraries\commands\args\EnumArg;
use astreal\libraries\commands\BaseCommand;
use astreal\libraries\Particle;
use astreal\libraries\StructureBlockManager;
use astreal\libraries\WrittenBookPacket;
use astreal\Loader;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityFactory;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;

class TestCmd extends BaseCommand
{

    public function __construct()
    {
        parent::__construct('test', '');
        $this->setPermission('globar.command');
        $this->registerArg(...[
            new EnumArg('type', false, 'spawn_type', fn() => ['particle', 'entity', 'book', 'structure']),
            new EnumArg('id', false, 'id_type', fn() => array_filter(array_diff((($r = scandir(StructureBlockManager::getInstance()->getPath())) === false ? [] : $r), ["..", "."]), fn(string $file): bool => (new \SplFileInfo(Loader::getInstance()->getDataFolder() . $file))->getExtension() === 'mcstructure'), true),
            new EnumArg('facing', false, 'facing_direction', fn() => ['north', 'south', 'east', 'west'])
        ]);
    }

    public function execute_command(CommandSender $sender, string $label, array $args)
    {
        if (!$sender instanceof Player) return;
        if (!isset($args[1])) return;
        switch ($args[0]) {
            case 'p':
            case 'particle':
                $sender->getWorld()->addParticle($sender->getPosition(), new Particle($args[1]));
                break;
            case 'e':
            case 'entity':
                try {
                    $nbt = CompoundTag::create()->setString('identifier', $args[1])->setTag('Pos', new ListTag([
                        new DoubleTag($sender->getPosition()->getX()),
                        new DoubleTag($sender->getPosition()->getY()),
                        new DoubleTag($sender->getPosition()->getZ()),
                    ]))->setTag('Rotation', new ListTag([
                        new FloatTag(0),
                        new FloatTag(0),
                    ]));
                    $sender->sendMessage($nbt->toString());
                    $entity = EntityFactory::getInstance()->createFromData($sender->getWorld(), $nbt);
                    $entity?->spawnToAll();
                } catch (\Throwable $th) {
                    $sender->sendMessage('Error: ' . $th->getMessage());
                }
                break;
            case 'book':
                $book = VanillaItems::WRITTEN_BOOK();
                $book->setPageText(0, "123456789\nabcdfg\n" . $args[1]);
                WrittenBookPacket::send($sender, $book);
                break;

            case 'structure':
                try {
                    StructureBlockManager::getInstance()->load_structure(str_replace('.mcstructure', '', $args[1]), $sender->getWorld(), $sender->getPosition()->add(0, -1, 0), match (strtolower($args[2] ?? '')) {
                        'north' => Facing::NORTH,
                        'south' => Facing::SOUTH,
                        'east' => Facing::EAST,
                        'west' => Facing::WEST,
                        default => Facing::NORTH
                    });
                } catch (\Throwable $th) {
                    $sender->sendMessage("§c" . $th->getMessage());
                }
                break;
        }
    }
}
