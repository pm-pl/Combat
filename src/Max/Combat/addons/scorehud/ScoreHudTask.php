<?php

declare(strict_types=1);

namespace Max\Combat\addons\scorehud;

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use Max\Combat\Combat;
use Max\Combat\Session;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class ScoreHudTask extends Task {
    private Player $player;
    private Session $session;

    public function __construct(Player $player) {
        $this->player = $player;
        $this->session = Combat::getInstance()->getSession($this->player);
    }

    public function onRun(): void {
        if ($this->player->isConnected()) {
            (new PlayerTagUpdateEvent($this->player, new ScoreTag("combat.cooldown", (string)max(0, ceil($this->session->getCombatCooldownExpiry()/20)))))->call();
            if ($this->session->isInCombat()) return;
        }
        $this->getHandler()->cancel();
    }
}