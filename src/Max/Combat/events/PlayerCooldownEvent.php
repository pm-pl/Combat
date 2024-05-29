<?php

declare(strict_types=1);

namespace Max\Combat\events;

use pocketmine\event\player\PlayerEvent;

abstract class PlayerCooldownEvent extends PlayerEvent {
    protected int $cooldown;

    public function getCooldown(): int {
        return $this->cooldown;
    }
}