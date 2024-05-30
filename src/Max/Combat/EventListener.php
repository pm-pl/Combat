<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\events\CombatCooldownRestartEvent;
use Max\Combat\events\CombatCooldownStartEvent;
use Max\Combat\events\CombatCooldownStopEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

final class EventListener implements Listener {
    private Combat $plugin;

    public function __construct(Combat $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @priority MONITOR
     */
    public function onHit(EntityDamageByEntityEvent $event): void {
        $victim = $event->getEntity();
        $attacker = $event->getDamager();
        if (!$victim instanceof Player || !$attacker instanceof Player || $victim === $attacker) return;
        foreach([$victim, $attacker] as $player) {
            if ($player->isCreative()) break;
            $session = $this->plugin->getSession($player);
            if ($session->isInCombat()) {
                $combatEvent = new CombatCooldownRestartEvent($player, $this->plugin->getCooldown());
                $combatEvent->call();
                if ($combatEvent->isCancelled()) break;
            } else {
                $combatEvent = new CombatCooldownStartEvent($player, $this->plugin->getCooldown());
                $combatEvent->call();
                if ($combatEvent->isCancelled()) break;
                $this->plugin->getScheduler()->scheduleRepeatingTask(new CombatTask($player), 1);
                $player->sendMessage($this->plugin->getMessage("cooldown-start"));
            }
            $session->startCombatCooldown($combatEvent->getCooldown());
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onCommand(CommandEvent $event): void {
        $player = $event->getSender();
        if (!$player instanceof Player || !$this->plugin->getSession($player)->isInCombat()) return;
        $args = explode(" ", strtolower(preg_replace(['/"/', '/:/'], "", trim($event->getCommand()))));
        $realCommand = $this->plugin->getServer()->getCommandMap()->getCommand($args[0]);
        if ($realCommand !== null) {
            $args[0] = strtolower($realCommand->getName());
        }
        $command = implode(" ", $args);
        foreach ($this->plugin->getBannedCommands() as $bannedCommand) {
            if (str_starts_with($command, $bannedCommand)) {
                $event->cancel();
                $player->sendMessage($this->plugin->getMessage("cancel-command-banned"));
                return;
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        (new CombatCooldownStopEvent($player))->call();
        $this->plugin->getSession($player)->stopCombatCooldown();
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->getQuitKill() && $this->plugin->getSession($player)->isInCombat()) $player->kill();
    }
}