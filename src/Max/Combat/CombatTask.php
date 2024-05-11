<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\events\CombatStopEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class CombatTask extends Task {
    private Session $session;

    public function __construct(private readonly Player $player) {
        $this->session = Combat::getInstance()->getSession($this->player);
    }

    public function onRun(): void {
        if ($this->player->isConnected()) {
            if ($this->session->isInCombat()) return;
            (new CombatStopEvent($this->player))->call();
            $this->player->sendMessage(TextFormat::colorize(Combat::getInstance()->getConfig()->getNested("messages.combat-stop", "combat-stop")));
        }
        $this->getHandler()->cancel();
    }
}