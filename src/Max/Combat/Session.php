<?php

declare(strict_types=1);

namespace Max\Combat;

use pocketmine\Server;

class Session {
    private int $endCombatCooldown;

    public function __construct() {
        $this->stopCombatCooldown();
    }

    public function startCombatCooldown(int $cooldown): void {
        $this->endCombatCooldown = Server::getInstance()->getTick() + $cooldown;
    }

    public function stopCombatCooldown(): void {
        $this->endCombatCooldown = Server::getInstance()->getTick();
    }

    public function isInCombat(): bool {
        return $this->endCombatCooldown > Server::getInstance()->getTick();
    }

    public function getCombatCooldownLeft(): int {
        return $this->endCombatCooldown - Server::getInstance()->getTick();
    }
}