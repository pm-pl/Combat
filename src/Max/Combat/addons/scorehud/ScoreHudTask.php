<?php

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
        if ($this->player->isConnected() && $this->session->isInCombat()) {
            (new PlayerTagUpdateEvent($this->player, new ScoreTag("combat.cooldown", (string)(int)($this->session->getCombatCooldownExpiry()/20))))->call();
        } else {
            $this->getHandler()->cancel();
        }
    }
}