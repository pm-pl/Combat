<?php

declare(strict_types=1);

namespace Max\Combat\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class CombatCooldownStartEvent extends PlayerEvent implements Cancellable {
    use CancellableTrait;
    private int $cooldown;
    private bool $start;

    public function __construct(Player $player, bool $start, int $cooldown) {
        $this->player = $player;
        $this->start = $start;
        $this->cooldown = $cooldown;
    }

    /**
     * Returns whether this is the start of a fight.
     */
    public function isStart(): bool {
        return $this->start;
    }

    public function getCooldown(): int {
        return $this->cooldown;
    }

    public function setCooldown(int $cooldown): void {
        $this->cooldown = $cooldown;
    }
}