<?php

declare(strict_types=1);

namespace Max\Combat;

use Max\Combat\events\CombatStartEvent;
use Max\Combat\events\CombatStopEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

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
                $session->startCombatCooldown($this->plugin->getCooldown());
            } else {
                $combatStartEvent = new CombatStartEvent($player);
                $combatStartEvent->call();
                if (!$combatStartEvent->isCancelled()) {
                    $session->startCombatCooldown($this->plugin->getCooldown());
                    $this->plugin->getScheduler()->scheduleRepeatingTask(new CombatTask($player), 1);
                    $player->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.combat-start", "combat-start")));
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onCommand(CommandEvent $event): void {
        $player = $event->getSender();
        if (!$player instanceof Player || !$this->plugin->getSession($player)->isInCombat()) return;
        $args = explode(" ", preg_replace(['/"/', "/:/"], "", trim($event->getCommand())));
        $realCommand = $this->plugin->getServer()->getCommandMap()->getCommand(strtolower($args[0]));
        if ($realCommand !== null) {
            $args[0] = $realCommand->getName();
        }
        $command = strtolower(implode(" ", $args));
        foreach ($this->plugin->getBannedCommands() as $bannedCommand) {
            if (str_starts_with($command, strtolower($bannedCommand))) {
                $event->cancel();
                $player->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.banned-command", "banned-command")));
                return;
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        (new CombatStopEvent($player))->call();
        $this->plugin->getSession($player)->stopCombatCooldown();
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if ($this->plugin->getConfig()->get("quit-kill", true) && $this->plugin->getSession($player)->isInCombat()) $player->kill();
        $this->plugin->removeSession($player);
    }
}