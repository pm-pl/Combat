<?php

declare(strict_types=1);

namespace Max\Combat\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class CombatCooldownRestartEvent extends PlayerCooldownEvent implements Cancellable {
    use CancellableTrait;

    public function __construct(Player $player, int $cooldown) {
        $this->player = $player;
        $this->cooldown = $cooldown;
    }

    public function setCooldown(int $cooldown): void {
        $this->cooldown = $cooldown;
    }
}