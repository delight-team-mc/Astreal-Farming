<?php

namespace night\task;

use night\libraries\ScoreAPI;
use night\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Symfony\Component\Filesystem\Path;

class ScoreTask extends Task
{

    public function onRun(): void
    {
        $score_config = new Config(Path::join(Loader::getInstance()->getDataFolder() . 'Scoreboard.json'));
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            $scores = ["§r"];
            $scores[] = '{empty}';
            $scores[] = '&bPing&7: ' . $p->getNetworkSession()->getPing() ?? 0;
            $scores[] = '{empty}';
            $scores[] = '';
            ($api = ScoreAPI::get())->newScore($p, TextFormat::colorize($score_config->getNested('title', 'objective.name')));
            if (count($scores) > 2) foreach ($scores as $score => $line) $line === '{empty}' ? $api->setEmptyLine($p, $score + 1) : $api->setLine($p, $score + 1, TextFormat::colorize($line));
            else $api->removeScoreboard($p);
        }
    }
}
