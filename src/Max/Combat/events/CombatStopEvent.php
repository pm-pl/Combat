<?php

namespace Max\Combat\events;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class CombatStopEvent extends PlayerEvent {
    public function __construct(Player $player) {
        $this->player = $player;
    }
}